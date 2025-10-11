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
    public bool $back = false;

    public bool $editMode = false;

    public function mount(?User $user)
    {
        $this->allRoles = Role::orderBy('name')->pluck('name', 'id')->toArray();

        if ($user && $user->exists) {
            $this->user_id = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->roles = $user->roles->pluck('name')->toArray();
            $this->role = $user->roles->pluck('name')->first() ?? '';
            $this->editMode = true;
        }
    }

    public function save()
    {
        $rules = [
            'name'  => 'required|string|max:255',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->user_id),
            ],
            'role'  => ['required', Rule::in(array_values($this->allRoles))],
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
        return view('livewire.tenant.users.form');
    }
}
