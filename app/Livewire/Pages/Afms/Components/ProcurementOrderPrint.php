<?php
namespace App\Livewire\Pages\Afms\Components;
use App\Actions\Procurement\PurchaseRequest\LoadMealDraft;
use App\Livewire\Forms\PurchaseRequest\MealDraftForm;
use App\Models\PrMealItem;
use App\Models\PrAccommodationItem;
use App\Models\PurchaseRequest;
use Livewire\Attributes\Layout;
use Livewire\Component;
class ProcurementOrderPrint extends Component
{
    public PurchaseRequest $request;
    public string $type = 'Meals';
    public array $poBlocks = [];
    public ?string $editingPoType = null;        
    public ?int $editingPoBlockIndex = null;
    public ?int $editingPoAccommodationIndex = null;
    public ?int $editingPoItemIndex = null;
    public ?float $editingPoValue = null;
    public function mount(LoadMealDraft $loadMealDraft): void
    {
        $this->loadPoBlocks($loadMealDraft);
    }
    public function updatedType(LoadMealDraft $loadMealDraft): void
    {
        $this->cancelPoEdit();
        $this->loadPoBlocks($loadMealDraft);
    }
    private function loadPoBlocks(LoadMealDraft $loadMealDraft): void
    {
        $data = $loadMealDraft->handle($this->request);
        $this->poBlocks = $data ?: [];
    }
    public function getBlockTotal(int $blockIndex): float
    {
        $block = $this->poBlocks[$blockIndex] ?? [];
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
    private function sumItems(array $items): float
    {
        return array_reduce(
            $items,
            fn ($carry, $item) => $carry + (
                ($item['qty'] ?? 0) * ($item['po_estimated_unit_cost'] ?? $item['estimated_unit_cost'] ?? 0)
            ),
            0.0
        );
    }
    public function startEditPoLotItem(int $blockIndex, int $itemIndex): void
    {
        $this->cancelPoEdit();
        $item = $this->poBlocks[$blockIndex]['items'][$itemIndex] ?? [];
        $this->editingPoType = 'lot';
        $this->editingPoBlockIndex = $blockIndex;
        $this->editingPoItemIndex = $itemIndex;
        $this->editingPoValue = $item['po_estimated_unit_cost'] ?? $item['estimated_unit_cost'] ?? 0;
    }
    public function startEditPoAccommodationItem(int $blockIndex, int $accommodationIndex, int $itemIndex): void
    {
        $this->cancelPoEdit();
        $item = $this->poBlocks[$blockIndex]['accommodations'][$accommodationIndex]['items'][$itemIndex] ?? [];
        $this->editingPoType = 'accommodation';
        $this->editingPoBlockIndex = $blockIndex;
        $this->editingPoAccommodationIndex = $accommodationIndex;
        $this->editingPoItemIndex = $itemIndex;
        $this->editingPoValue = $item['po_estimated_unit_cost'] ?? $item['estimated_unit_cost'] ?? 0;
    }
    public function savePoEdit(): void
    {
        if (!$this->editingPoType) return;
        $value = (float) ($this->editingPoValue ?? 0);
        if ($this->editingPoType === 'lot') {
            $b = $this->editingPoBlockIndex;
            $i = $this->editingPoItemIndex;
            $itemId = $this->poBlocks[$b]['items'][$i]['id'] ?? null;
            if ($itemId) {
                PrMealItem::where('id', $itemId)->update(['po_estimated_unit_cost' => $value]);
            }
            $this->poBlocks[$b]['items'][$i]['po_estimated_unit_cost'] = $value;
        }
        if ($this->editingPoType === 'accommodation') {
            $b = $this->editingPoBlockIndex;
            $a = $this->editingPoAccommodationIndex;
            $i = $this->editingPoItemIndex;
            $itemId = $this->poBlocks[$b]['accommodations'][$a]['items'][$i]['id'] ?? null;
            if ($itemId) {
                PrAccommodationItem::where('id', $itemId)->update(['po_estimated_unit_cost' => $value]);
            }
            $this->poBlocks[$b]['accommodations'][$a]['items'][$i]['po_estimated_unit_cost'] = $value;
        }
        $this->cancelPoEdit();
        session()->flash('success', 'PO Unit Cost updated!');
    }
    public function cancelPoEdit(): void
    {
        $this->editingPoType = null;
        $this->editingPoBlockIndex = null;
        $this->editingPoAccommodationIndex  = null;
        $this->editingPoItemIndex = null;
        $this->editingPoValue = null;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.components.procurement-order-print');
    }
}