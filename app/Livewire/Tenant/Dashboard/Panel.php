<?php

namespace App\Livewire\Tenant\Dashboard;

use Livewire\Component;
use App\Models\Tenant\Student;
use Illuminate\Support\Carbon;

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
            'email'      => ['nullable', 'email', 'max:120'],
        ];
    }

    public function saveStudent()
    {
        $data = $this->validate();

        $student = Student::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'phone'      => $data['phone'] ?? null,
            'email'      => $data['email'] ?? null,
            'status'     => 'prospect',
            'is_user_enabled' => 'false'
        ]);

        // Redirige directo a la edición del alumno
        return redirect()->route('tenant.dashboard.students.edit', $student);
    }

    public function render()
    {
        // PLACEHOLDERS: luego engancho a queries reales
        $publishedCount     = 23;   // alumnos activos
        $draftCount         = 12;   // rutinas en curso
        $unreadContacts     = 2;    // mensajes sin responder
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
        $contactsToday  = 0;
        $recentPublishedCount = 0;
        $recentBlogCount      = 0;

        $weeks = collect(range(11, 0))->map(function ($i) {
            return Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks($i);
        });

        $chartLabels = $weeks->map(function (Carbon $w) {
            return 'S' . $w->isoWeek();
        });

        $chartNew = $weeks->map(fn() => random_int(3, 18)); // Altas
        $chartChurn = $chartNew->map(fn($n) => max(0, $n - random_int(2, 15))); // Bajas <= Altas aprox.

        $chartSeries = [
            ['name' => __('site.new_students'), 'data' => $chartNew->values()],
            ['name' => __('site.churn'),        'data' => $chartChurn->values()],
        ];

        return view('livewire.tenant.dashboard.panel', compact(
            'publishedCount',
            'draftCount',
            'unreadContacts',
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
        ));
    }
}
