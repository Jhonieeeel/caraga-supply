<div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
    <div class="flex items-center justify-between sm:pb-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            User Management
        </h2>
    </div>
    {{-- content --}}
    <x-table :$headers :rows="$this->rows" filter :quantity="[2, 5, 10]">
        @interact('column_role', $row)
            @foreach ($row->roles as $role)
                <x-badge text="{{ $role->name }}" color="blue" light />
            @endforeach
        @endinteract
    </x-table>
</div>

