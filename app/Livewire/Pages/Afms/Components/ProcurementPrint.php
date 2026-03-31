<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Actions\Procurement\PurchaseRequest\LoadAdminJanitorialDraft;
use App\Actions\Procurement\PurchaseRequest\LoadMealDraft;
use App\Actions\Procurement\PurchaseRequest\LoadServiceDraft;
use App\Actions\Procurement\PurchaseRequest\LoadTransportationDraft;
use App\Actions\Procurement\PurchaseRequest\LoadWorkbookDraft;
use App\Actions\Procurement\PurchaseRequest\SaveAdminJanitorialDraft;
use App\Actions\Procurement\PurchaseRequest\SaveMealDraft;
use App\Actions\Procurement\PurchaseRequest\SaveServiceDraft;
use App\Actions\Procurement\PurchaseRequest\SaveTransportationDraft;
use App\Actions\Procurement\PurchaseRequest\SaveWorkbookDraft;
use App\Livewire\Forms\PurchaseRequest\AdminJanitorialDraftForm;
use App\Livewire\Forms\PurchaseRequest\FlatDraftForm;
use App\Livewire\Forms\PurchaseRequest\MealDraftForm;
use App\Livewire\Forms\PurchaseRequest\ServiceDraftForm;
use App\Livewire\Forms\PurchaseRequest\TransportationDraftForm;
use App\Livewire\Forms\PurchaseRequest\WorkbookDraftForm;
use App\Models\PurchaseRequest;
use App\Services\Print\PrPrintService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ProcurementPrint extends Component
{
    public PurchaseRequest $request;
    public string $type = 'Meals';

    public MealDraftForm $mealForm;
    public WorkbookDraftForm $workbookForm;
    public TransportationDraftForm $transportationForm;
    public ServiceDraftForm $serviceForm;
    public AdminJanitorialDraftForm $adminJanitorialForm;

    // ==================== ADD NEW TYPES HERE ONLY ====================

    /**
     * Maps each type name to its xlsx template filename.
     * Add new types here — nothing else in this file needs to change.
     */
    private array $templates = [
        'Meals'              => 'pr_meal_template.xlsx',
        'Workbook'           => 'pr_workbook_template.xlsx',
        'Transportation'     => 'pr_transportation_template.xlsx',
        'Services'           => 'pr_service_template.xlsx',
        'Admin & Janitorial' => 'pr_admin_janitorial_template.xlsx',
    ];

    /**
     * Flat forms: single record per PR, no blocks array.
     * Must extend FlatDraftForm (initDefaults, fillFromDraft, validateForm, toFormArray).
     * Adding a new flat type: declare its form property above, add it here.
     */
    private function flatForms(): array
    {
        return [
            'Transportation' => $this->transportationForm,
            'Services'       => $this->serviceForm,
        ];
    }

    /**
     * Block-based forms: support multiple blocks with items arrays.
     * Adding a new block type: declare its form property above, add it here.
     */
    private function blockForms(): array
    {
        return [
            'Meals'              => $this->mealForm,
            'Workbook'           => $this->workbookForm,
            'Admin & Janitorial' => $this->adminJanitorialForm,
        ];
    }

    // =================================================================

    public function mount(
        PurchaseRequest $request,
        LoadMealDraft $loadMealDraft,
        LoadWorkbookDraft $loadWorkbookDraft,
        LoadAdminJanitorialDraft $loadAdminJanitorialDraft,
        LoadTransportationDraft $loadTransportationDraft,
        LoadServiceDraft $loadServiceDraft
    ): void {
        $this->request = $request;

        foreach ($this->blockForms() as $form) $form->initDefaults();
        foreach ($this->flatForms() as $form)  $form->initDefaults();

        // Block-based: assign blocks array
        $this->loadBlockDraft($loadMealDraft, $this->mealForm);
        $this->loadBlockDraft($loadWorkbookDraft, $this->workbookForm);
        $this->loadBlockDraft($loadAdminJanitorialDraft, $this->adminJanitorialForm);

        // Flat: hydrate individual properties
        $this->loadFlatDraft($loadTransportationDraft, $this->transportationForm);
        $this->loadFlatDraft($loadServiceDraft, $this->serviceForm);
    }

    public function updatedType(
        LoadMealDraft $loadMealDraft,
        LoadWorkbookDraft $loadWorkbookDraft,
        LoadAdminJanitorialDraft $loadAdminJanitorialDraft,
        LoadTransportationDraft $loadTransportationDraft,
        LoadServiceDraft $loadServiceDraft
    ): void {
        // Cancel any open edits across all forms
        foreach ($this->blockForms() as $form) $form->cancelEdit();
        foreach ($this->flatForms() as $form)  $form->cancelEdit();

        // Re-init and reload only the newly selected type
        if (isset($this->blockForms()[$this->type])) {
            $loaders = [
                'Meals'              => $loadMealDraft,
                'Workbook'           => $loadWorkbookDraft,
                'Admin & Janitorial' => $loadAdminJanitorialDraft,
            ];
            $form = $this->blockForms()[$this->type];
            $form->initDefaults();
            $this->loadBlockDraft($loaders[$this->type], $form);
        }

        if (isset($this->flatForms()[$this->type])) {
            $loaders = ['Transportation' => $loadTransportationDraft, 'Services' => $loadServiceDraft];
            $form    = $this->flatForms()[$this->type];
            $form->initDefaults();
            $this->loadFlatDraft($loaders[$this->type], $form);
        }
    }

    private function loadBlockDraft(object $action, object $form): void
    {
        $data = $action->handle($this->request);

        if (empty($data)) return;

        // AdminJanitorialDraft returns ['delivery_period', 'delivery_site', 'blocks' => [...]]
        // Other block drafts return a flat array of blocks directly
        if (isset($data['blocks'])) {
            $form->delivery_period = $data['delivery_period'] ?? '';
            $form->delivery_site   = $data['delivery_site'] ?? '';
            $form->blocks          = $data['blocks'];
        } else {
            $form->blocks = $data;
        }
    }

    private function loadFlatDraft(object $action, FlatDraftForm $form): void
    {
        $data = $action->handle($this->request);
        if (!empty($data)) {
            $form->fillFromDraft($data);
        }
    }

    // ==================== ACTIVE FORM ROUTING ====================

    private function activeBlockForm(): MealDraftForm|WorkbookDraftForm|AdminJanitorialDraftForm
    {
        return $this->blockForms()[$this->type] ?? $this->mealForm;
    }

    private function activeFlatForm(): FlatDraftForm
    {
        return $this->flatForms()[$this->type];
    }

    // ==================== MEALS/WORKBOOK SHARED ====================

    public function removeBlock(int $blockIndex): void
    {
        $this->activeBlockForm()->removeBlock($blockIndex);
    }

    public function removeItem(int $blockIndex, int $itemIndex, string $blockType = 'lot', ?int $accommodationIndex = null): void
    {
        $this->activeBlockForm()->removeItem($blockIndex, $itemIndex, $blockType, $accommodationIndex);
    }

    public function cancelEdit(): void
    {
        foreach ($this->blockForms() as $form) $form->cancelEdit();
        foreach ($this->flatForms() as $form)  $form->cancelEdit();
    }

    public function saveEdit(): void
    {
        if (isset($this->flatForms()[$this->type])) {
            if ($error = $this->activeFlatForm()->saveEdit()) {
                session()->flash('error', $error);
            }
            return;
        }

        if ($error = $this->activeBlockForm()->saveEdit()) {
            session()->flash('error', $error);
        }
    }

    public function getBlockTotal(int $blockIndex): float
    {
        return $this->activeBlockForm()->getBlockTotal($blockIndex);
    }

    public function sumItemsPublic(array $items): float
    {
        return $this->activeBlockForm()->sumItemsPublic($items);
    }

    // ==================== MEALS-SPECIFIC ====================

    public function addLotBlock(): void
    {
        $this->mealForm->addLotBlock();
    }

    public function addAccommodationBlock(int $blockIndex): void
    {
        $this->mealForm->addAccommodationBlock($blockIndex);
    }

    public function removeAccommodationBlock(int $blockIndex, int $accommodationIndex): void
    {
        $this->mealForm->removeAccommodationBlock($blockIndex, $accommodationIndex);
    }

    public function addLotItem(int $blockIndex): void
    {
        if ($error = $this->mealForm->addLotItem($blockIndex)) {
            session()->flash('error', $error);
        }
    }

    public function addAccommodationItem(int $blockIndex, int $accommodationIndex): void
    {
        if ($error = $this->mealForm->addAccommodationItem($blockIndex, $accommodationIndex)) {
            session()->flash('error', $error);
        }
    }

    public function startEditLotItem(int $blockIndex, int $itemIndex): void
    {
        $this->mealForm->startEditLotItem($blockIndex, $itemIndex);
    }

    public function startEditAccommodationItem(int $blockIndex, int $accommodationIndex, int $itemIndex): void
    {
        $this->mealForm->startEditAccommodationItem($blockIndex, $accommodationIndex, $itemIndex);
    }

    // ==================== WORKBOOK-SPECIFIC ====================

    public function addWorkbookBlock(): void
    {
        $this->workbookForm->addWorkbookBlock();
    }

    public function addWorkbookItem(int $blockIndex): void
    {
        if ($error = $this->workbookForm->addWorkbookItem($blockIndex)) {
            session()->flash('error', $error);
        }
    }

    public function startEditWorkbookItem(int $blockIndex, int $itemIndex): void
    {
        $this->workbookForm->startEditWorkbookItem($blockIndex, $itemIndex);
    }

    // ==================== TRANSPORTATION-SPECIFIC ====================

    public function addTransportationItem(): void
    {
        if ($error = $this->transportationForm->addItem()) {
            session()->flash('error', $error);
        }
    }

    public function removeTransportationItem(int $itemIndex): void
    {
        $this->transportationForm->removeItem($itemIndex);
    }

    public function startEditTransportationItem(int $itemIndex): void
    {
        $this->transportationForm->startEditItem($itemIndex);
    }

    // ==================== ADMIN & JANITORIAL-SPECIFIC ====================

    public function addAdminJanitorialBlock(): void
    {
        $this->adminJanitorialForm->addBlock();
    }

    public function removeAdminJanitorialBlock(int $blockIndex): void
    {
        $this->adminJanitorialForm->removeBlock($blockIndex);
    }

    public function addAdminItem(int $blockIndex): void
    {
        if ($error = $this->adminJanitorialForm->addAdminItem($blockIndex)) {
            session()->flash('error', $error);
        }
    }

    public function addJanitorialItem(int $blockIndex): void
    {
        if ($error = $this->adminJanitorialForm->addJanitorialItem($blockIndex)) {
            session()->flash('error', $error);
        }
    }

    public function removeAdminJanitorialItem(int $blockIndex, int $itemIndex, string $group): void
    {
        $this->adminJanitorialForm->removeItem($blockIndex, $itemIndex, $group);
    }

    public function startEditAdminJanitorialItem(int $blockIndex, int $itemIndex, string $group): void
    {
        $this->adminJanitorialForm->startEditItem($blockIndex, $itemIndex, $group);
    }

    // ==================== SAVE DRAFTS ====================

    public function saveMealDraft(SaveMealDraft $action): void
    {
        $this->mealForm->cancelEdit();

        foreach ($this->mealForm->blocks as $index => $block) {
            if (empty($block['items']) && empty($block['accommodations'])) {
                session()->flash('error', 'Lot Block ' . ($index + 1) . ' has no items!');
                return;
            }
        }

        $action->handle($this->request, $this->mealForm->blocks);
        session()->flash('success', 'Meal draft saved!');
    }

    public function saveWorkbookDraft(SaveWorkbookDraft $action): void
    {
        $this->workbookForm->cancelEdit();

        foreach ($this->workbookForm->blocks as $index => $block) {
            if (empty($block['items'])) {
                session()->flash('error', 'Workbook Block ' . ($index + 1) . ' has no items!');
                return;
            }
        }

        $action->handle($this->request, $this->workbookForm->blocks);
        session()->flash('success', 'Workbook draft saved!');
    }

    public function saveAdminJanitorialDraft(SaveAdminJanitorialDraft $action): void
    {
        $this->adminJanitorialForm->cancelEdit();

        foreach ($this->adminJanitorialForm->blocks as $index => $block) {
            if (empty($block['administrative_items']) && empty($block['janitorial_items'])) {
                session()->flash('error', 'Lot Block ' . ($index + 1) . ' has no items!');
                return;
            }
        }

        $action->handle($this->request, [
            'delivery_period' => $this->adminJanitorialForm->delivery_period,
            'delivery_site'   => $this->adminJanitorialForm->delivery_site,
            'blocks'          => $this->adminJanitorialForm->blocks,
        ]);

        session()->flash('success', 'Admin & Janitorial draft saved!');
    }

    public function saveTransportationDraft(SaveTransportationDraft $action): void
    {
        $this->transportationForm->cancelEdit();

        if ($error = $this->transportationForm->validateForm()) {
            session()->flash('error', $error);
            return;
        }

        $action->handle($this->request, $this->transportationForm->toFormArray());
        session()->flash('success', 'Transportation draft saved!');
    }

    public function saveServiceDraft(SaveServiceDraft $action): void
    {
        if ($error = $this->serviceForm->validateForm()) {
            session()->flash('error', $error);
            return;
        }

        $action->handle($this->request, $this->serviceForm->toFormArray());
        session()->flash('success', 'Service draft saved!');
    }

    // ==================== PRINT ====================

    public function printPR(PrPrintService $service)
    {
        $template = $this->templates[$this->type] ?? null;

        if (!$template) {
            session()->flash('error', 'Invalid type!');
            return;
        }

        if (isset($this->flatForms()[$this->type])) {
            $form = $this->activeFlatForm();

            if ($error = $form->validateForm()) {
                session()->flash('error', $error);
                return;
            }

            $blocks = [$form->toFormArray()];

        } else {
            $form   = $this->activeBlockForm();
            $blocks = $form->blocks;

            foreach ($blocks as $index => $block) {
                $hasItems = !empty($block['items'])
                    || !empty($block['accommodations'])
                    || !empty($block['administrative_items'])
                    || !empty($block['janitorial_items']);

                if (!$hasItems) {
                    session()->flash('error', 'Block ' . ($index + 1) . ' has no items!');
                    return;
                }
            }

            if ($this->type === 'Admin & Janitorial') {
                $blocks = array_map(fn ($block) => array_merge($block, [
                    'delivery_period' => $form->delivery_period,
                    'delivery_site'   => $form->delivery_site,
                ]), $blocks);
            }
        }

        $filename = $service->handle($template, $blocks, $this->request);

        return response()->download(storage_path("app/public/pr-records/$filename"), $filename);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.components.procurement-print');
    }
}