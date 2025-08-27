<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

// tallstackui
use TallStackUi\Facades\TallStackUi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // table
        TallStackUi::personalize()
            ->table()
            ->block('wrapper', 'overflow-hidden dark:ring-dark-600 rounded-sm shadow ring-1 ring-gray-300')
            ->block('table.thead.normal', 'bg-primary-500')
            ->block('table.th', 'px-2 py-2.5 text-left text-xs  font-semibold text-gray-100')
            ->block('table.td', 'whitespace-nowrap px-2 py-3 text-sm capitalize text-gray-500');

        // sidebar
        TallStackUi::personalize()
            ->sideBar('item')
            ->block('item.state.current', 'text-primary-700 bg-primary-200 dark:bg-dark-600 dark:text-white')
            ->block('item.state.normal', 'text-primary-700 hover:bg-primary-200 dark:hover:bg-dark-600 dark:text-white');

        Model::automaticallyEagerLoadRelationships();

        $storage = Storage::disk('local');
        foreach ($storage->allFiles('livewire-tmp') as $file) {
            $yesterday = now()->subDay()->timestamp;
            if ($yesterday > $storage->lastModified($file)) {
                $storage->delete($file);
            }
        }
    }
}
