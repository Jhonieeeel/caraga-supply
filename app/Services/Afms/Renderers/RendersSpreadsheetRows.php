<?php

namespace App\Services\Afms\Renderers;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

trait RendersSpreadsheetRows
{
    protected function renderItemGroup(int $startRow, array $items, string $type): int
    {
        if (empty($items)) return $startRow;

        if ($type !== 'workbook') {
            $this->setHeaders($startRow, $type);
            $startRow++;
        }

        $templateRow = $startRow;

        foreach ($items as $index => $item) {
            if ($index > 0) {
                $this->sheet->insertNewRowBefore($startRow, 1);
                $this->cloneRow($templateRow, $startRow);
            }
            $this->populateRow($startRow, $item, $type);
            $startRow++;
        }

        return $startRow;
    }

    protected function populateRow(int $row, array $item, string $type): void
    {
        foreach ($this->config['fields_mapping'][$type] ?? [] as $col => $field) {
            $col   = str_contains($col, ':') ? explode(':', $col)[0] : $col;
            $value = $field === 'calculated_cost'
                ? ($item['qty'] ?? 0) * ($item['estimated_unit_cost'] ?? 0)
                : ($item[$field] ?? ($field === 'qty' ? 0 : ''));

            $this->sheet->setCellValue("{$col}{$row}", $value);
        }
    }

    protected function setHeaders(int $row, string $type): void
    {
        foreach ($this->config['headers'][$type] ?? [] as $cellRange => $text) {
            if (str_contains($cellRange, ':')) {
                [$startCol, $endCol] = explode(':', $cellRange);
                $fullRange = "{$startCol}{$row}:{$endCol}{$row}";
                if (!isset($this->sheet->getMergeCells()[$fullRange])) {
                    $this->sheet->mergeCells($fullRange);
                }
                $this->sheet->setCellValue("{$startCol}{$row}", $text);
            } else {
                $this->sheet->setCellValue("{$cellRange}{$row}", $text);
            }
        }
    }

    protected function cloneRow(int $sourceRow, int $targetRow): void
    {
        $startCol = Coordinate::columnIndexFromString($this->config['item_columns']['start']);
        $endCol = Coordinate::columnIndexFromString($this->config['item_columns']['end']);

        foreach (range($startCol, $endCol) as $col) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $this->sheet->getStyle($letter . $targetRow)
                ->applyFromArray($this->sheet->getStyle($letter . $sourceRow)->exportArray());
        }
    }

    protected function calculateTotal(array $block): float
    {
        $total = array_reduce(
            $block['items'] ?? [],
            fn ($c, $i) => $c + ($i['qty'] ?? 0) * ($i['estimated_unit_cost'] ?? 0),
            0.0
        );

        foreach ($block['accommodations'] ?? [] as $acc) {
            $total += array_reduce(
                $acc['items'] ?? [],
                fn ($c, $i) => $c + ($i['qty'] ?? 0) * ($i['estimated_unit_cost'] ?? 0),
                0.0
            );
        }

        return $total;
    }
}
