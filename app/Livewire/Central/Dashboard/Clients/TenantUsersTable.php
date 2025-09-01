<?php

namespace App\Livewire\Central\Dashboard\Clients;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class TenantUsersTable extends Component
{
    use WithPagination;

    public string $tenantId = '';
    public ?Tenant $tenant = null;

    // Reset password
    public ?int $selectedUserId = null;
    public string $password = '';
    public string $password_confirmation = '';

    // Confirm make admin
    public ?int $confirmAdminUserId = null;

    protected string $paginationTheme = 'tailwind';
    protected $queryString = ['page' => ['as' => 'tenantUsersPage']];

    protected function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function mount(?string $tenantId = null): void
    {
        $this->tenantId = $tenantId ?? '';
        $this->tenant   = $this->tenantId ? Tenant::find($this->tenantId) : null;
    }

    public function getUsersProperty()
    {
        if (! $this->tenant) return null;

        return $this->tenant->run(function () {
            $paginator = \App\Models\User::query()
                ->select('id', 'name', 'email', 'email_verified_at', 'created_at')
                ->orderBy('name')
                ->paginate(10, pageName: 'tenantUsersPage');

            $paginator->getCollection()->transform(function ($u) {
                return (object) [
                    'id'                   => $u->id,
                    'name'                 => $u->name,
                    'email'                => $u->email,
                    'email_verified_at'    => $u->email_verified_at,
                    'created_at'           => $u->created_at,
                    'created_at_formatted' => optional($u->created_at)?->format('d/m/Y H:i'),
                ];
            });

            return $paginator;
        });
    }

    // ======================= Acciones =======================

    // 1) Resetear password
    public function openResetModal(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->password = '';
        $this->password_confirmation = '';

        // abre modal por nombre
        $this->dispatch('modal-open', name: 'tenant-user-reset-password');
    }

    public function saveResetPassword(): void
    {
        $this->validate();

        if (! $this->tenant || ! $this->selectedUserId) return;

        $pwd = $this->password;

        $this->tenant->run(function () use ($pwd) {
            $user = \App\Models\User::findOrFail($this->selectedUserId);
            $user->password = Hash::make($pwd);
            $user->save();
        });

        // cerrar modal por nombre (patrón Flux que usás)
        $this->dispatch('modal-close', name: 'tenant-user-reset-password');
        $this->dispatch('saved');

        $this->reset(['password', 'password_confirmation']);
    }

    // 2) Hacer Administrador (con confirmación)
    public function confirmMakeAdmin(int $userId): void
    {
        $this->confirmAdminUserId = $userId;
        $this->dispatch('modal-open', name: 'confirm-make-admin');
    }

    public function makeAdmin(): void
    {
        if (! $this->tenant || ! $this->confirmAdminUserId) return;

        $userId = $this->confirmAdminUserId;

        $this->tenant->run(function () use ($userId) {
            $user = \App\Models\User::findOrFail($userId);
            if (method_exists($user, 'syncRoles')) {
                $user->syncRoles(['Admin']); // limpia y asigna sólo Admin
            }
        });

        $this->dispatch('modal-close', name: 'confirm-make-admin');
        $this->dispatch('saved');

        $this->resetPage('tenantUsersPage');
    }

    public function render()
    {
        return view('livewire.central.dashboard.clients.tenant-users-table', [
            'users' => $this->users,
        ]);
    }
}
