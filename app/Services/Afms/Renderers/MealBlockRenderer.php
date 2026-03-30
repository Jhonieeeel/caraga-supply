<?php

namespace App\Services\Afms\Renderers;

use App\Services\Afms\Renderers\RendersSpreadsheetRows;
use App\Services\Afms\Renderers\BlockRenderer;

class MealBlockRenderer implements BlockRenderer
{
    use RendersSpreadsheetRows;

    public function __construct(
        private $sheet,
        private array $config,
        private \Closure $duplicator,
        private \Closure $accommodationDuplicator,
    ) {}

    public function render(array $block, int $startRow, bool $insertNew = false): array
    {
        try {
            if ($insertNew) {
                $startRow = ($this->duplicator)($startRow);
            }

            $total = $this->calculateTotal($block);

            $this->fillTitleSection($startRow, [
                $block['lot_title'] ?? '',
                'Delivery Site: ' . ($block['location'] ?? ''),
                'Delivery Period: ' . ($block['date'] ?? ''),
            ], $total);

            $row = $this->renderItemGroup(
                $startRow + $this->config['header_offset'],
                $block['items'] ?? [],
                'meal'
            );

            foreach ($block['accommodations'] ?? [] as $accommodation) {
                $row = $this->renderAccommodation($accommodation, $row);
            }

            return [$row, $total];
        } catch (\Exception) {
            return [$startRow + $this->config['header_offset'], 0];
        }
    }

    private function renderAccommodation(array $data, int $startRow): int
    {
        try {
            $startRow = ($this->accommodationDuplicator)($startRow);

            if (!empty($this->config['total_cell'])) {
                $totalCell = str_replace('{row}', $startRow, $this->config['total_cell']);
                $this->sheet->setCellValue($totalCell, '');
            }

            $this->fillTitleSection($startRow, [
                $data['accommodation_title'] ?? '',
                'Location: ' . ($data['location'] ?? ''),
                'Date: ' . ($data['date'] ?? ''),
            ]);

            return $this->renderItemGroup(
                $startRow + $this->config['header_offset'],
                $data['items'] ?? [],
                'accommodation'
            );
        } catch (\Exception) {
            return $startRow + $this->config['header_offset'];
        }
    }

    private function fillTitleSection(int $row, array $lines, ?float $total = null): void
    {
        foreach ($lines as $i => $text) {
            $this->sheet->setCellValue($this->config['title_column'] . ($row + $i), $text);
        }

        if ($total !== null) {
            $cell = str_replace('{row}', $row, $this->config['total_cell']);
            $this->sheet->setCellValue($cell, $total);
        }
    }
}
