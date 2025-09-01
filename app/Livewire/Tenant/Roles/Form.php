<?php

namespace App\Livewire\Tenant\Roles;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Permission;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public $role_id;
    public $name = '';
    public $permissions = [];
    public $permission = '';
    public $allPermissions = [];
    public $password = '';
    public $password_confirmation = '';

    public $editMode = false;

    public function mount(?Role $role)
    {
        $this->allPermissions = Permission::orderBy('name')->pluck('name', 'id')->toArray();

        if ($role && $role->exists) {
            $this->role_id = $role->id;
            $this->name = $role->name;
            $this->permissions = $role->permissions->pluck('name')->toArray();
            $this->editMode = true;
        } else {
            $this->editMode = false;
            $this->role_id = null;
            $this->name = '';
            $this->permissions = [];
        }
        $this->permission = $role?->permissions->pluck('name')->first() ?? '';
    }

    public function save()
    {
        $rules = [
            'name'  => 'required|string|max:255',
            'permissions' => ['array', Rule::in(array_values($this->allPermissions))],

        ];



        $validated = $this->validate($rules);

        if ($this->editMode) {
            $role = Role::findOrFail($this->role_id);
        } else {
            $role = new Role();
        }

        $role->name = $this->name;


        $role->save();


        $role->syncPermissions([$this->permissions]);

        session()->flash('success', $this->editMode ? 'Rol actualizado' : 'Rol creado');

        return redirect()->route('tenant.dashboard.roles.index');
    }

    public function render()
    {
        return view('livewire.tenant.roles.form');
    }
}
