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

    // Asignar roles
    public ?int $selectedUserForRoles = null;
    public array $selectedRoles = [];
    public array $availableRoles = [];

    protected string $paginationTheme = 'tailwind';
    protected $queryString = ['page' => ['as' => 'tenantUsersPage']];

    public bool $showCreateModal = false;
    public string $new_name = '';
    public string $new_email = '';
    public string $new_password = '';


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

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->new_name = '';
        $this->new_email = '';
        $this->new_password = '';

        $this->dispatch('modal-open', name: 'tenant-user-create');
    }

    public function saveNewUser(): void
    {
        $this->validate([
            'new_name' => ['required', 'string', 'max:255'],
            'new_email' => ['required', 'email', 'max:255'],
            'new_password' => ['required', Password::min(8)],
        ]);

        if (! $this->tenant) return;

        $data = [
            'name'     => $this->new_name,
            'email'    => $this->new_email,
            'password' => Hash::make($this->new_password),
        ];

        $this->tenant->run(function () use ($data) {
            \App\Models\User::create($data);
        });

        $this->dispatch('modal-close', name: 'tenant-user-create');
        $this->dispatch('saved');
        $this->reset(['new_name', 'new_email', 'new_password']);
        $this->resetPage('tenantUsersPage');
    }



    public function impersonate(int $userId): void
    {
        if (! $this->tenant) return;
        $tenantDomain = $this->tenant->mainDomain();
        $signature = hash_hmac('sha256', $userId, config('app.key'));
        $url = "https://{$tenantDomain}/_impersonate-login/{$userId}/{$signature}";
        if (env('APP_ENV') === 'local') {
            $url = "http://{$tenantDomain}/_impersonate-login/{$userId}/{$signature}";
        }


        $this->dispatch('open-url', url: $url);
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
                $roles = [];
                if (method_exists($u, 'getRoleNames')) {
                    // Convertir Collection de Spatie a array de strings simples
                    $roles = $u->getRoleNames()->map(fn($role) => (string) $role)->values()->toArray();
                }

                return (object) [
                    'id'                   => $u->id,
                    'name'                 => $u->name,
                    'email'                => $u->email,
                    'email_verified_at'    => $u->email_verified_at,
                    'created_at'           => $u->created_at,
                    'created_at_formatted' => optional($u->created_at)?->format('d/m/Y H:i'),
                    'roles'                => $roles,
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

    // 2) Asignar roles
    public function openRolesModal(int $userId): void
    {
        if (! $this->tenant) return;

        $this->selectedUserForRoles = $userId;

        // Obtener roles disponibles y roles actuales del usuario
        $this->tenant->run(function () use ($userId) {
            // Obtener todos los roles disponibles en el tenant
            $allRoles = \Spatie\Permission\Models\Role::all()->pluck('name')->toArray();
            $this->availableRoles = $allRoles;

            // Obtener los roles actuales del usuario
            $user = \App\Models\User::findOrFail($userId);
            if (method_exists($user, 'getRoleNames')) {
                $this->selectedRoles = $user->getRoleNames()->toArray();
            } else {
                $this->selectedRoles = [];
            }
        });

        $this->dispatch('modal-open', name: 'assign-roles');
    }

    public function saveRoles(): void
    {
        if (! $this->tenant || ! $this->selectedUserForRoles) return;

        $userId = $this->selectedUserForRoles;
        $roles = $this->selectedRoles;

        $this->tenant->run(function () use ($userId, $roles) {
            $user = \App\Models\User::findOrFail($userId);
            if (method_exists($user, 'syncRoles')) {
                $user->syncRoles($roles);
            }
        });

        $this->dispatch('modal-close', name: 'assign-roles');
        $this->dispatch('saved');

        $this->reset(['selectedUserForRoles', 'selectedRoles', 'availableRoles']);
        $this->resetPage('tenantUsersPage');
    }

    public function render()
    {
        return view('livewire.central.dashboard.clients.tenant-users-table', [
            'users' => $this->users,
        ]);
    }
}
