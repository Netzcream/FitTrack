<?php

namespace App\Livewire\Tenant\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $role_id = null;

    public string $name = '';
    /** @var string[] */
    public array $permissions = [];
    /** @var array<int,string> id => name */
    public array $allPermissions = [];

    public bool $editMode = false;
    public bool $back = false; // <-- Check “Volver al listado”

    public function mount(?Role $role)
    {
        $this->allPermissions = Permission::orderBy('name')->pluck('name', 'id')->toArray();

        if ($role && $role->exists) {
            $this->role_id     = $role->id;
            $this->name        = $role->name;
            $this->permissions = $role->permissions->pluck('name')->toArray();
            $this->editMode    = true;
        } else {
            $this->editMode = false;
            $this->role_id  = null;
            $this->name     = '';
            $this->permissions = [];
        }
    }

    public function save()
    {
        $rules = [
            'name'         => ['required','string','max:255'],
            'permissions'  => ['array'],
            'permissions.*'=> [Rule::in(array_values($this->allPermissions))], // validar cada item
        ];

        $validated = $this->validate($rules);

        $role = $this->editMode
            ? Role::findOrFail($this->role_id)
            : new Role();

        $role->name = $this->name;
        $role->save();

        // Importante: NO envolver en array; syncPermissions ya acepta array de strings
        $role->syncPermissions($this->permissions);

        // Feedback Livewire (toast/snackbar según tu layout)
        $this->dispatch('saved');

        // Redirección condicional según “Volver al listado”
        if ($this->back) {
            return redirect()->route('tenant.dashboard.roles.index');
        }

        // Permanecer en la vista con flash de éxito
        session()->flash('success', $this->editMode ? 'Rol actualizado' : 'Rol creado');
    }

    public function render()
    {
        return view('livewire.tenant.roles.form');
    }
}
