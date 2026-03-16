<?php

namespace App\Services\Afms\Renderers;

interface BlockRenderer
{
    public function render(array $block, int $startRow, bool $insertNew = false): array;
    // returns [$nextRow, $blockTotal]
}
