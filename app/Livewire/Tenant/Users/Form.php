<?php

namespace App\Livewire\Tenant\Users;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public $user_id;
    public string $name = '';
    public string $email = '';
    public $roles = [];
    public string $role = '';
    public $allRoles = [];
    public array $allPermissions = [];
    public array $permissionsByRole = [];
    public array $directPermissionIds = [];
    public string $permissionSearch = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $back = false;

    public bool $editMode = false;

    public function mount(?User $user)
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->allRoles = $roles->pluck('name', 'id')->toArray();
        $this->permissionsByRole = $roles
            ->mapWithKeys(fn (Role $role) => [
                $role->name => $role->permissions
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->toArray(),
            ])
            ->toArray();

        $this->allPermissions = Permission::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        if ($user && $user->exists) {
            $this->user_id = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->roles = $user->roles->pluck('name')->toArray();
            $this->role = $user->roles->pluck('name')->first() ?? '';
            $this->directPermissionIds = $user->getDirectPermissions()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
            $this->normalizeDirectPermissions();
            $this->editMode = true;
        }
    }

    public function updatedRole(): void
    {
        $this->normalizeDirectPermissions();
        $this->permissionSearch = '';
    }

    public function addDirectPermission(int $permissionId): void
    {
        if (!isset($this->allPermissions[$permissionId])) {
            return;
        }

        if (in_array($permissionId, $this->currentRolePermissionIds(), true)) {
            return;
        }

        if (in_array($permissionId, $this->directPermissionIds, true)) {
            return;
        }

        $this->directPermissionIds[] = $permissionId;
        $this->permissionSearch = '';
    }

    public function removeDirectPermission(int $permissionId): void
    {
        $this->directPermissionIds = array_values(array_filter(
            $this->directPermissionIds,
            fn ($selectedId) => (int) $selectedId !== $permissionId
        ));
    }

    public function save()
    {
        $this->normalizeDirectPermissions();

        $rules = [
            'name'  => 'required|string|max:255',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->user_id),
            ],
            'role'  => ['required', Rule::in(array_values($this->allRoles))],
            'directPermissionIds' => ['array'],
            'directPermissionIds.*' => [Rule::in(array_map(fn ($id) => (int) $id, array_keys($this->allPermissions)))],
        ];

        if (!$this->editMode || $this->password) {
            $rules['password'] = 'required|min:8|confirmed';
        }

        $validated = $this->validate($rules);

        $user = $this->editMode
            ? User::findOrFail($this->user_id)
            : new User();

        $user->fill([
            'name'  => $this->name,
            'email' => $this->email,
        ]);

        if ($this->password) {
            $user->password = Hash::make($this->password);
        }

        $user->save();
        $user->syncRoles([$this->role]);

        $directPermissionNames = collect($this->directPermissionIds)
            ->map(fn (int $permissionId) => $this->allPermissions[$permissionId] ?? null)
            ->filter()
            ->values()
            ->toArray();

        $user->syncPermissions($directPermissionNames);

        // Mensaje Livewire visual
        $this->dispatch('saved');

        // Redirección si el checkbox "volver" está activado
        if ($this->back) {
            return redirect()->route('tenant.dashboard.users.index');
        }

        // Mensaje persistente para próxima request
        session()->flash('success', $this->editMode ? __('users.updated') : __('users.created'));
    }

    public function render()
    {
        return view('livewire.tenant.users.form', [
            'permissionSuggestions' => $this->filteredPermissionSuggestions(),
            'selectedDirectPermissions' => $this->selectedDirectPermissions(),
        ]);
    }

    private function normalizeDirectPermissions(): void
    {
        $rolePermissionIds = $this->currentRolePermissionIds();
        $validPermissionIds = array_map(fn ($id) => (int) $id, array_keys($this->allPermissions));

        $this->directPermissionIds = collect($this->directPermissionIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $validPermissionIds, true))
            ->reject(fn ($id) => in_array($id, $rolePermissionIds, true))
            ->unique()
            ->values()
            ->toArray();
    }

    private function currentRolePermissionIds(): array
    {
        return $this->permissionsByRole[$this->role] ?? [];
    }

    private function filteredPermissionSuggestions(): array
    {
        $search = mb_strtolower(trim($this->permissionSearch));
        $rolePermissionIds = $this->currentRolePermissionIds();

        return collect($this->allPermissions)
            ->filter(function (string $permissionName, int|string $permissionId) use ($search, $rolePermissionIds): bool {
                $permissionId = (int) $permissionId;

                if (in_array($permissionId, $this->directPermissionIds, true)) {
                    return false;
                }

                if (in_array($permissionId, $rolePermissionIds, true)) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                return str_contains(mb_strtolower($permissionName), $search);
            })
            ->take(12)
            ->toArray();
    }

    private function selectedDirectPermissions(): array
    {
        return collect($this->directPermissionIds)
            ->map(function (int $permissionId): ?array {
                $name = $this->allPermissions[$permissionId] ?? null;
                if ($name === null) {
                    return null;
                }

                return [
                    'id' => $permissionId,
                    'name' => $name,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }
}
