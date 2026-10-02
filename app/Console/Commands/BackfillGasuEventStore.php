<?php

namespace App\Console\Commands;

use App\Domain\Gasu\Aggregates\RequisitionAggregate;
use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Domain\Gasu\Events\StockAllocatedToRequisition;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Stock;
use App\Models\Supply;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\EventSourcing\Projectionist;

class BackfillGasuEventStore extends Command
{
    protected $signature = 'gasu:backfill-events {--dry-run : Run preflight checks and report counts without writing any events}';

    protected $description = 'Synthesize an event-sourcing history for the GASU domain from the current supplies/stocks/requisitions/requisition_items rows.';

    public function handle(): int
    {
        // The rows we're backfilling FROM are already the correct, current
        // projection — replaying these synthesized events through the real
        // projectors would re-INSERT duplicate supplies/stocks/requisitions
        // instead of just recording history. Disable projection for the
        // remainder of this command; only stored_events + the uuid columns
        // are written here.
        app(Projectionist::class)->withoutEventHandlers();

        $violations = $this->preflight();

        if ($violations->isNotEmpty()) {
            $this->error('Preflight checks failed — nothing was written:');

            foreach ($violations as $violation) {
                $this->line("  - {$violation}");
            }

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run: preflight passed, no events written.');
            $this->reportCounts();

            return self::SUCCESS;
        }

        DB::transaction(function () {
            $this->backfillSupplies();
            $this->backfillRequisitions();
        });

        $this->info('Backfill complete.');
        $this->reportCounts();

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, string>
     */
    private function preflight(): Collection
    {
        $violations = collect();

        Stock::select('supply_id', 'stock_number')
            ->groupBy('supply_id', 'stock_number')
            ->havingRaw('count(*) > 1')
            ->get()
            ->each(function (Stock $duplicate) use ($violations) {
                $violations->push("Duplicate stock_number '{$duplicate->stock_number}' for supply_id {$duplicate->supply_id}.");
            });

        RequisitionItem::whereDoesntHave('stock')
            ->get()
            ->each(function (RequisitionItem $item) use ($violations) {
                $violations->push("RequisitionItem #{$item->id} references stock_id {$item->stock_id}, which no longer exists.");
            });

        return $violations;
    }

    private function backfillSupplies(): void
    {
        Supply::whereNull('uuid')->orderBy('created_at')->get()->each(function (Supply $supply) {
            $uuid = (string) Str::uuid();

            $aggregate = SupplyAggregate::retrieve($uuid)
                ->createSupply($supply->name, $supply->category, $supply->unit);

            $supply->stocks()->orderBy('created_at')->get()->each(function (Stock $stock) use ($aggregate) {
                $aggregate->addLot($stock->stock_number, $stock->barcode, $stock->stock_location, (float) $stock->price);

                if ((int) $stock->initial_quantity > 0) {
                    $aggregate->receiveStock($stock->stock_number, (int) $stock->initial_quantity);
                }
            });

            $aggregate->persist();

            $supply->update(['uuid' => $uuid]);
        });
    }

    private function backfillRequisitions(): void
    {
        Requisition::whereNull('uuid')->orderBy('created_at')->get()->each(function (Requisition $requisition) {
            $uuid = (string) Str::uuid();

            $aggregate = RequisitionAggregate::retrieve($uuid)
                ->open($requisition->user_id, $requisition->requested_by, $requisition->requested_date, $requisition->purpose);

            $requisition->items()->with('stock.supply')->orderBy('id')->get()->each(function (RequisitionItem $item) use ($aggregate, $uuid) {
                $supply = $item->stock->supply;

                // Recorded directly (not via SupplyAggregate::allocate()) so
                // this reproduces the EXACT historical (stock_number,
                // quantity) pair on record, rather than recomputing an
                // anchor+spillover split that might land differently.
                SupplyAggregate::retrieve($supply->uuid)
                    ->recordThat(new StockAllocatedToRequisition(
                        supplyUuid: $supply->uuid,
                        stockNumber: $item->stock->stock_number,
                        requisitionUuid: $uuid,
                        quantity: $item->requested_qty,
                    ))
                    ->persist();

                $aggregate->recordAllocation($supply->uuid, $item->stock->stock_number, $item->requested_qty);
            });

            if ($requisition->completed) {
                $aggregate->complete((string) ($requisition->pdf ?? ''), null);
            }

            $aggregate->persist();

            $requisition->update(['uuid' => $uuid]);
        });
    }

    private function reportCounts(): void
    {
        $this->table(
            ['Metric', 'Count'],
            [
                ['Supplies', Supply::count()],
                ['Stocks', Stock::count()],
                ['Requisitions', Requisition::count()],
                ['Requisition items', RequisitionItem::count()],
                ['Stored events', DB::table('stored_events')->count()],
            ],
        );
    }
}
