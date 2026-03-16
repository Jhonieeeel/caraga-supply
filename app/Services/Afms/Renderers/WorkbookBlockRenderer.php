<?php

namespace App\Services\Afms\Renderers;

use App\Services\Afms\Renderers\RendersSpreadsheetRows;
use App\Services\Afms\Renderers\BlockRenderer;

class WorkbookBlockRenderer implements BlockRenderer
{
    use RendersSpreadsheetRows;

    public function __construct(
        private $sheet,
        private array $config,
        private \Closure $duplicator
    ) {}

    public function render(array $block, int $startRow, bool $insertNew = false): array
    {
        try {
            if ($insertNew) {
                $startRow = ($this->duplicator)($startRow);
            }

            $total= $this->calculateTotal($block);
            $titleRow = $startRow + 1;

            $this->sheet->setCellValue('C' . $titleRow, $block['block_title'] ?? 'Workbook Items');
            $this->sheet->setCellValue('I' . $titleRow, $total);

            $this->setHeaders($startRow + $this->config['header_offset'] - 1, 'workbook');

            $lastRow = $this->renderItemGroup(
                $startRow + $this->config['header_offset'],
                $block['items'] ?? [],
                'workbook'
            );

            return [$lastRow, $total];
        } catch (\Exception) {
            return [$startRow + $this->config['header_offset'], 0];
        }
    }
}
