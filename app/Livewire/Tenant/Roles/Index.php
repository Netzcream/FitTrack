<?php

namespace App\Livewire\Tenant\Roles;

//use App\Models\Permission;
use Livewire\Component;

use App\Models\PropertyType;
use App\Models\User;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

#[Layout('components.layouts.tenant')]
class Index extends Component
{


    use WithPagination;

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public string $search = '';

    public string $roleToDelete = '';
    public $perPage = 10;
    public string $permission = '';
    public $permissions = [];

    public function mount()
    {

        $this->permissions = Permission::orderBy('name')->pluck('name')->toArray();
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
    public function updating($field): void
    {
        if (in_array($field, ['search', 'permission'])) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'permission']);
        $this->resetPage();
    }

    public function confirmDelete(string $id): void
    {
        $this->roleToDelete = $id;
    }

    public function delete(): void
    {
        $role = Role::find($this->roleToDelete);


        if (!$role) return;

        $role->delete();
        $this->dispatch('role-deleted');
        $this->reset('roleToDelete');
    }

    public function render()
    {
        $query = Role::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            })
            ->when($this->permission, function ($q) {
                $q->whereHas('permissions', function ($subq) {
                    $subq->where('name', $this->permission);
                });
            });

        $roles = $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.roles.index', compact('roles'));
    }
}
