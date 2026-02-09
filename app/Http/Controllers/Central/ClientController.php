<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\App;
use \Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use App\Enums\TenantStatus;
use App\Events\TenantCreatedSuccessfully;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('central.clients.index', [
            'tenants' => Tenant::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('central.clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $reservedSubdomains = ['www', 'admin', 'mail', 'api', 'ftp', 'cpanel', 'webmail', 'fittrak', 'test'];


        $request->validate([
            'name' => [
                'required',
                'string',
                'max:24',
                function ($attribute, $value, $fail) use ($reservedSubdomains) {
                    $id = Str::slug(Str::lower($value), '-');
                    if (in_array($id, $reservedSubdomains)) {
                        $fail('Este nombre no está disponible.');
                    }
                    if (Tenant::find($id)) {
                        $fail("Ya existe una entidad con ese nombre.");
                    }
                },
            ],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['nullable', 'string', 'min:8', 'max:50'],
        ]);


        $id = Str::slug(Str::lower($request->name), '-');
        $adminPassword = (string) ($request->input('admin_password') ?: Str::random(16));
        $tenant = Tenant::create([
            'id' => $id,
            'name' => $request->name,
            'admin_email' => $request->admin_email,
        ]);
        $subdomain = $id . '.' . env('APP_DOMAIN', 'fittrack.com.ar');
        $tenant->domains()->create([
            'domain' => $subdomain,
        ]);

        $tenant->run(function () use ($tenant, $adminPassword) {
            $user = User::create([
                'name' => 'Admin',
                'email' => $tenant->admin_email,
                'password' => Hash::make($adminPassword),
            ]);
            if (Role::where('name', 'Admin')->exists()) {
                $user->assignRole('Admin');
            }
        });
        event(new TenantCreatedSuccessfully($tenant, $subdomain, $adminPassword));

        return redirect()->route('central.dashboard.clients.index', $tenant)->with('success',__('central.client_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $client)
    {
        return view('central.clients.show', [
            'tenant' => $client,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $client)
    {

        return view('central.clients.edit', [
            'entity' => $client,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $client)
    {
        #Validar que no existe la ddbb
        $request->validate([
            //'id' => 'required|unique:tenants,id,' . $tenant->id . '|string|max:24',
            'status' => [new Enum(TenantStatus::class)],
            'admin_email' => ['required', 'email', 'max:255'],
            'name' => [
                'required',
                'string',
                'max:24'],
        ]);

        $client->update([
            //'id' => $request->id,
            'name' => $request->name,
            'admin_email' => $request->admin_email,
            'status' => $request->status,

        ]);

        return redirect()->route('central.dashboard.clients.index', $client)->with('success', __('central.client_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $client)
    {
        $client->update(['status' => TenantStatus::DELETED]);
        return redirect()->route('central.dashboard.clients.index')->with('success', __('central.client_mark_to_delete'));
    }



    public function forceDestroy(Tenant $client)
    {
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

        return redirect()->route('central.dashboard.clients.index')->with('success', __('central.client_deleted',['id' => $client->id]) );
    }
}
