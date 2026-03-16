<?php

namespace App\Livewire\Forms\PurchaseRequest;

use Livewire\Form;

class MealDraftForm extends Form
{
    public array $blocks = [];

    public array $currentLotItem = [
        'pax_qty' => 0,
        'mealSnack' => '',
        'arrangement' => '',
        'delivery_date' => '',
        'menu' => '',
        'other_requirement' => '',
        'qty' => 0,
        'unit' => '',
        'estimated_unit_cost' => 0,
    ];

    public array $currentAccommodationItem = [
        'no_days' => 0,
        'room_type' => '',
        'room_arrangement' => '',
        'inclusive_dates' => '',
        'remarks' => '',
        'other_requirement' => '',
        'qty' => 0,
        'unit' => '',
        'estimated_unit_cost' => 0,
    ];

    /**
     * Shared edit state
     */
    public array $editing = [
        'type' => null,
        'blockIndex' => null,
        'accommodationIndex' => null,
        'itemIndex' => null,
    ];

    public array $editForm = [];

    private array $lotDefault = [
        'type' => 'lot',
        'lot_title' => '',
        'location' => '',
        'date' => '',
        'items' => [],
        'accommodations' => [],
    ];

    public function initDefaults(): void
    {
        $this->blocks = [$this->lotDefault];
        $this->cancelEdit();
        $this->resetCurrentLotItem();
        $this->resetCurrentAccommodationItem();
    }

    // ==================== BLOCK MANAGEMENT ====================

    public function addLotBlock(): void
    {
        $this->cancelEdit();
        $this->blocks[] = $this->lotDefault;
    }

    public function removeBlock(int $blockIndex): void
    {
        $this->cancelEdit();

        if (count($this->blocks) > 1) {
            array_splice($this->blocks, $blockIndex, 1);
        }
    }

    public function addAccommodationBlock(int $blockIndex): void
    {
        $this->cancelEdit();

        $this->blocks[$blockIndex]['accommodations'][] = [
            'type' => 'accommodation',
            'accommodation_title' => '',
            'location' => '',
            'date' => '',
            'items' => [],
        ];
    }

    public function removeAccommodationBlock(int $blockIndex, int $accommodationIndex): void
    {
        $this->cancelEdit();
        array_splice($this->blocks[$blockIndex]['accommodations'], $accommodationIndex, 1);
    }

    // ==================== ITEM MANAGEMENT ====================

    public function addLotItem(int $blockIndex): ?string
    {
        $this->cancelEdit();

        $required = [
            'pax_qty', 'mealSnack', 'arrangement', 'delivery_date', 'menu',
            'qty', 'unit', 'estimated_unit_cost'
        ];

        if ($error = $this->validateFields($this->currentLotItem, $required)) {
            return $error;
        }

        $this->blocks[$blockIndex]['items'][] = $this->currentLotItem;
        $this->resetCurrentLotItem();

        return null;
    }

    public function addAccommodationItem(int $blockIndex, int $accommodationIndex): ?string
    {
        $this->cancelEdit();

        $required = [
            'no_days', 'room_type', 'room_arrangement', 'inclusive_dates',
            'remarks', 'qty', 'unit', 'estimated_unit_cost'
        ];

        if ($error = $this->validateFields($this->currentAccommodationItem, $required)) {
            return $error;
        }

        $this->blocks[$blockIndex]['accommodations'][$accommodationIndex]['items'][] = $this->currentAccommodationItem;
        $this->resetCurrentAccommodationItem();

        return null;
    }

    public function removeItem(
        int $blockIndex,
        int $itemIndex,
        string $blockType = 'lot',
        ?int $accommodationIndex = null
    ): void {
        $this->cancelEdit();

        if ($blockType === 'accommodation') {
            array_splice($this->blocks[$blockIndex]['accommodations'][$accommodationIndex]['items'], $itemIndex, 1);
        } else {
            array_splice($this->blocks[$blockIndex]['items'], $itemIndex, 1);
        }
    }

    // ==================== EDITING ====================

    public function startEditLotItem(int $blockIndex, int $itemIndex): void
    {
        $this->editing = [
            'type' => 'lot',
            'blockIndex' => $blockIndex,
            'accommodationIndex' => null,
            'itemIndex' => $itemIndex,
        ];

        $this->editForm = $this->blocks[$blockIndex]['items'][$itemIndex] ?? [];
    }

    public function startEditAccommodationItem(int $blockIndex, int $accommodationIndex, int $itemIndex): void
    {
        $this->editing = [
            'type' => 'accommodation',
            'blockIndex' => $blockIndex,
            'accommodationIndex' => $accommodationIndex,
            'itemIndex' => $itemIndex,
        ];

        $this->editForm = $this->blocks[$blockIndex]['accommodations'][$accommodationIndex]['items'][$itemIndex] ?? [];
    }

    public function cancelEdit(): void
    {
        $this->editing = [
            'type' => null,
            'blockIndex' => null,
            'accommodationIndex' => null,
            'itemIndex' => null,
        ];

        $this->editForm = [];
    }

    public function saveEdit(): ?string
    {
        $type = $this->editing['type'];
        if (!$type) return null;

        if ($type === 'lot') {
            $required = [
                'pax_qty', 'mealSnack', 'arrangement', 'delivery_date', 'menu',
                'qty', 'unit', 'estimated_unit_cost'
            ];

            if ($error = $this->validateFields($this->editForm, $required)) {
                return $error;
            }

            $b = $this->editing['blockIndex'];
            $i = $this->editing['itemIndex'];

            $this->blocks[$b]['items'][$i] = $this->editForm;
            $this->cancelEdit();

            return null;
        }

        if ($type === 'accommodation') {
            $required = [
                'no_days', 'room_type', 'room_arrangement', 'inclusive_dates',
                'remarks', 'qty', 'unit', 'estimated_unit_cost'
            ];

            if ($error = $this->validateFields($this->editForm, $required)) {
                return $error;
            }

            $b = $this->editing['blockIndex'];
            $a = $this->editing['accommodationIndex'];
            $i = $this->editing['itemIndex'];

            $this->blocks[$b]['accommodations'][$a]['items'][$i] = $this->editForm;
            $this->cancelEdit();

            return null;
        }

        return null;
    }

    // ==================== TOTALS ====================

    public function getBlockTotal(int $blockIndex): float
    {
        $block = $this->blocks[$blockIndex] ?? [];
        $total = $this->sumItems($block['items'] ?? []);

        foreach ($block['accommodations'] ?? [] as $accommodation) {
            $total += $this->sumItems($accommodation['items'] ?? []);
        }

        return $total;
    }

    public function sumItemsPublic(array $items): float
    {
        return $this->sumItems($items);
    }

    // ==================== PRIVATE HELPERS ====================

    private function validateFields(array $data, array $fields): ?string
    {
        foreach ($fields as $field) {
            if (empty($data[$field]) && $data[$field] !== 0) {
                return ucfirst(str_replace('_', ' ', $field)) . ' is required!';
            }
        }

        return null;
    }

    private function resetCurrentLotItem(): void
    {
        $this->currentLotItem = [
            'pax_qty' => 0,
            'mealSnack' => '',
            'arrangement' => '',
            'delivery_date' => '',
            'menu' => '',
            'other_requirement' => '',
            'qty' => 0,
            'unit' => '',
            'estimated_unit_cost' => 0,
        ];
    }

    private function resetCurrentAccommodationItem(): void
    {
        $this->currentAccommodationItem = [
            'no_days' => 0,
            'room_type' => '',
            'room_arrangement' => '',
            'inclusive_dates' => '',
            'remarks' => '',
            'other_requirement' => '',
            'qty' => 0,
            'unit' => '',
            'estimated_unit_cost' => 0,
        ];
    }

    private function sumItems(array $items): float
    {
        return array_reduce(
            $items,
            fn ($carry, $item) => $carry + (($item['qty'] ?? 0) * ($item['estimated_unit_cost'] ?? 0)),
            0.0
        );
    }
}
