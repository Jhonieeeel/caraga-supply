<?php

namespace App\Livewire;
use App\Models\Requisition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Rectification extends Component
{   
    use WithFileUploads;  
    public $headers = [];
    public $rows;
    public $pageTitle = 'DTR Management';

    public ?int $quantity = 5;

    public ?int $year = 2025;
    public ?int $month = 1;
    
    public array $thisMonth = [];

    public ?string $search = '';

    // manage DTR variables
    public $signatories = [];

    public function submitDate() {

        if (count($this->thisMonth) > 0) {
            $this->thisMonth = [];
        }

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
     public $dtrFile;

    public function uploadDTR()
    {
        $this->validate([
            'dtrFile' => 'required|file|max:10240', // max 10MB
        ]);

        $path = $this->dtrFile->store('dtr');

        session()->flash('message', [
            'text' => 'DTR uploaded successfully!',
            'color' => 'green',
            'title' => 'Success',
        ]);
    }
    
    #[On('refresh')]
    public function refresh() {
    }

    public function mount()
    {
        $this->headers = [
            ['index' => 'Time', 'label' => 'Time'],
            ['index' => 'IN/OUT', 'label' => 'In/Out'],
            ['index' => 'Action', 'label' => 'Action'],
        ];

        $this->rows = User::all();
    }

    public function deleteUser($id)
    {
        if ((int) $id === (int) Auth::id()) {
            session()->flash('message', [
                'text' => 'You cannot delete your own account.',
                'color' => 'red',
                'title' => 'Error',
            ]);
            return;
        }

        $hasRequisitions = Requisition::where('user_id', $id)
            ->orWhere('requested_by', $id)
            ->orWhere('approved_by', $id)
            ->orWhere('issued_by', $id)
            ->orWhere('received_by', $id)
            ->exists();

        if ($hasRequisitions) {
            session()->flash('message', [
                'text' => 'Cannot delete this user because they have related requisition records.',
                'color' => 'red',
                'title' => 'Error',
            ]);
            return;
        }

        User::find($id)?->delete();
        $this->rows = User::all(); // Refresh rows

        session()->flash('message', [
            'text' => 'User deleted successfully.',
            'color' => 'green',
            'title' => 'Success',
        ]);
    }



    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.Rectification');
    }
}
