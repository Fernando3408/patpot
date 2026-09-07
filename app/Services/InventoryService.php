<?php

namespace App\Services;

use App\Models\Input;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Price;
use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function receivePurchase(Purchase $purchase, array $quantities, string $receivedOn): void
    {
        DB::transaction(function () use ($purchase, $quantities, $receivedOn): void {
            $lockedPurchase = Purchase::query()->lockForUpdate()->findOrFail($purchase->id);
            $lines = PurchaseLine::query()
                ->whereBelongsTo($lockedPurchase)
                ->lockForUpdate()
                ->get();

            $reception = $lockedPurchase->receptions()->create([
                'received_on' => $receivedOn,
                'total' => 0,
                'user_id' => auth()->id(),
            ]);
            $total = 0.0;

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
                $reception->lines()->create([
                    'purchase_line_id' => $line->id,
                    'quantity' => $quantity,
                    'unit_cost' => $line->unit_cost,
                ]);
                $total += $quantity * (float) $line->unit_cost;
                InventoryMovement::query()->create(['input_id' => $input->id, 'kind' => 'Recepción de compra', 'quantity' => $quantity, 'reference' => $lockedPurchase->number, 'user_id' => auth()->id()]);
            }
            $reception->update(['total' => $total]);
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

            collect($lines)
                ->filter(fn (array $line): bool => filled($line['id'] ?? null) && ! $existingLines->has((int) $line['id']))
                ->whenNotEmpty(function (): void {
                    throw ValidationException::withMessages(['lines' => 'Una de las líneas de compra no pertenece a esta orden.']);
                });

            foreach ($existingLines as $existingLine) {
                $submittedLine = $submittedLines->get((string) $existingLine->id);

                if ((float) $existingLine->received_quantity > 0) {
                    if ($submittedLine === null || (int) $submittedLine['input_id'] !== (int) $existingLine->input_id || round((float) ($submittedLine['ordered_quantity'] ?? 0)) !== round((float) $existingLine->ordered_quantity) || round((float) ($submittedLine['unit_cost'] ?? 0)) !== round((float) $existingLine->unit_cost)) {
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
                    if ($lockedPurchase->status === 'received') {
                        throw ValidationException::withMessages(['lines' => 'No puedes agregar líneas a una compra ya recibida.']);
                    }

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
     * @param  array<int, array{id?: int|string|null, product_id: int|string, boxes: int|string, price_box: int|float|string}>  $lines
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

            collect($lines)
                ->filter(fn (array $line): bool => filled($line['id'] ?? null) && ! $existingLines->has((int) $line['id']))
                ->whenNotEmpty(function (): void {
                    throw ValidationException::withMessages(['lines' => 'Una de las líneas de pedido no pertenece a esta orden.']);
                });

            foreach ($existingLines as $existingLine) {
                if ((int) $existingLine->dispatched_boxes === 0) {
                    continue;
                }

                $submittedLine = collect($lines)->first(fn (array $line): bool => (int) ($line['id'] ?? 0) === $existingLine->id);
                if ($submittedLine === null || (int) $submittedLine['product_id'] !== $existingLine->product_id || (float) $submittedLine['price_box'] !== (float) $existingLine->price_box) {
                    throw ValidationException::withMessages(['lines' => 'No puedes cambiar producto ni precio en una línea que ya tiene despachos.']);
                }

                if ((int) $submittedLine['boxes'] < (int) $existingLine->dispatched_boxes) {
                    throw ValidationException::withMessages(['lines' => 'No puedes bajar las cajas por debajo de lo ya despachado.']);
                }
            }

            $existingLines->filter(fn (OrderLine $line): bool => (int) $line->dispatched_boxes === 0 && ! $submittedLineIds->contains($line->id))->each->delete();

            foreach ($lines as $line) {
                $existingLine = filled($line['id'] ?? null) ? $existingLines->get((int) $line['id']) : null;
                if ($existingLine === null) {
                    if ($lockedOrder->status === 'completed') {
                        throw ValidationException::withMessages(['lines' => 'No puedes agregar líneas a un pedido completado.']);
                    }

                    if (blank($line['price_box'] ?? null)) {
                        $line['price_box'] = $this->resolveOrderLinePrice($lockedOrder, $line);
                    }
                    $lockedOrder->lines()->create($line);
                } elseif ((int) $existingLine->dispatched_boxes === 0) {
                    $existingLine->update($line);
                } else {
                    $existingLine->update(['boxes' => $line['boxes']]);
                }
            }

            $lockedOrder->refresh()->load('lines');
            $lockedOrder->update(['status' => $lockedOrder->lines->every(fn (OrderLine $line): bool => (int) $line->dispatched_boxes >= (int) $line->boxes) ? 'completed' : ($lockedOrder->lines->sum('dispatched_boxes') > 0 ? 'partial' : 'pending')]);
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
                if ($input->isService()) {
                    continue;
                }
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

    /**
     * @param  array<string, int|float|string|null>  $costs
     */
    public function dispatchOrder(Order $order, array $quantities, string $shippedOn, array $costs = []): void
    {
        DB::transaction(function () use ($order, $quantities, $shippedOn, $costs): void {
            $lockedOrder = Order::query()->with('customer')->lockForUpdate()->findOrFail($order->id);
            $lines = OrderLine::query()
                ->whereBelongsTo($lockedOrder)
                ->with('product.recipes.input')
                ->lockForUpdate()
                ->get();
            $totalBoxesToDispatch = $lines->sum(fn (OrderLine $line): int => max(0, (int) ($quantities[$line->id] ?? 0)));
            $freightCost = (float) ($costs['freight_cost'] ?? 0);
            $managementCost = (float) ($costs['management_cost'] ?? 0);
            $otherCost = (float) ($costs['other_cost'] ?? 0);
            $variableCostPerBox = $totalBoxesToDispatch > 0 ? round(($freightCost + $managementCost + $otherCost) / $totalBoxesToDispatch, 2) : 0;

            $shipment = $lockedOrder->shipments()->create([
                'shipped_on' => $shippedOn,
                'total' => 0,
                'freight_cost' => $freightCost,
                'management_cost' => $managementCost,
                'other_cost' => $otherCost,
            ]);
            $total = 0.0;
            foreach ($lines as $line) {
                $quantity = (int) ($quantities[$line->id] ?? 0);
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
                $shipment->lines()->create([
                    'order_line_id' => $line->id,
                    'boxes' => $quantity,
                    'price_box' => $line->price_box,
                    'cost_box' => $line->product?->cost_per_box ?? 0,
                    'variable_cost_box' => $variableCostPerBox,
                ]);
                $total += $quantity * (float) $line->price_box;
                InventoryMovement::query()->create(['product_id' => $product->id, 'kind' => 'Despacho de pedido', 'quantity' => -$quantity, 'reference' => $lockedOrder->number, 'user_id' => auth()->id()]);

                $this->deductPackaging($product, $quantity, $lockedOrder->number);
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

    private function resolveOrderLinePrice(Order $order, array $line): float
    {
        $price = Price::query()
            ->where('customer_id', $order->customer_id)
            ->where('product_id', $line['product_id'])
            ->first();

        return (float) ($price?->effective_price ?? Product::query()->findOrFail($line['product_id'])->sale_price_box);
    }

    private function deductPackaging(Product $product, int $boxes, string $reference): void
    {
        $packagingCategories = ['envases', 'packaging', 'empaque'];
        $product->load('recipes.input');

        foreach ($product->recipes as $recipe) {
            $input = $recipe->input;
            if (! $input || ! $input->isMaterial()) {
                continue;
            }
            if ($input->category === null || ! in_array(mb_strtolower($input->category), $packagingCategories)) {
                continue;
            }
            $needed = $boxes * (float) $recipe->qty_per_box;
            if ($needed <= 0) {
                continue;
            }
            if ((float) $input->stock < $needed) {
                continue;
            }
            $input->decrement('stock', $needed);
            InventoryMovement::query()->create([
                'input_id' => $input->id,
                'kind' => 'Despacho de pedido',
                'quantity' => -$needed,
                'reference' => $reference,
                'user_id' => auth()->id(),
            ]);
        }
    }
}
