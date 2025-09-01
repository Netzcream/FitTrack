<?php

use App\Http\Controllers\Central\ClientController;
use App\Livewire\Central\Dashboard\Clients\ClientsForm;
use App\Livewire\Central\Dashboard\Clients\ClientsIndex;
use App\Livewire\Central\Dashboard\DeployPanel;
use App\Livewire\Central\Dashboard\LogViewer;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


$first = true;
foreach (config('tenancy.central_domains') as $domain) {
    $prefix = $first ? '' : 'cntrl' . Str::slug($domain) . '.';
    $first = false;
    Route::domain($domain)->as($prefix)->group(function () {

        Route::get('/', function () {
            return view('welcome');
        })->name('home');

        Route::view('dashboard', 'dashboard')
            ->middleware(['auth'])
            ->name('dashboard'); // → "127-0-0-1.dashboard"

        Route::middleware(['auth'])->group(function () {

            Route::prefix('dashboard')->as('central.dashboard.')->group(function () {

                Route::redirect('settings', 'settings/profile')->name('settings'); // → "127-0-0-1.central.dashboard.settings"
                Route::get('settings/profile', Profile::class)->name('settings.profile');
                Route::get('settings/password', Password::class)->name('settings.password');
                Route::get('settings/appearance', Appearance::class)->name('settings.appearance');


                Route::prefix('clients')->name('clients.')->group(function () {
                    Route::get('/', ClientsIndex::class)->name('index');
                    Route::get('/create', ClientsForm::class)->name('create');
                    Route::get('/{client}/edit', ClientsForm::class)->name('edit');
                });



                //Route::resource('clients', ClientController::class);
                Route::delete('clients/{client}/force', [ClientController::class, 'forceDestroy'])
                    ->name('clients.force');

                Route::get('/deploy', DeployPanel::class)->name('deploy');
                Route::get('/log', LogViewer::class)->name('log-viewer');

                Route::get('/log/show/{file}', function ($file) {
                    $path = storage_path("logs/$file");
                    abort_unless(\Illuminate\Support\Facades\File::exists($path), 404);
                    return response()->file($path);
                })->name('log-viewer.show');
            });
        });

        require __DIR__ . '/auth.php';
    });
}

    /*Route::get('/__debug-session', function () {
        return [
            'session_id' => Session::getId(),
            'exists_in_db' => DB::table('sessions')->where('id', Session::getId())->exists(),
            'data' => Session::all(),
            'connection_used' => config('session.connection'),
            'db_used' => DB::connection()->getDatabaseName(),
        ];
    });*/
