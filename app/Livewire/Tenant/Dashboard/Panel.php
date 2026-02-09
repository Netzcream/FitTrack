<?php

namespace App\Livewire\Tenant\Dashboard;

use Livewire\Component;
use App\Events\Tenant\StudentCreated;
use App\Models\Tenant\Student;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class Panel extends Component
{
    // Campos mínimos para el alta rápida
    public string $first_name = '';
    public string $last_name  = '';
    public ?string $phone     = null;
    public ?string $email     = null;

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'email'      => ['required', 'email', 'max:120'],
        ];
    }

    public function saveStudent()
    {
        $data = $this->validate();
        $studentRole = Role::firstOrCreate(['name' => 'Alumno']);

        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => trim($data['first_name'] . ' ' . $data['last_name']),
                'password' => Str::random(20),
            ]
        );
        if (! $user->hasRole($studentRole)) {
            $user->assignRole($studentRole);
        }

        $student = Student::create([
            'user_id' => $user->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'status' => 'prospect',
            'is_user_enabled' => true,
        ]);

        $token = Password::broker()->createToken($user);
        $registrationUrl = route('tenant.password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        StudentCreated::dispatch(
            $student,
            Auth::id() ? (string) Auth::id() : null,
            $registrationUrl,
            tenant('id') ? (string) tenant('id') : null
        );



        // Redirige directo a la edición del alumno
        return redirect()->route('tenant.dashboard.students.edit', $student);
    }

    public function markAllSupportAsRead(): void
    {
        $tenantId = tenant('id');
        if (! $tenantId) {
            return;
        }

        $conversationIds = \App\Models\Central\ConversationParticipant::where('participant_type', \App\Enums\ParticipantType::TENANT)
            ->where('participant_id', $tenantId)
            ->pluck('conversation_id');

        foreach ($conversationIds as $cid) {
            app(\App\Services\Central\MessagingService::class)->markAsRead(
                (int)$cid,
                \App\Enums\ParticipantType::TENANT,
                $tenantId
            );
        }
    }

    public function render()
    {
        // Datos reales para mensajes pendientes
        $tenantId = tenant('id');
        $centralMessaging = app(\App\Services\Central\MessagingService::class);
        $tenantMessaging  = app(\App\Services\Tenant\MessagingService::class);

        // Soporte (Central <-> Entrenador)
        $unreadSupport = $tenantId ? $centralMessaging->getUnreadCount(\App\Enums\ParticipantType::TENANT, $tenantId) : 0;

        // Mensajes de Alumnos (Tenant <-> Alumnos)
        $unreadStudentMessages = $tenantId
            ? $tenantMessaging->getUnreadCount(\App\Enums\ParticipantType::TENANT, (string) $tenantId)
            : 0;

        // Contactos desde la web (reales): entradas creadas hoy
        $webContactsPending = \App\Models\Contact::whereDate('created_at', Carbon::today())->count();

        // Métricas reales
        $publishedCount     = Student::where('status', 'active')->count(); // alumnos activos
        $blogCount          = 7;    // widgets varios / métricas extra
        $recentPublishedCovers = []; // collage opcional


        // barras semanales (placeholder)
        $publishedLast8Weeks = collect(range(1, 8))->map(fn($w) => [
            'label' => "S{$w}",
            'value' => rand(0, 8),
        ])->all();
        $publishedPeak = max(1, collect($publishedLast8Weeks)->max('value'));

        // listas (placeholder)
        $recentContacts = collect([]); // contactos/mensajes recientes
        $topPackages    = collect([]); // top rutinas/planes (naming a definir)

        $currencySymbol = '$';
        $readyToPublish = 0;
        // Mensajes recibidos hoy para este Entrenador
        $contactsToday  = 0;
        if ($tenantId) {
            $conversationIds = \App\Models\Central\ConversationParticipant::where('participant_type', \App\Enums\ParticipantType::TENANT)
                ->where('participant_id', $tenantId)
                ->pluck('conversation_id');

            $contactsToday = \App\Models\Central\Message::whereIn('conversation_id', $conversationIds)
                ->whereDate('created_at', Carbon::today())
                ->count();
        }
        $recentPublishedCount = 0;
        $recentBlogCount      = 0;

        // Últimas 8 semanas (de lunes a domingo)
        $weeks = collect(range(7, 0))->map(function ($i) {
            return Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks($i);
        });

        $chartLabels = $weeks->map(function (Carbon $weekStart) {
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            return $weekStart->format('d M') . ' - ' . $weekEnd->format('d M');
        });

        // Contar alumnos nuevos por semana
        $chartNew = $weeks->map(function (Carbon $weekStart) {
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            return Student::whereBetween('created_at', [$weekStart, $weekEnd])->count();
        });

        $chartSeries = [
            ['name' => __('site.new_students'), 'data' => $chartNew->values()],
        ];

        // Uso de IA (si tiene acceso)
        $tenant = tenant();
        $hasAiAccess = false;
        $aiUsage = [];
        $aiChartData = [];

        if ($tenant && $tenant->plan) {
            $planSlug = $tenant->plan->slug ?? '';
            $hasAiAccess = in_array($planSlug, ['pro', 'equipo']);

            if ($hasAiAccess) {
                $aiUsage = $tenant->getAiGenerationUsage();

                // Obtener historial de últimos 6 meses
                $history = $tenant->getAiUsageHistory(6)->reverse();

                $aiChartData = [
                    'labels' => $history->pluck('month')->map(function($month) {
                        return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y');
                    })->toArray(),
                    'series' => [
                        ['name' => 'Usado', 'data' => $history->pluck('usage_count')->toArray()],
                        ['name' => 'Límite', 'data' => $history->pluck('limit')->toArray()],
                    ],
                ];
            }
        }

        return view('livewire.tenant.dashboard.panel', compact(
            'publishedCount',
            // 'draftCount' removed
            'unreadStudentMessages',
            'unreadSupport',
            'webContactsPending',
            'blogCount',
            'publishedLast8Weeks',
            'publishedPeak',
            'recentContacts',
            'topPackages',
            'currencySymbol',
            'readyToPublish',
            'contactsToday',
            'recentPublishedCount',
            'recentBlogCount',
            'chartLabels',
            'chartSeries',
            'hasAiAccess',
            'aiUsage',
            'aiChartData',
        ));
    }
}

