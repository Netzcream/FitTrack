<?php

namespace App\Livewire\Tenant\Users;

use Livewire\Component;

use App\Models\PropertyType;
use App\Models\User;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.tenant')]
class Index extends Component
{


    use WithPagination;

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public string $search = '';
    public $role = '';
    public string $userToDelete = '';

    public $perPage = 10;
    public $roles = [];

    public function mount()
    {
        $this->roles = Role::orderBy('name')->pluck('name')->toArray();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }
    public function filter()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function updatedRole()
    {
        $this->resetPage();
    }

    public function confirmDelete(string $id): void
    {
        $this->userToDelete = $id;
    }

    public function delete(): void
    {
        $user = User::find($this->userToDelete);


        if (!$user) return;
        /** @var User $logUser */
        $logUser = Auth::user();
        if ($logUser->id === $user->id) return; // Prevenir autodelete

        $user->delete();
        $this->dispatch('user-deleted');
        $this->reset('userToDelete');
    }

    public function render()
    {
        $query = User::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->when($this->role, function ($q) {
                $q->whereHas('roles', function ($subq) {
                    $subq->where('name', $this->role);
                });
            });

        $users = $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.users.index', compact('users'));
    }
}
