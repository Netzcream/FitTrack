<?php

namespace App\Livewire\Central\Dashboard\Clients;

use App\Enums\TenantStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Tenant;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.app')]
class ClientsIndex extends Component
{
    use WithPagination;

    public string $sortBy = 'id';
    public string $sortDirection = 'asc';
    public string $search = '';
    public ?string $tenantToDelete = null;
    public ?string $tenantToDeleteForce = null;


    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function filter()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    #[Computed]
    public function clients()
    {
        return Tenant::query()
            ->when(
                $this->search,
                fn($q) => $q
                    ->where('id', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }


    public function confirmDelete(string $uuid): void
    {
        $this->tenantToDelete = $uuid;
    }

    public function delete(): void
    {
        $c = \App\Models\Tenant::where('id', $this->tenantToDelete)->first();

        $c->update(['status' => TenantStatus::DELETED]);
        $this->dispatch('modal-close', name: 'confirm-delete-client');
    }

    public function confirmDeleteForce(string $uuid): void
    {
        $this->tenantToDeleteForce = $uuid;
    }

    public function deleteForce(): void
    {

        $client = \App\Models\Tenant::where('id', $this->tenantToDeleteForce)->first();
        $domain = $client->mainDomain();
        $client->delete(); // Soft-delete o logical delete

        if (App::environment('production')) {

            // Eliminar certificado SSL
            if ($domain) {
                exec("sudo certbot delete --cert-name $domain --non-interactive --quiet", $output, $code);

                if ($code !== 0) {
                    Log::warning("No se pudo eliminar el certificado SSL de {$domain}. Código: {$code}", $output);
                }
            }
            exec("sudo a2dissite {$domain}.conf");
            exec("sudo a2dissite {$domain}-le-ssl.conf");

            foreach (['sites-available', 'sites-enabled'] as $dir) {
                foreach (['', '-le-ssl'] as $suffix) {
                    $path = "/etc/apache2/{$dir}/{$domain}{$suffix}.conf";
                    $cmd = "sudo rm -f $path";
                    exec($cmd, $output, $code);

                    if ($code === 0) {
                        Log::info("Archivo Apache eliminado correctamente: {$path}");
                    } else {
                        Log::warning("No se pudo eliminar el archivo Apache: {$path}. Código: {$code}");
                    }
                }
            }

            // Recargar Apache
            exec("sudo systemctl reload apache2");
        }

        $this->dispatch('modal-close', name: 'confirm-delete-client-force');
    }


    public function render()
    {
        return view('livewire.central.dashboard.clients.index');
    }
}
