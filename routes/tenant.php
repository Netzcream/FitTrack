<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\SiteConfigController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Livewire\Tenant\Settings\Appearance;
use App\Livewire\Tenant\Settings\Password;
use App\Livewire\Tenant\Settings\Profile;

use App\Livewire\Tenant\Configuration\General as ConfigGeneral;
use App\Livewire\Tenant\Configuration\Notification as ConfigNotification;
use App\Livewire\Tenant\Configuration\Appearance as ConfigAppearance;
use App\Livewire\Tenant\Landing\General as LandingGeneral;
use App\Livewire\Tenant\Landing\Cards as LandingCards;
use App\Livewire\Tenant\Landing\Banners as LandingBanners;
use App\Livewire\Tenant\Landing\Booklets as LandingBooklets;


use App\Livewire\Tenant\Contacts\Index as ContactIndex;
use App\Livewire\Tenant\Contacts\Show as ContactShow;
use App\Livewire\Tenant\Users\Form as UsersForm;
use App\Livewire\Tenant\Users\Index as UsersIndex;
use App\Livewire\Tenant\Roles\Form as RolesForm;
use App\Livewire\Tenant\Roles\Index as RolesIndex;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Middleware\EnsureTenantIsActive;

use App\Models\Tenant\Student;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/


Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    EnsureTenantIsActive::class,
    Stancl\Tenancy\Middleware\ScopeSessions::class,
])->group(function () {
    Route::name('tenant.')->group(function () {
        Route::get('/', function () {
            return view('tenant.landing');
        })->name('landing');

        Route::get('/__diagnostics', function () {
            return response()->json([
                'ok' => true,
                'tenant' => tenant()?->id,
                'session_domain' => config('session.domain'),
                'app_url' => config('app.url'),
                'sanctum_stateful' => config('sanctum.stateful'),
            ]);
        });



        Route::get('/file/{path}', function ($path) {
            $path = Storage::path($path);
            if (!file_exists($path)) {
                abort(404);
            }
            return response()->file($path);
        })->where('path', '.*')
            ->name('file');

        Route::middleware(['tenant.auth'])->group(function () {
            Route::get('/dashboard', function () {
                return view('tenant.dashboard');
            })->name('dashboard');

            Route::prefix('dashboard')->as('dashboard.')->group(function () {

                Route::redirect('settings', 'settings/profile')->name('settings');
                Route::get('settings/profile', Profile::class)->name('settings.profile');
                Route::get('settings/password', Password::class)->name('settings.password');
                Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

                Route::prefix('payments')->as('payments.')->group(function () {
                    Route::get('/', \App\Livewire\Tenant\Payments\Index::class)->name('index');
                    Route::get('/create/{student?}', \App\Livewire\Tenant\Payments\Form::class)->name('create');
                    Route::get('/{payment}/edit', \App\Livewire\Tenant\Payments\Form::class)->name('edit');
                });

                Route::prefix('contacts')->name('contacts.')->group(function () {
                    Route::get('/', ContactIndex::class)->name('index');
                    Route::get('/{contact}', ContactShow::class)->name('show');
                })->middleware('permission:gestionar contactos');

                Route::prefix('users')->name('users.')->group(function () {
                    Route::get('/', UsersIndex::class)->name('index');
                    Route::get('/create', UsersForm::class)->name('create');
                    Route::get('/{user}/edit', UsersForm::class)->name('edit');
                })->middleware('permission:gestionar usuarios');

                Route::prefix('commercial-plans')->name('commercial-plans.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\CommercialPlans\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\CommercialPlans\Form::class)->name('create');
                    Route::get('/{commercialPlan}/edit', App\Livewire\Tenant\CommercialPlans\Form::class)->name('edit');
                });



                Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\PaymentMethods\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\PaymentMethods\Form::class)->name('create');
                    Route::get('/{paymentMethod}/edit', App\Livewire\Tenant\PaymentMethods\Form::class)->name('edit');
                });

                Route::prefix('students')->name('students.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\Students\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\Students\Form::class)->name('create');
                    Route::get('/{student}/edit', App\Livewire\Tenant\Students\Form::class)->name('edit');
                });

                Route::prefix('exercises')->name('exercises.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\Exercises\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\Exercises\Form::class)->name('create');
                    Route::get('/{exercise}/edit', App\Livewire\Tenant\Exercises\Form::class)->name('edit');
                });
                Route::prefix('training-plans')->name('training-plans.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\TrainingPlan\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\TrainingPlan\Form::class)->name('create');
                    Route::get('/{trainingPlan}/edit', App\Livewire\Tenant\TrainingPlan\Form::class)->name('edit');
                });


                Route::prefix('roles')->name('roles.')->group(function () {
                    Route::get('/', RolesIndex::class)->name('index');
                    Route::get('/create', RolesForm::class)->name('create');
                    Route::get('/{role}/edit', RolesForm::class)->name('edit');
                })->middleware('permission:gestionar roles');

                Route::redirect('landing', 'landing/general')->name('landing')->middleware('permission:gestionar recursos');
                Route::get('landing/general', LandingGeneral::class)->name('landing.general')->middleware('permission:gestionar recursos');
                Route::get('landing/cards', LandingCards::class)->name('landing.cards')->middleware('permission:gestionar recursos');
                Route::get('landing/banners', LandingBanners::class)->name('landing.banners')->middleware('permission:gestionar recursos');
                Route::get('landing/booklets', LandingBooklets::class)->name('landing.booklets')->middleware('permission:gestionar recursos');


                Route::redirect('configuration', 'configuration/general')->name('configuration')->middleware('permission:gestionar recursos');
                Route::get('configuration/general', ConfigGeneral::class)->name('configuration.general')->middleware('permission:gestionar recursos');
                Route::get('configuration/notifications', ConfigNotification::class)->name('configuration.notifications')->middleware('permission:gestionar recursos');
                Route::get('configuration/appearance', ConfigAppearance::class)->name('configuration.appearance')->middleware('permission:gestionar recursos');
            });
        });


        require __DIR__ . '/tenant-auth.php';
        require __DIR__ . '/tenant-student.php';
    });
});
