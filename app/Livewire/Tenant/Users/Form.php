<?php

namespace App\Livewire\Tenant\Users;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public $user_id;
    public $name = '';
    public $email = '';
    public $roles = [];
    public $role = '';
    public $allRoles = [];
    public $password = '';
    public $password_confirmation = '';

    public $editMode = false;

    public function mount(?User $user)
    {
        $this->allRoles = Role::orderBy('name')->pluck('name', 'id')->toArray();

        if ($user && $user->exists) {
            $this->user_id = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->roles = $user->roles->pluck('name')->toArray();
            $this->editMode = true;
        } else {
            $this->editMode = false;
            $this->user_id = null;
            $this->name = '';
            $this->email = '';
            $this->roles = [];
        }
        $this->role = $user?->roles->pluck('name')->first() ?? '';
    }


    public function save()
    {
        $rules = [
            'name'  => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user_id),
            ],
            'role' => ['required', Rule::in(array_values($this->allRoles))],

        ];

        // Password solo en alta o si se quiere cambiar
        if (!$this->editMode || $this->password) {
            $rules['password'] = 'required|min:8|confirmed';
        }

        $validated = $this->validate($rules);

        if ($this->editMode) {
            $user = User::findOrFail($this->user_id);
        } else {
            $user = new User();
        }

        $user->name = $this->name;
        $user->email = $this->email;

        if ($this->password) {
            $user->password = Hash::make($this->password);
        }

        $user->save();


        $user->syncRoles([$this->role]);


        session()->flash('success', $this->editMode ? 'Usuario actualizado' : 'Usuario creado');

        return redirect()->route('tenant.dashboard.users.index');
    }

    public function render()
    {
        return view('livewire.tenant.users.form');
    }
}
