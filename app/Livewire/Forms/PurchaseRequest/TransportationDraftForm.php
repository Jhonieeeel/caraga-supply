<?php

namespace App\Livewire\Forms\PurchaseRequest;

class TransportationDraftForm extends FlatDraftForm
{
    public string $type = 'transportation';
    public string $delivery_period = '';
    public string $delivery_site = '';
    public string $pick_up = '';
    public string $reqs_vehicle = '';
    public string $reqs_model = '';
    public string $reqs_number = '';
    public array  $items = [];

    public array $currentItem = [];
    public array $editForm = [];
    public array $editing = ['blockIndex' => null, 'itemIndex' => null];

    // ==================== DEFAULTS ====================

    public function initDefaults(): void
    {
        $this->type = 'transportation';
        $this->delivery_period = '';
        $this->delivery_site = '';
        $this->pick_up = '';
        $this->reqs_vehicle = '';
        $this->reqs_model = '';
        $this->reqs_number = '';
        $this->items = [];
        $this->currentItem = $this->defaultItem();
        $this->editForm = [];
        $this->editing = ['blockIndex' => null, 'itemIndex' => null];
    }

    private function defaultItem(): array
    {
        return [
            'pax_qty' => 0,
            'itinerary' => '',
            'date_time' => '',
            'no_of_vehicles' => 0,
            'estimated_unit_cost' => 0,
        ];
    }

    // ==================== HYDRATION ====================

    public function fillFromDraft(array $data): void
    {
        $this->delivery_period = $data['delivery_period'] ?? '';
        $this->delivery_site = $data['delivery_site'] ?? '';
        $this->pick_up = $data['pick_up'] ?? '';
        $this->reqs_vehicle = $data['reqs_vehicle'] ?? '';
        $this->reqs_model = $data['reqs_model'] ?? '';
        $this->reqs_number = $data['reqs_number'] ?? '';
        $this->items = $data['items'] ?? [];
        $this->currentItem = $this->defaultItem();
    }

    // ==================== ITEM ACTIONS ====================

    public function addItem(): ?string
    {
        $item = $this->currentItem;

        if (empty($item['itinerary'])) {
            return 'Itinerary is required.';
        }

        if (empty($item['date_time'])) {
            return 'Date/Time is required.';
        }

        if (($item['no_of_vehicles'] ?? 0) <= 0) {
            return 'No. of Vehicles must be greater than 0.';
        }

        if (($item['estimated_unit_cost'] ?? 0) <= 0) {
            return 'Estimated Unit Cost must be greater than 0.';
        }

        $this->items[] = $item;
        $this->currentItem = $this->defaultItem();

        return null;
    }

    public function removeItem(int $itemIndex): void
    {
        array_splice($this->items, $itemIndex, 1);
    }

    // ==================== EDIT ACTIONS ====================

    public function startEditItem(int $itemIndex): void
    {
        $this->editing  = ['blockIndex' => null, 'itemIndex' => $itemIndex];
        $this->editForm = $this->items[$itemIndex];
    }

    public function saveEdit(): ?string
    {
        $item = $this->editForm;

        if (empty($item['itinerary'])) {
            return 'Itinerary is required.';
        }

        if (empty($item['date_time'])) {
            return 'Date/Time is required.';
        }

        if (($item['no_of_vehicles'] ?? 0) <= 0) {
            return 'No. of Vehicles must be greater than 0.';
        }

        if (($item['estimated_unit_cost'] ?? 0) <= 0) {
            return 'Estimated Unit Cost must be greater than 0.';
        }

        $this->items[$this->editing['itemIndex']] = $item;
        $this->cancelEdit();

        return null;
    }

    public function cancelEdit(): void
    {
        $this->editing = ['blockIndex' => null, 'itemIndex' => null];
        $this->editForm = [];
    }

    // ==================== VALIDATION ====================

    public function validateForm(): ?string
    {
        if (empty($this->delivery_period)) return 'Delivery Period is required.';
        if (empty($this->delivery_site)) return 'Delivery Site is required.';
        if (empty($this->pick_up)) return 'Pick-up Point is required.';
        if (empty($this->reqs_vehicle)) return 'Required Vehicle is required.';
        if (empty($this->reqs_model)) return 'Vehicle Model is required.';
        if (empty($this->reqs_number)) return 'Plate/Unit No. is required.';
        if (empty($this->items)) return 'At least one item is required.';

        return null;
    }

    // ==================== TOTALS ====================

    public function getTotal(): float
    {
        return array_reduce(
            $this->items,
            fn ($carry, $item) => $carry + (($item['no_of_vehicles'] ?? 0) * ($item['estimated_unit_cost'] ?? 0)),
            0.0
        );
    }

    // ==================== TO ARRAY ====================

    public function toFormArray(): array
    {
        return [
            'type' => $this->type,
            'delivery_period' => $this->delivery_period,
            'delivery_site' => $this->delivery_site,
            'pick_up' => $this->pick_up,
            'reqs_vehicle' => $this->reqs_vehicle,
            'reqs_model' => $this->reqs_model,
            'reqs_number' => $this->reqs_number,
            'items' => $this->items,
        ];
    }
}
