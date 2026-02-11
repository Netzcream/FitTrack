<?php

namespace App\Livewire\Central\Dashboard\Clients;

use App\Models\Tenant;
use App\Models\Central\CommercialPlan;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;

use Livewire\Attributes\On;
use Livewire\Attributes\Modelable;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Stancl\Tenancy\Database\Models\Domain as TenancyDomain;
use Illuminate\Validation\Rule;
use App\Events\TenantCustomDomainAttached;
use App\Enums\TenantStatus;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.app')]
class ClientsForm extends Component
{

    public array $domains = [];
    public string $new_domain = '';

    // =========================================================================
    // 🧩 Propiedades - Client
    // =========================================================================

    public ?\App\Models\Tenant $client = null;

    public string $name = '';
    public string $admin_email = '';
    public string $admin_password = '';
    public $status = null;
    public string $id = ''; // subdominio, readonly
    public bool $edit_mode = false;
    public bool $hasChanges = false;

    public string $slug = '';
    public ?string $slug_suggestion = null;
    public bool $slug_manually_edited = false;
    public $commercial_plan_id = null;

    // Si querés mostrar mensajes de error custom, podés agregar:
    public array $reservedSubdomains = ['www', 'admin', 'mail', 'api', 'ftp', 'cpanel', 'webmail', 'lunico', 'test'];

    public $plans;

    // =========================================================================
    // 🔁 Ciclo de vida
    // =========================================================================
    public function mount($client = null)
    {
        $this->plans = CommercialPlan::all();
        if ($client) {
            $this->client = $client;
            $this->name = $client->name ?? "";
            $this->admin_email = $client->admin_email ?? "";
            $this->status = $client->status->value ?? "";
            $this->id = $this->client->id;
            $this->commercial_plan_id = $client->commercial_plan_id;
            $this->edit_mode = true;
            $this->domains = $this->client->domains()->orderBy('id')->get(['id', 'domain'])->toArray();
            $this->slug = $this->id;
        } else {
            $this->status = TenantStatus::ACTIVE;
            $this->slug = '';
            $this->slug_suggestion = null;
            $this->slug_manually_edited = false;
            $this->admin_password = '';
        }
    }


    public function rules()
    {
        $id = $this->client?->id ?? null;

        $base = [
            'name' => [
                'required',
                'string',
                'max:24',
                function ($attribute, $value, $fail) use ($id) {
                    $slug = Str::slug(Str::lower($value), '-');
                    if (in_array($slug, $this->reservedSubdomains)) {
                        $fail('Este nombre no está disponible.');
                    }
                    $tenantExists = \App\Models\Tenant::query()
                        ->where('id', $slug)
                        ->when($id, fn($q) => $q->where('id', '!=', $id))
                        ->exists();
                    if ($tenantExists) $fail("Ya existe una entidad con ese nombre.");
                },
            ],

            'status' => ['required', new \Illuminate\Validation\Rules\Enum(\App\Enums\TenantStatus::class)],
        ];

        // Regla de dominio (solo aplica en edit cuando uses addDomain)
        if ($this->edit_mode) {
            $base['new_domain'] = [
                'nullable',
                'string',
                'max:255',
                'regex:/^(?!-)([A-Za-z0-9-]+\.)+[A-Za-z]{2,}$/',
                Rule::unique('domains', 'domain'),
            ];
        } else {
            $base['admin_email'] = ['required', 'email', 'max:255'];

            $base['admin_password'] = [
                'required',
                'string',
                'min:8',
                'max:50',

                function ($attribute, $value, $fail) {
                    if (Str::lower($value) === Str::lower($this->name)) {
                        $fail('La contraseña no puede ser igual al nombre del entrenador.');
                    }
                },
            ];

            $base['slug'] = [
                'required',
                'string',
                'between:3,32',
                'regex:/^[a-z][a-z0-9-]*$/',
                'not_regex:/--/',
                'not_regex:/-$/',
                Rule::notIn($this->reservedSubdomains),
                Rule::unique('tenants', 'id'),
            ];
        }

        return $base;
    }
    public function updatedSlug(): void
    {
        $this->slug_manually_edited = true;
        $this->slug = Str::slug(Str::lower($this->slug), '-');
    }

    public function updatedName(): void
    {
        if ($this->edit_mode) return;

        if (!$this->slug_manually_edited) {
            $this->slug = Str::slug(Str::lower($this->name), '-');
            $this->slug_suggestion = $this->slug;
        }
    }

    public function suggestSlug(): void
    {
        if ($this->edit_mode) return;

        if (!$this->slug_manually_edited) {
            $this->slug = Str::slug(Str::lower($this->name), '-');
            $this->slug_suggestion = $this->slug;
        }
    }

    public function getFullDomainProperty(): string
    {
        $root = env('APP_DOMAIN', 'fittrack.com.ar');
        return $this->slug ? "{$this->slug}.{$root}" : "-";
    }



    // =========================================================================
    // 💾 Guardado general del Prestador
    // =========================================================================


    public function save()
    {
        $validated = $this->validate();

        if ($this->client) {
            $this->client->update([
                'name' => $this->name,
                'admin_email' => $this->admin_email,
                'status' => $this->status,
                'commercial_plan_id' => $this->commercial_plan_id,
            ]);

            $this->client->name = $this->name;
            $this->client->admin_email = $this->admin_email;
            $this->client->status = $this->status;
            $this->client->commercial_plan_id = $this->commercial_plan_id;
            $this->client->save();


            // dd($this->client);
            session()->flash('success', __('central.client_updated'));
            $this->redirect(route('central.dashboard.clients.index', $this->client), navigate: true);
        } else {
            // Alta nueva, igual que el paso anterior
            //$id = Str::slug(Str::lower($this->name), '-');
            $id = $this->slug;

            $tenant = Tenant::create([
                'id' => $id,
                'name' => $this->name,
                'admin_email' => $validated['admin_email'],
                'status' => $this->status,
                'commercial_plan_id' => $this->commercial_plan_id,
            ]);

            $tenant->name = $this->name;
            $tenant->admin_email = $this->admin_email;
            $tenant->status = $this->status;
            $tenant->commercial_plan_id = $this->commercial_plan_id;
            $tenant->save();

            $subdomain = $id . '.' . env('APP_DOMAIN', 'fittrack.com.ar');
            $tenant->domains()->create([
                'domain' => $subdomain,
            ]);
            $adminPassword = $validated['admin_password'];
            $adminMail = $validated['admin_email'];
            $tenant->run(function () use ($adminMail, $adminPassword) {
                $user = \App\Models\User::create([
                    'name' => 'Admin',
                    'email' => $adminMail,
                    'password' => \Illuminate\Support\Facades\Hash::make($adminPassword),
                ]);

                if (\Spatie\Permission\Models\Role::where('name', 'Admin')->exists()) {
                    $user->assignRole('Admin');
                }
            });
            event(new \App\Events\TenantCreatedSuccessfully($tenant, $subdomain, $adminPassword));
            session()->flash('success', __('central.client_created'));
            $this->redirect(route('central.dashboard.clients.index', $tenant), navigate: true);
        }
    }


    public function confirmBack(): void
    {
        $this->hasChanges
            ? $this->dispatch('confirming-leave')
            : $this->redirect(route('central.dashboard.clients.index'));
    }

    public function goBack(): void
    {
        $this->redirect(route('tenant.dashboard.clients.index'));
    }

    public function updated(): void
    {
        $this->hasChanges = true;
    }

    public function addDomain(): void
    {
        $this->validateOnly('new_domain');

        $domain = Str::lower(trim($this->new_domain));
        if ($domain === '') return;

        // Evitar que agreguen subdominios de tu root en esta pantalla
        $root = env('APP_DOMAIN', 'fittrack.com.ar');
        if (Str::endsWith($domain, '.' . $root)) {
            $this->addError('new_domain', 'Usá esta pantalla solo para dominios propios del entrenador (no subdominios de ' . $root . ').');
            return;
        }

        // Seguridad: debe existir un cliente (edit mode)
        if (!$this->client) {
            $this->addError('new_domain', 'Primero guardá el entrenador.');
            return;
        }

        $created = $this->client->domains()->create(['domain' => $domain]);

        // refrescar listado en memoria
        $this->domains = $this->client->domains()->orderBy('id')->get(['id', 'domain'])->toArray();
        $this->new_domain = '';

        Log::info("[SSL] Dispatching TenantCustomDomainAttached", [
            'tenant_id' => $this->client->id,
            'domain' => $domain,
            'env' => app()->environment(),
        ]);

        // Paso 2 (cuando corresponda): emitir SSL/provisionar
        event(new TenantCustomDomainAttached($this->client, $domain));

        session()->flash('success', 'Dominio agregado: ' . $domain . ' - se iniciará la provisión de SSL.');
    }

    public function removeDomain(int $domainId): void
    {
        if (!$this->client) return;

        $record = $this->client->domains()->where('id', $domainId)->first();
        if (!$record) return;

        // No eliminar el principal
        $main = $this->client->id . '.' . env('APP_DOMAIN', 'fittrack.com.ar');
        if ($record->domain === $main) {
            $this->addError('new_domain', 'No podés eliminar el subdominio principal (' . $main . ').');
            return;
        }

        $record->delete();

        $this->domains = $this->client->domains()->orderBy('id')->get(['id', 'domain'])->toArray();
        session()->flash('success', 'Dominio eliminado: ' . $record->domain);
    }

    public function render()
    {
        return view('livewire.central.dashboard.clients.form');
    }
}
