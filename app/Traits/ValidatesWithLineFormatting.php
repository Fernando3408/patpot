<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait ValidatesWithLineFormatting
{
    private array $lineFieldNames = [
        'input_id' => 'insumo',
        'product_id' => 'producto',
        'ordered_quantity' => 'cantidad ordenada',
        'boxes' => 'cajas',
        'price_box' => 'precio por caja',
        'unit_cost' => 'costo unitario',
        'received_quantity' => 'cantidad recibida',
        'qty_per_box' => 'cantidad por caja',
    ];

    protected function validateLines(array $data, array $rules, array $customMessages = []): array
    {
        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            $raw = $validator->errors()->toArray();
            $fieldNames = $this->lineFieldNames;
            $reformatted = [];
            foreach ($raw as $key => $msgs) {
                if (preg_match('/^lines\.(\d+)\.(.+)$/', $key, $m)) {
                    $lineNum = $m[1] + 1;
                    $field = $m[2];
                    $fieldLabel = $fieldNames[$field] ?? $field;
                    foreach ($msgs as $msg) {
                        $msg = str_replace(
                            "lines.{$m[1]}.{$field}",
                            "{$fieldLabel} de la línea {$lineNum}",
                            $msg
                        );
                        $cleanKey = "línea {$lineNum}, {$fieldLabel}";
                        $reformatted[$cleanKey][] = $msg;
                    }
                } else {
                    foreach ($msgs as $msg) {
                        $reformatted[$key][] = $msg;
                    }
                }
            }
            throw ValidationException::withMessages($reformatted);
        }

        return $validator->validated();
    }
}
