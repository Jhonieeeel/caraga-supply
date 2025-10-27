<?php

namespace App\Livewire;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class Rectification extends Component
{    public $headers = [];

    public ?int $quantity = 5;

    public ?int $year = 2025;
    public ?int $month = 1;
    
    public array $thisMonth = [];

    public ?string $search = '';

    public function submitDate() {

        if (count($this->thisMonth) > 0) {
            $this->thisMonth = [];
        }

        $this->year = now()->format('Y');

        $firstDay = Carbon::create($this->year, $this->month, 1);
        $daysInMonth = $firstDay->daysInMonth;

        

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);
            $weekDay = $date->format('l');
            
            $this->thisMonth[] = [
                'day' => $day,
                'weekday' => $weekDay
            ];
        }

        $this->dispatch('refresh');
       
    }
    
    #[On('refresh')]
    public function refresh() {
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.Rectification');
    }
}
