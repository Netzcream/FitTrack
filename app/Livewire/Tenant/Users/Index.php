<?php

namespace App\Livewire\Tenant\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Models\User;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public string $search = '';
    public string $role = '';
    public string $userToDelete = '';
    public int $perPage = 10;
    public array $roles = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    /** @var array<string> */
    protected array $sortableColumns = ['name', 'email', 'created_at'];

    public function mount(): void
    {
        $this->roles = Role::orderBy('name')->pluck('name')->toArray();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, $this->sortableColumns, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updating($field): void
    {
        if (in_array($field, ['search', 'role'])) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'role']);
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

        /** @var User $authUser */
        $authUser = Auth::user();
        if ($authUser->id === $user->id) return; // prevenir autodelete

        $user->delete();
        $this->dispatch('user-deleted');
        $this->reset('userToDelete');
    }

    public function render()
    {
        $query = User::query()
            ->with(['roles', 'student'])
            // Búsqueda agrupada para evitar problemas de precedencia con orWhere
            ->when($this->search, function ($q) {
                $t = "%{$this->search}%";
                $q->where(function ($qq) use ($t) {
                    $qq->where('name', 'like', $t)
                       ->orWhere('email', 'like', $t);
                });
            })
            ->when($this->role, fn($q) =>
                $q->whereHas('roles', fn($r) => $r->where('name', $this->role))
            )
            ->orderBy($this->sortBy, $this->sortDirection);

        return view('livewire.tenant.users.index', [
            'users' => $query->paginate($this->perPage),
        ]);
    }
}
