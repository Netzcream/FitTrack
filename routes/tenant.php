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

use App\Livewire\Tenant\CommercialPlans\Index as CommercialPlansIndex;
use App\Livewire\Tenant\CommercialPlans\Form as CommercialPlansForm;
use App\Livewire\Tenant\TrainingGoals\Index as TrainingGoalsIndex;
use App\Livewire\Tenant\TrainingGoals\Form as TrainingGoalsForm;

use App\Livewire\Tenant\TrainingPhases\Index as TrainingPhasesIndex;
use App\Livewire\Tenant\TrainingPhases\Form as TrainingPhasesForm;
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

        Route::middleware(['auth'])->group(function () {
            Route::get('/dashboard', function () {
                return view('tenant.dashboard');
            })->name('dashboard');

            Route::prefix('dashboard')->as('dashboard.')->group(function () {

                Route::redirect('settings', 'settings/profile')->name('settings');
                Route::get('settings/profile', Profile::class)->name('settings.profile');
                Route::get('settings/password', Password::class)->name('settings.password');
                Route::get('settings/appearance', Appearance::class)->name('settings.appearance');



                Route::prefix('exercise-levels')->name('exercise.exercise-levels.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\Exercises\ExerciseLevels\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\Exercises\ExerciseLevels\Form::class)->name('create');
                    Route::get('/{exerciseLevel}/edit', App\Livewire\Tenant\Exercises\ExerciseLevels\Form::class)->name('edit');
                });

                Route::prefix('movement-patterns')->name('exercise.movement-patterns.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\Exercises\MovementPatterns\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\Exercises\MovementPatterns\Form::class)->name('create');
                    Route::get('/{movementPattern}/edit', App\Livewire\Tenant\Exercises\MovementPatterns\Form::class)->name('edit');
                });

                Route::prefix('exercise-planes')->name('exercise.exercise-planes.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\Exercises\ExercisePlanes\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\Exercises\ExercisePlanes\Form::class)->name('create');
                    Route::get('/{exercisePlane}/edit', App\Livewire\Tenant\Exercises\ExercisePlanes\Form::class)->name('edit');
                });

                Route::prefix('exercises')->name('exercise.exercises.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\Exercises\Exercises\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\Exercises\Exercises\Form::class)->name('create');
                    Route::get('/{exercise}/edit', App\Livewire\Tenant\Exercises\Exercises\Form::class)->name('edit');
                });

                Route::prefix('equipments')->name('exercise.equipments.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\Exercises\Equipments\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\Exercises\Equipments\Form::class)->name('create');
                    Route::get('/{equipment}/edit', App\Livewire\Tenant\Exercises\Equipments\Form::class)->name('edit');
                });

                Route::prefix('muscles')->name('exercise.muscles.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\Exercises\Muscles\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\Exercises\Muscles\Form::class)->name('create');
                    Route::get('/{muscle}/edit', App\Livewire\Tenant\Exercises\Muscles\Form::class)->name('edit');
                });

                Route::prefix('muscle-groups')->name('exercise.muscle-groups.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\Exercises\MuscleGroups\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\Exercises\MuscleGroups\Form::class)->name('create');
                    Route::get('/{muscleGroup}/edit', App\Livewire\Tenant\Exercises\MuscleGroups\Form::class)->name('edit');
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
                    Route::get('/', CommercialPlansIndex::class)->name('index');
                    Route::get('/create', CommercialPlansForm::class)->name('create');
                    Route::get('/{commercialPlan}/edit', CommercialPlansForm::class)->name('edit');
                });

                Route::prefix('training-goals')->name('training-goals.')->group(function () {
                    Route::get('/', TrainingGoalsIndex::class)->name('index');
                    Route::get('/create', TrainingGoalsForm::class)->name('create');
                    Route::get('/{trainingGoal}/edit', TrainingGoalsForm::class)->name('edit');
                });
                Route::prefix('training-phases')->name('training-phases.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\TrainingPhases\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\TrainingPhases\Form::class)->name('create');
                    Route::get('/{trainingPhase}/edit', App\Livewire\Tenant\TrainingPhases\Form::class)->name('edit');
                });

                Route::prefix('communication-channels')->name('communication-channels.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\CommunicationChannels\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\CommunicationChannels\Form::class)->name('create');
                    Route::get('/{communicationChannel}/edit', App\Livewire\Tenant\CommunicationChannels\Form::class)->name('edit');
                });


                Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\PaymentMethods\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\PaymentMethods\Form::class)->name('create');
                    Route::get('/{paymentMethod}/edit', App\Livewire\Tenant\PaymentMethods\Form::class)->name('edit');
                });

                Route::prefix('tags')->name('tags.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\Tags\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\Tags\Form::class)->name('create');
                    Route::get('/{tag}/edit', App\Livewire\Tenant\Tags\Form::class)->name('edit');
                });

                Route::prefix('students')->name('students.')->group(function () {
                    Route::get('/', App\Livewire\Tenant\Students\Index::class)->name('index');
                    Route::get('/create', App\Livewire\Tenant\Students\Form::class)->name('create');
                    // Route::get('/{student}/edit', App\Livewire\Tenant\Students\Form::class)->name('edit');
                });






                Route::prefix('students/{student:uuid}')
                    ->name('students.')
                    ->group(function () {

                        Route::get('/', function (Student $student) {
                            return to_route('tenant.dashboard.students.training', $student);
                        })->name('edit');


                        Route::get('/training',   \App\Livewire\Tenant\Students\Training\Index::class)->name('training');
                        Route::get('/profile',    \App\Livewire\Tenant\Students\Profile\Form::class)->name('profile');
                        Route::get('/finance',    \App\Livewire\Tenant\Students\Finance\Index::class)->name('finance');
                        Route::get('/health',     \App\Livewire\Tenant\Students\Health\Index::class)->name('health');
                        Route::get('/metrics',    \App\Livewire\Tenant\Students\Metrics\Index::class)->name('metrics');
                        Route::get('/files',      \App\Livewire\Tenant\Students\Files\Index::class)->name('files');
                        Route::get('/messages',   \App\Livewire\Tenant\Students\Messages\Thread::class)->name('messages');
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
    });
});
