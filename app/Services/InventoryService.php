<?php

namespace App\Services;

use App\Models\Input;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function receivePurchase(Purchase $purchase, array $quantities): void
    {
        DB::transaction(function () use ($purchase, $quantities): void {
            $lockedPurchase = Purchase::query()->lockForUpdate()->findOrFail($purchase->id);
            $lines = PurchaseLine::query()
                ->whereBelongsTo($lockedPurchase)
                ->lockForUpdate()
                ->get();

            foreach ($lines as $line) {
                $quantity = (float) ($quantities[$line->id] ?? 0);
                if ($quantity <= 0) {
                    continue;
                }
                $remaining = (float) $line->ordered_quantity - (float) $line->received_quantity;
                if ($quantity > $remaining) {
                    throw ValidationException::withMessages(['receipt' => 'La recepción supera lo pendiente.']);
                }
                $input = Input::query()->lockForUpdate()->findOrFail($line->input_id);
                $input->increment('stock', $quantity);
                $input->decrement('transit', min($quantity, (float) $input->transit));
                $input->update(['unit_cost' => $line->unit_cost]);
                $line->increment('received_quantity', $quantity);
                InventoryMovement::query()->create(['input_id' => $input->id, 'kind' => 'Recepción de compra', 'quantity' => $quantity, 'reference' => $lockedPurchase->number, 'user_id' => auth()->id()]);
            }
            $lockedPurchase->refresh()->load('lines');
            $lockedPurchase->update(['status' => $lockedPurchase->lines->every(fn (PurchaseLine $line): bool => (float) $line->received_quantity >= (float) $line->ordered_quantity) ? 'received' : 'partial']);
            AuditService::log('RECEPCIÓN DE COMPRA', 'Recepción de compra', $lockedPurchase);
        });
    }

    /**
     * @param  array<int, array{id?: int|string|null, input_id: int|string, ordered_quantity: int|float|string, unit_cost: int|float|string}>  $lines
     */
    public function updatePurchaseLines(Purchase $purchase, array $lines): void
    {
        DB::transaction(function () use ($purchase, $lines): void {
            $lockedPurchase = Purchase::query()->lockForUpdate()->findOrFail($purchase->id);
            $existingLines = PurchaseLine::query()
                ->whereBelongsTo($lockedPurchase)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $submittedLines = collect($lines)->keyBy(fn (array $line): string => (string) ($line['id'] ?? 'new-'.uniqid()));

            foreach ($existingLines as $existingLine) {
                $submittedLine = $submittedLines->get((string) $existingLine->id);

                if ((float) $existingLine->received_quantity > 0) {
                    if ($submittedLine === null || (int) $submittedLine['input_id'] !== $existingLine->input_id || (float) $submittedLine['ordered_quantity'] !== (float) $existingLine->ordered_quantity || (float) $submittedLine['unit_cost'] !== (float) $existingLine->unit_cost) {
                        throw ValidationException::withMessages(['lines' => 'No puedes modificar ni eliminar una línea que ya tiene recepciones.']);
                    }

                    continue;
                }

                if ($submittedLine === null) {
                    $input = Input::query()->lockForUpdate()->findOrFail($existingLine->input_id);
                    $input->decrement('transit', $existingLine->ordered_quantity);
                    $existingLine->delete();
                }
            }

            foreach ($lines as $line) {
                $existingLine = filled($line['id'] ?? null) ? $existingLines->get((int) $line['id']) : null;
                if ($existingLine !== null && (float) $existingLine->received_quantity > 0) {
                    continue;
                }

                $input = Input::query()->lockForUpdate()->findOrFail($line['input_id']);
                if ($existingLine === null) {
                    $lockedPurchase->lines()->create($line);
                    $input->increment('transit', $line['ordered_quantity']);

                    continue;
                }

                $previousInput = Input::query()->lockForUpdate()->findOrFail($existingLine->input_id);
                $previousInput->decrement('transit', $existingLine->ordered_quantity);
                $existingLine->update($line);
                $input->increment('transit', $line['ordered_quantity']);
            }

            AuditService::log('EDITAR LÍNEAS DE COMPRA', $lockedPurchase->number);
        }, attempts: 5);
    }

    /**
     * @param  array<int, array{id?: int|string|null, product_id: int|string, boxes: int|string, price_box: int|float|string, discount_pct?: int|float|string|null}>  $lines
     */
    public function updateOrderLines(Order $order, array $lines): void
    {
        DB::transaction(function () use ($order, $lines): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $existingLines = OrderLine::query()
                ->whereBelongsTo($lockedOrder)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $submittedLineIds = collect($lines)->pluck('id')->filter()->map(fn ($id): int => (int) $id);

            foreach ($existingLines as $existingLine) {
                if ((int) $existingLine->dispatched_boxes === 0) {
                    continue;
                }

                $submittedLine = collect($lines)->first(fn (array $line): bool => (int) ($line['id'] ?? 0) === $existingLine->id);
                if ($submittedLine === null || (int) $submittedLine['product_id'] !== $existingLine->product_id || (int) $submittedLine['boxes'] !== $existingLine->boxes || (float) $submittedLine['price_box'] !== (float) $existingLine->price_box || (float) ($submittedLine['discount_pct'] ?? 0) !== (float) ($existingLine->discount_pct ?? 0)) {
                    throw ValidationException::withMessages(['lines' => 'No puedes modificar ni eliminar una línea que ya tiene despachos.']);
                }
            }

            $existingLines->filter(fn (OrderLine $line): bool => (int) $line->dispatched_boxes === 0 && ! $submittedLineIds->contains($line->id))->each->delete();

            foreach ($lines as $line) {
                $existingLine = filled($line['id'] ?? null) ? $existingLines->get((int) $line['id']) : null;
                if ($existingLine === null) {
                    $lockedOrder->lines()->create($line);
                } elseif ((int) $existingLine->dispatched_boxes === 0) {
                    $existingLine->update($line);
                }
            }

            AuditService::log('EDITAR LÍNEAS DE PEDIDO', $lockedOrder->number);
        }, attempts: 5);
    }

    public function closeProduction(Production $production, float $boxes, string $completedOn): void
    {
        DB::transaction(function () use ($production, $boxes, $completedOn): void {
            $lockedProduction = Production::query()->lockForUpdate()->findOrFail($production->id);
            $lockedProduction->load('product.recipes.input');
            if ($lockedProduction->status === 'closed') {
                throw ValidationException::withMessages(['production' => 'La producción ya fue cerrada.']);
            }
            foreach ($lockedProduction->product->recipes as $recipe) {
                $input = Input::query()->lockForUpdate()->findOrFail($recipe->input_id);
                $needed = $boxes * (float) $recipe->qty_per_box;
                if ((float) $input->stock < $needed) {
                    throw ValidationException::withMessages(['production' => "Stock insuficiente de {$input->name}."]);
                }
                $input->decrement('stock', $needed);
                InventoryMovement::query()->create(['input_id' => $input->id, 'kind' => 'Consumo de producción', 'quantity' => -$needed, 'reference' => $lockedProduction->number, 'user_id' => auth()->id()]);
            }
            $product = Product::query()->lockForUpdate()->findOrFail($lockedProduction->product_id);
            $product->increment('stock_boxes', $boxes);
            InventoryMovement::query()->create(['product_id' => $product->id, 'kind' => 'Ingreso producto terminado', 'quantity' => $boxes, 'reference' => $lockedProduction->number, 'user_id' => auth()->id()]);
            $lockedProduction->update(['actual_boxes' => $boxes, 'completed_on' => $completedOn, 'status' => 'closed']);
            AuditService::log('CIERRE DE PRODUCCIÓN', $lockedProduction->number);
        });
    }

    public function dispatchOrder(Order $order, array $quantities, string $shippedOn): void
    {
        DB::transaction(function () use ($order, $quantities, $shippedOn): void {
            $lockedOrder = Order::query()->with('customer')->lockForUpdate()->findOrFail($order->id);
            $lines = OrderLine::query()
                ->whereBelongsTo($lockedOrder)
                ->with('product')
                ->lockForUpdate()
                ->get();
            $shipment = $lockedOrder->shipments()->create(['shipped_on' => $shippedOn, 'total' => 0]);
            $total = 0.0;
            foreach ($lines as $line) {
                $quantity = (float) ($quantities[$line->id] ?? 0);
                if ($quantity <= 0) {
                    continue;
                }
                if ($quantity > (float) $line->boxes - (float) $line->dispatched_boxes) {
                    throw ValidationException::withMessages(['dispatch' => 'El despacho supera lo pendiente.']);
                }
                $product = Product::query()->lockForUpdate()->findOrFail($line->product_id);
                if ((float) $product->stock_boxes < $quantity) {
                    throw ValidationException::withMessages(['dispatch' => "Stock insuficiente de {$product->name}."]);
                }
                $product->decrement('stock_boxes', $quantity);
                $line->increment('dispatched_boxes', $quantity);
                $shipment->lines()->create(['order_line_id' => $line->id, 'boxes' => $quantity, 'price_box' => $line->price_box]);
                $total += $quantity * (float) $line->price_box * (1 - ((float) ($line->discount_pct ?? $lockedOrder->customer->discount ?? 0)) / 100);
                InventoryMovement::query()->create(['product_id' => $product->id, 'kind' => 'Despacho de pedido', 'quantity' => -$quantity, 'reference' => $lockedOrder->number, 'user_id' => auth()->id()]);
            }
            if ($shipment->lines()->doesntExist()) {
                $shipment->delete();
                throw ValidationException::withMessages(['dispatch' => 'Ingresa al menos una cantidad para despachar.']);
            }
            $shipment->update(['total' => $total]);
            $lockedOrder->refresh()->load('lines');
            $lockedOrder->update(['status' => $lockedOrder->lines->every(fn (OrderLine $line): bool => (float) $line->dispatched_boxes >= (float) $line->boxes) ? 'completed' : 'partial']);
            AuditService::log('DESPACHO DE PEDIDO', $lockedOrder->number);
        });
    }
}
