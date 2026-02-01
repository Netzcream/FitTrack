<?php

namespace App\Livewire\Tenant\TrainingPlan;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Exercise;
use App\Models\Tenant\Student;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?TrainingPlan $plan = null;

    public string $name = '';
    public string $description = '';
    public string $goal = '';
    public string $duration = '';
    public bool $is_active = true;

    public bool $editMode = false;
    public bool $back = false;

    public string $exerciseSearch = '';
    public array $selectedExercises = [];
    public $availableExercises = [];

    /** Propiedades para generación con IA */
    public bool $showAiModal = false;
    public string $aiPrompt = '';
    public bool $generatedByAi = false; // Rastrea si fue generado por IA

    /** Modal de detalles del ejercicio */
    public bool $showExerciseDetails = false;
    public ?array $selectedExerciseDetails = null;

    /** Alumno asignado (ID numérico) o null */
    public ?int $student_id = null;

    /**
     * Verifica si el tenant actual tiene acceso a generación con IA.
     * Solo planes Pro y Equipo tienen acceso.
     */
    public function getHasAiAccessProperty(): bool
    {
        $tenant = tenant();

        if (!$tenant || !$tenant->plan) {
            return false;
        }

        $planSlug = $tenant->plan->slug ?? '';

        return in_array($planSlug, ['pro', 'equipo']);
    }

    /**
     * Obtiene información sobre el uso de IA (usado, límite, disponible, porcentaje).
     */
    public function getAiUsageProperty(): array
    {
        $tenant = tenant();

        if (!$tenant) {
            return [
                'used' => 0,
                'limit' => 0,
                'available' => 0,
                'percentage' => 0,
                'has_limit' => false,
                'is_exceeded' => false,
            ];
        }

        return $tenant->getAiGenerationUsage();
    }

    /** Fechas de vigencia (solo si hay student_id) */
    public ?string $assigned_from = null;   // 'Y-m-d'
    public ?string $assigned_until = null;  // 'Y-m-d'

    /* -------------------- Mount -------------------- */
    public function mount(?TrainingPlan $trainingPlan): void
    {
        if ($trainingPlan && $trainingPlan->exists) {
            $this->plan = $trainingPlan;
            $this->name = $trainingPlan->name;
            $this->description = $trainingPlan->description ?? '';
            $this->goal = $trainingPlan->goal ?? '';
            $this->duration = (string) ($trainingPlan->duration ?? '');
            $this->is_active = (bool) $trainingPlan->is_active;
            $this->editMode = true;

            $this->student_id    = $trainingPlan->student_id;
            $this->assigned_from = optional($trainingPlan->assigned_from)?->format('Y-m-d');
            $this->assigned_until = optional($trainingPlan->assigned_until)?->format('Y-m-d');

            // Cargar exercises_data
            $exercisesData = $trainingPlan->exercises_data ?? [];
            $this->selectedExercises = [];

            if (!empty($exercisesData)) {
                $exerciseIds = collect($exercisesData)->pluck('exercise_id')->toArray();
                $exercises = Exercise::whereIn('id', $exerciseIds)->get()->keyBy('id');

                foreach ($exercisesData as $item) {
                    $exercise = $exercises->get($item['exercise_id']);
                    if ($exercise) {
                        $this->selectedExercises[] = [
                            'id' => $exercise->id,
                            'uuid' => $exercise->uuid,
                            'name' => $exercise->name,
                            'category' => $exercise->category,
                            'image' => $exercise->getFirstMediaUrl('images', 'thumb'),
                            'day' => $item['day'] ?? 1,
                            'order' => $item['order'] ?? 1,
                            'detail' => $item['detail'] ?? '',
                            'notes' => $item['notes'] ?? '',
                        ];
                    }
                }
            }
        } else {
            // Si viene ?student=<uuid> desde la vista del alumno, convertir a ID
            if ($uuid = request()->query('student')) {
                $student = Student::where('uuid', $uuid)->first();
                if ($student) {
                    $this->student_id = $student->id;
                    // Defaults de vigencia cuando creamos un plan asignado
                    $this->assigned_from  = now()->toDateString();
                    $this->assigned_until = now()->addMonth()->toDateString();
                }
            }
        }
    }

    /* -------------------- Reglas -------------------- */
    protected function rules(): array
    {
        $rules = [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'goal'        => ['nullable', 'string', 'max:255'],
            'duration'    => ['nullable'],
            'is_active'   => ['boolean'],

            'selectedExercises.*.day'    => ['nullable', 'integer', 'min:1', 'max:7'],
            'selectedExercises.*.detail' => ['nullable', 'string', 'max:50'],
            'selectedExercises.*.notes'  => ['nullable', 'string', 'max:255'],
        ];

        // Validar fechas solo si el plan está asignado a un alumno
        if ($this->student_id) {
            $rules['assigned_from']  = ['nullable', 'date'];
            $rules['assigned_until'] = ['nullable', 'date', 'after_or_equal:assigned_from'];
        }

        return $rules;
    }

    /* -------------------- Búsqueda ejercicios -------------------- */
    public function updatedExerciseSearch(): void
    {
        if (strlen($this->exerciseSearch) < 2) {
            $this->availableExercises = [];
            return;
        }

        $excludeIds = collect($this->selectedExercises)->pluck('id')->all();
        $this->availableExercises = Exercise::active()
            ->search($this->exerciseSearch)
            ->whereNotIn('id', $excludeIds)
            ->take(5)
            ->get(['id', 'uuid', 'name', 'category'])
            ->toArray();
    }

    /* -------------------- Agregar/Quitar ejercicios -------------------- */
    #[On('exercise-created')]
    public function onExerciseCreated($exerciseId)
    {
        // Agregar automáticamente el ejercicio recién creado
        $this->addExercise($exerciseId);

        // Limpiar búsqueda
        $this->exerciseSearch = '';
        $this->availableExercises = [];
    }

    public function addExercise(int $id): void
    {
        $exercise = Exercise::find($id);
        if (!$exercise) return;

        $this->selectedExercises[] = [
            'id' => $exercise->id,
            'uuid' => $exercise->uuid,
            'name' => $exercise->name,
            'category' => $exercise->category,
            'image' => $exercise->getFirstMediaUrl('images', 'thumb'),
            'day' => 1,
            'order' => count($this->selectedExercises) + 1,
            'detail' => '',
            'notes' => '',
        ];

        $this->exerciseSearch = '';
        $this->availableExercises = [];
    }

    public function removeExercise(int $index): void
    {
        array_splice($this->selectedExercises, $index, 1);
        $this->reorderExercises();
    }

    public function moveUp(int $index): void
    {
        if ($index === 0) return;
        [$this->selectedExercises[$index - 1], $this->selectedExercises[$index]] =
            [$this->selectedExercises[$index], $this->selectedExercises[$index - 1]];
        $this->reorderExercises();
    }

    public function moveDown(int $index): void
    {
        if ($index === count($this->selectedExercises) - 1) return;
        [$this->selectedExercises[$index + 1], $this->selectedExercises[$index]] =
            [$this->selectedExercises[$index], $this->selectedExercises[$index + 1]];
        $this->reorderExercises();
    }

    protected function reorderExercises(): void
    {
        foreach ($this->selectedExercises as $i => &$ex) {
            $ex['order'] = $i + 1;
        }
    }

    /* -------------------- Guardar -------------------- */
    public function save(): void
    {
        $this->validate();

        // Crear o editar
        $plan = $this->editMode && $this->plan ? $this->plan : new TrainingPlan();

        $plan->fill([
            'name'        => $this->name,
            'description' => $this->description,
            'goal'        => $this->goal,
            'duration'    => $this->duration,
            'is_active'   => $this->is_active,
            'student_id'  => $this->student_id, // null para planes generales
            'created_by_ai' => $this->generatedByAi || ($plan->created_by_ai ?? false), // Mantener si ya estaba marcado
        ]);


        // 🔹 Validar solapamiento de fechas para el alumno
        if ($this->student_id && $this->is_active) {
            $graceDays = 2;

            $from  = $plan->assigned_from ?? now();
            $until = $plan->assigned_until ?? now()->addDays(30);

            $overlap = TrainingPlan::where('student_id', $this->student_id)
                ->where('id', '!=', $plan->id)
                ->where('is_active', true)
                ->where(function ($q) use ($from, $until, $graceDays) {
                    $q->whereBetween('assigned_from', [$from->copy()->subDays($graceDays), $until->copy()->addDays($graceDays)])
                        ->orWhereBetween('assigned_until', [$from->copy()->subDays($graceDays), $until->copy()->addDays($graceDays)])
                        ->orWhere(function ($qq) use ($from, $until, $graceDays) {
                            $qq->where('assigned_from', '<=', $from->copy()->subDays($graceDays))
                                ->where('assigned_until', '>=', $until->copy()->addDays($graceDays));
                        });
                })
                ->exists();

            if ($overlap) {
                $this->addError('assigned_from', __('training_plans.overlap_error'));
                return;
            }
        }


        $plan->save();
        $plan->refresh();

        // Guardar exercises_data como JSON
        $exercisesData = [];

        foreach ($this->selectedExercises as $ex) {
            $exercisesData[] = [
                'exercise_id' => $ex['id'],
                'day'    => $ex['day'] ?? 1,
                'order'  => $ex['order'] ?? 1,
                'detail' => $ex['detail'] ?? '',
                'notes'  => $ex['notes'] ?? '',
            ];
        }

        $plan->exercises_data = $exercisesData;
        $plan->save();

        // Refrescar para UI
        $this->plan = $plan;

        // Feedback
        $this->dispatch('saved');
        session()->flash('success', __('training_plans.saved'));

        // Navegación
        if ($this->back) {
            if ($this->student_id && $plan->student) {
                $this->redirectRoute('tenant.dashboard.students.training-plans', [
                    'student' => $plan->student->uuid,
                ]);
            } else {
                $this->redirectRoute('tenant.dashboard.training-plans.index');
            }
            return;
        }

        if (!$this->editMode) {
            if ($this->student_id && $plan->student) {
                $this->redirectRoute('tenant.dashboard.students.training-plans', [
                    'student' => $plan->student->uuid,
                ]);
            } else {
                $this->redirectRoute('tenant.dashboard.training-plans.edit', $plan->uuid);
            }
        }
    }

    public function clearSearch(): void
    {
        $this->exerciseSearch = '';
        $this->availableExercises = [];
    }

    /* -------------------- Modal de detalles del ejercicio -------------------- */
    public function viewExerciseDetails(int $exerciseId, int $index = 0): void
    {
        $exercise = Exercise::find($exerciseId);

        if (!$exercise) {
            return;
        }

        $meta = $exercise->meta ?? [];

        $this->selectedExerciseDetails = [
            'id' => $exercise->id,
            'uuid' => $exercise->uuid,
            'name' => $exercise->name,
            'category' => $exercise->category,
            'description' => $exercise->description,
            'equipment' => $exercise->equipment,
            'level' => $exercise->level,
            'difficulty_level' => $meta['difficulty_level'] ?? $exercise->level ?? null,
            'muscle_group' => $meta['muscle_group'] ?? null,
            'video_url' => $meta['video_url'] ?? null,
            'images' => $exercise->getMedia('images')->map(function ($media) {
                return [
                    'url' => $media->getUrl(),
                    'thumb' => $media->getUrl('thumb'),
                ];
            })->toArray(),
        ];

        $this->showExerciseDetails = true;
    }

    public function closeExerciseDetails(): void
    {
        $this->showExerciseDetails = false;
        $this->selectedExerciseDetails = null;
    }

    /* -------------------- Generación con IA -------------------- */
    public function openAiModal(): void
    {
        // Verificar acceso según plan comercial
        if (!$this->hasAiAccess) {
            session()->flash('error', 'La generación con IA solo está disponible en los planes Pro y Equipo.');
            return;
        }

        // Verificar límite de uso mensual
        $tenant = tenant();
        if (!$tenant->canUseAiGeneration()) {
            $usage = $this->aiUsage;
            session()->flash('error', "Has alcanzado el límite mensual de {$usage['limit']} generaciones con IA. Se renovará el 1º del próximo mes.");
            return;
        }

        $this->showAiModal = true;
        $this->aiPrompt = '';
    }

    public function closeAiModal(): void
    {
        $this->showAiModal = false;
        $this->aiPrompt = '';
    }

    public function generateWithAi(): void
    {
        // Verificar acceso según plan comercial
        if (!$this->hasAiAccess) {
            $this->addError('aiPrompt', 'La generación con IA solo está disponible en los planes Pro y Equipo.');
            return;
        }

        // Verificar límite de uso mensual
        $tenant = tenant();
        if (!$tenant->canUseAiGeneration()) {
            $usage = $this->aiUsage;
            $this->addError('aiPrompt', "Has alcanzado el límite mensual de {$usage['limit']} generaciones. Se renovará el 1º del próximo mes.");
            return;
        }

        $this->validate(['aiPrompt' => 'required|string|min:10|max:500']);

        Log::info('[AI Generation] Iniciando generación de plan', [
            'tenant_id' => $tenant->id,
            'prompt' => $this->aiPrompt,
        ]);

        try {
            // Obtener todos los ejercicios activos del tenant (solo IDs y nombres para diccionario compacto)
            $exercises = Exercise::active()
                ->select('id', 'name', 'category')
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->map(fn($e) => [
                    'i' => $e->id,
                    'n' => $e->name,
                    'c' => $e->category,
                ])
                ->toArray();

            if (empty($exercises)) {
                $this->addError('aiPrompt', 'No hay ejercicios disponibles en el sistema.');
                Log::warning('[AI Generation] No hay ejercicios disponibles', ['tenant_id' => $tenant->id]);
                return;
            }

            Log::info('[AI Generation] Ejercicios obtenidos', [
                'tenant_id' => $tenant->id,
                'exercises_count' => count($exercises),
            ]);

            // Datos actuales del formulario
            $currentData = [
                'name' => $this->name ?: null,
                'goal' => $this->goal ?: null,
                'duration' => $this->duration ?: null,
                'description' => $this->description ?: null,
            ];

            // Prompt optimizado para JSON mode nativo de OpenAI
            $systemPrompt = 'You are a specialized training plan generator. You MUST respond with valid JSON only. All text fields must be in Spanish. If the request is not about training plans, respond with {"error":"invalid_request"}.';

            $userPrompt = "User goal: {$this->aiPrompt}\n\n";
            $userPrompt .= "Current form: " . json_encode($currentData, JSON_UNESCAPED_UNICODE) . "\n\n";
            $userPrompt .= "Available exercises (i=id, n=name, c=category):\n" . json_encode($exercises, JSON_UNESCAPED_UNICODE) . "\n\n";
            $userPrompt .= "Generate a training plan with this JSON structure:\n";
            $userPrompt .= '{"name":"Plan name","goal":"Goal","duration":"1 mes","description":"Concise description (100-200 chars)","exercises":[{"i":5,"d":1,"o":1,"t":"3x12","notes":"Optional notes"}]}' . "\n\n";
            $userPrompt .= "CRITICAL Rules:\n";
            $userPrompt .= "1) Use 3-5 training days if not specified\n";
            $userPrompt .= "2) IMPORTANT: Day numbers (d) are CONSECUTIVE training sessions, NOT days of the week\n";
            $userPrompt .= "   - For '3 times per week': use days 1, 2, 3 (NOT 1, 3, 5)\n";
            $userPrompt .= "   - For '4 times per week': use days 1, 2, 3, 4 (NOT 1, 2, 4, 6)\n";
            $userPrompt .= "   - For '5 times per week': use days 1, 2, 3, 4, 5\n";
            $userPrompt .= "3) Minimum 4 exercises per day\n";
            $userPrompt .= "4) ALWAYS prefer existing exercise IDs (i>0) - use the available exercises list\n";
            $userPrompt .= "5) Each exercise can appear in multiple days (e.g. days 1, 2, 3), but NEVER repeat the same exercise within the SAME day\n";
            $userPrompt .= "6) For NEW exercises (i=0) you MUST provide ALL these fields:\n";
            $userPrompt .= "   - i: 0 (indicates new exercise)\n";
            $userPrompt .= "   - n: EXERCISE NAME - SHORT name (MAX 40 chars, e.g. 'Estiramiento cuádriceps', 'Press de banca') - THIS IS MANDATORY\n";
            $userPrompt .= "   - c: category (MANDATORY - one of: 'Piernas', 'Pecho', 'Espalda', 'Brazos', 'Hombros', 'Core', 'Cardio', 'Estiramiento')\n";
            $userPrompt .= "   - l: level ('principiante', 'intermedio', or 'avanzado')\n";
            $userPrompt .= "   - desc: detailed description explaining how to do the exercise (in Spanish)\n";
            $userPrompt .= "   - d: day number (CONSECUTIVE: 1, 2, 3, 4...)\n";
            $userPrompt .= "   - o: order in that day (1, 2, 3...)\n";
            $userPrompt .= "   - t: sets/reps (e.g. '3x12', '4x8', '30 segundos')\n";
            $userPrompt .= "   - notes: additional notes (optional - can be empty string)\n";
            $userPrompt .= "\nExample of NEW exercise: {\"i\":0,\"n\":\"Estiramiento cuádriceps\",\"c\":\"Piernas\",\"l\":\"principiante\",\"desc\":\"De pie, flexiona una pierna y sujeta el pie con la mano\",\"d\":1,\"o\":1,\"t\":\"30 seg\",\"notes\":\"\"}\n";
            $userPrompt .= "\nExample using EXISTING exercise: {\"i\":5,\"d\":1,\"o\":2,\"t\":\"3x12\",\"notes\":\"Mantén la espalda recta\"}\n";
            $userPrompt .= "7) Plan 'description' field should be CONCISE (100-200 chars) - summarize the plan's purpose clearly\n";
            $userPrompt .= "8) Field 'n' is the EXERCISE NAME for new exercises (i=0) - NEVER leave it empty\n";
            $userPrompt .= "9) Field 'notes' is for additional coaching notes - this is optional\n";
            $userPrompt .= "10) Category 'c' is ABSOLUTELY REQUIRED for i=0 exercises - no exceptions\n";
            $userPrompt .= "11) Max 20 exercises total\n";
            $userPrompt .= "12) All text in Spanish";

            Log::info('[AI Generation] Llamando al servicio de IA', [
                'tenant_id' => $tenant->id,
                'prompt_length' => strlen($userPrompt),
                'model' => 'gpt-4o-mini',
                'using_json_mode' => true,
            ]);

            // Llamar al servicio de IA con JSON mode nativo
            $aiService = app(\App\Services\Ai\ApiService::class);
            $response = $aiService->respond($userPrompt, [
                'system' => $systemPrompt,
                'model' => 'gpt-4o-mini',
                'temperature' => 0.3,
                'max_tokens' => 2500,
                'response_format' => 'json_object', // Forzar JSON nativo
            ]);

            Log::info('[AI Generation] Respuesta recibida de IA', [
                'tenant_id' => $tenant->id,
                'has_text' => !empty($response['text']),
                'text_length' => strlen($response['text'] ?? ''),
                'finish_reason' => $response['raw']['choices'][0]['finish_reason'] ?? null,
            ]);

            // Verificar si la respuesta fue truncada por límite de tokens
            $finishReason = $response['raw']['choices'][0]['finish_reason'] ?? null;
            if ($finishReason === 'length') {
                Log::warning('[AI Generation] Respuesta truncada por límite de tokens', ['tenant_id' => $tenant->id]);
                throw new \Exception('La respuesta de la IA fue demasiado larga. Intenta con un objetivo más específico o menos ejercicios.');
            }

            $text = $response['text'] ?? null;

            if (!$text) {
                Log::error('[AI Generation] No se recibió respuesta de la IA', [
                    'tenant_id' => $tenant->id,
                    'response' => $response,
                ]);
                throw new \Exception('No se recibió respuesta de la IA.');
            }

            Log::info('[AI Generation] Texto de respuesta recibido', [
                'tenant_id' => $tenant->id,
                'text_preview' => substr($text, 0, 200),
            ]);

            // Con JSON mode nativo, no necesitamos limpiar markdown ni caracteres de control
            // El texto ya viene como JSON puro
            $data = json_decode($text, true);
            $jsonError = json_last_error();

            if ($jsonError !== JSON_ERROR_NONE) {
                Log::error('[AI Generation] Error al decodificar JSON', [
                    'tenant_id' => $tenant->id,
                    'json_error' => json_last_error_msg(),
                    'json_error_code' => $jsonError,
                    'text' => $text,
                ]);
                throw new \Exception('Respuesta JSON inválida de la IA: ' . json_last_error_msg());
            }

            if (!isset($data['exercises'])) {
                Log::error('[AI Generation] Respuesta JSON sin campo exercises', [
                    'tenant_id' => $tenant->id,
                    'data_keys' => array_keys($data),
                    'data' => $data,
                ]);
                throw new \Exception('Respuesta JSON inválida de la IA: falta el campo exercises.');
            }

            Log::info('[AI Generation] JSON decodificado correctamente', [
                'tenant_id' => $tenant->id,
                'exercises_count' => count($data['exercises']),
            ]);

            // Log detallado de TODOS los ejercicios recibidos para debug
            Log::info('[AI Generation] Ejercicios recibidos de IA (detalle completo)', [
                'tenant_id' => $tenant->id,
                'exercises' => $data['exercises'],
            ]);

            // Validar que solo se está creando un plan (prevenir otros usos)
            if (isset($data['error']) && $data['error'] === 'invalid_request') {
                throw new \Exception('Esta función solo está disponible para generar planes de entrenamiento.');
            }

            // Procesar ejercicios generados
            $createdExercises = [];
            $validExercises = [];

            // Actualizar campos del formulario si la IA los generó
            if (!empty($data['name']) && empty($this->name)) {
                $this->name = $data['name'];
            }
            if (!empty($data['goal']) && empty($this->goal)) {
                $this->goal = $data['goal'];
            }
            if (!empty($data['duration']) && empty($this->duration)) {
                $this->duration = $data['duration'];
            }
            if (!empty($data['description']) && empty($this->description)) {
                $this->description = $data['description'];
            }

            // Rastrear ejercicios ya usados por día para evitar duplicados en el mismo día
            $usedExerciseIds = []; // ['day' => [id1, id2, ...]]

            foreach ($data['exercises'] as $index => $item) {
                Log::info('[AI Generation] Procesando ejercicio', [
                    'tenant_id' => $tenant->id,
                    'index' => $index,
                    'exercise_data' => $item,
                ]);
                $exerciseId = $item['i'] ?? 0;
                $day = (int) ($item['d'] ?? 1);

                // Saltar si el ejercicio ya fue usado EN EL MISMO DÍA (evitar duplicados por día)
                if ($exerciseId > 0 && isset($usedExerciseIds[$day]) && in_array($exerciseId, $usedExerciseIds[$day])) {
                    Log::warning('[AI Generation] Ejercicio duplicado en el mismo día detectado y omitido', [
                        'tenant_id' => $tenant->id,
                        'exercise_id' => $exerciseId,
                        'day' => $day,
                    ]);
                    continue;
                }

                if ($exerciseId === 0) {
                    // Ejercicio sugerido que no existe - CREAR AUTOMÁTICAMENTE
                    $exerciseName = $item['n'] ?? 'Ejercicio sin nombre';

                    // VALIDAR: Nombre NO PUEDE estar vacío
                    if (empty($exerciseName) || trim($exerciseName) === '' || $exerciseName === 'Ejercicio sin nombre') {
                        Log::error('[AI Generation] Ejercicio sin nombre válido omitido', [
                            'tenant_id' => $tenant->id,
                            'exercise_data' => $item,
                        ]);
                        continue; // Saltar ejercicios sin nombre
                    }

                    // VALIDAR: Nombre no debe exceder 40 caracteres
                    if (strlen($exerciseName) > 40) {
                        $exerciseName = substr($exerciseName, 0, 37) . '...';
                        Log::warning('[AI Generation] Nombre de ejercicio truncado', [
                            'tenant_id' => $tenant->id,
                            'original_name' => $item['n'],
                            'truncated_name' => $exerciseName,
                        ]);
                    }

                    // VALIDAR: Categoría es OBLIGATORIA para ejercicios nuevos
                    $exerciseCategory = $item['c'] ?? null;

                    // Si no tiene categoría, intentar inferirla del nombre
                    if (empty($exerciseCategory)) {
                        $exerciseCategory = $this->inferCategoryFromName($exerciseName);

                        if ($exerciseCategory) {
                            Log::warning('[AI Generation] Categoría inferida del nombre', [
                                'tenant_id' => $tenant->id,
                                'exercise_name' => $exerciseName,
                                'inferred_category' => $exerciseCategory,
                            ]);
                        } else {
                            Log::error('[AI Generation] Ejercicio sin categoría omitido (no se pudo inferir)', [
                                'tenant_id' => $tenant->id,
                                'exercise_name' => $exerciseName,
                            ]);
                            continue; // Saltar solo si no se pudo inferir
                        }
                    }

                    // VALIDAR: Nivel es OBLIGATORIO para ejercicios nuevos
                    $exerciseLevel = $item['l'] ?? null;
                    if (empty($exerciseLevel)) {
                        Log::warning('[AI Generation] Ejercicio sin nivel, usando "intermedio" por defecto', [
                            'tenant_id' => $tenant->id,
                            'exercise_name' => $exerciseName,
                        ]);
                        $exerciseLevel = 'intermedio';
                    }

                    $exerciseEquipment = $item['equip'] ?? 'Sin equipamiento';

                    // Usar la descripción generada por IA (desc) - ya no ponemos texto genérico
                    $exerciseDescription = $item['desc'] ?? null;

                    // Crear nuevo ejercicio marcado como created_by_ai
                    $newExercise = Exercise::create([
                        'name' => $exerciseName,
                        'category' => $exerciseCategory,
                        'level' => $exerciseLevel,
                        'equipment' => $exerciseEquipment,
                        'description' => $exerciseDescription,
                        'is_active' => true,
                        'created_by_ai' => true, // Marcar como creado por IA
                    ]);

                    $createdExercises[] = $exerciseName;
                    $exerciseId = $newExercise->id;
                    $exercise = $newExercise;
                } else {
                    // Verificar que existe
                    $exercise = Exercise::find($exerciseId);
                    if (!$exercise) {
                        continue; // Saltar si no se encuentra
                    }

                    // Si el ejercicio no tiene descripción y la IA la generó, actualizarla
                    if (empty($exercise->description) && !empty($item['desc'])) {
                        $exercise->description = $item['desc'];
                        $exercise->save();
                    }
                }

                // Registrar que este ejercicio ya fue usado en este día
                if (!isset($usedExerciseIds[$day])) {
                    $usedExerciseIds[$day] = [];
                }
                $usedExerciseIds[$day][] = $exerciseId;

                $validExercises[] = [
                    'id' => $exercise->id,
                    'uuid' => $exercise->uuid,
                    'name' => $exercise->name,
                    'category' => $exercise->category,
                    'image' => $exercise->getFirstMediaUrl('images', 'thumb'),
                    'day' => (int) ($item['d'] ?? 1),
                    'order' => (int) ($item['o'] ?? count($validExercises) + 1),
                    'detail' => $item['t'] ?? '',
                    'notes' => $item['notes'] ?? ($item['n'] ?? ''), // Soportar ambos: 'notes' (nuevo) y 'n' (legacy)
                ];
            }

            if (empty($validExercises)) {
                throw new \Exception('No se pudieron procesar ejercicios válidos.');
            }

            // Reemplazar ejercicios seleccionados
            $this->selectedExercises = $validExercises;

            // Marcar que este plan fue generado por IA
            $this->generatedByAi = true;

            // Incrementar contador de uso de IA
            $tenant->incrementAiUsage();

            // Mensaje de éxito con ejercicios creados
            $successMessage = 'Plan generado con IA exitosamente.';
            if (!empty($createdExercises)) {
                $successMessage .= ' Se crearon ' . count($createdExercises) . ' ejercicios nuevos: ' . implode(', ', $createdExercises) . '.';
            }

            $this->dispatch('saved');
            session()->flash('success', $successMessage);

            // Cerrar modal
            $this->showAiModal = false;

        } catch (\Exception $e) {
            Log::error('[AI Generation] Excepción capturada', [
                'tenant_id' => $tenant->id ?? null,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            // Mensaje de error amigable para timeouts
            $errorMessage = 'Error al generar plan: ';

            if (str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'cURL error 28')) {
                $errorMessage = 'La conexión con el servicio de IA tardó demasiado. Por favor, intenta nuevamente en unos momentos.';
            } else {
                $errorMessage .= $e->getMessage();
            }

            $this->addError('aiPrompt', $errorMessage);
        }
    }

    /**
     * Infiere la categoría de un ejercicio basándose en palabras clave en su nombre
     */
    private function inferCategoryFromName(string $name): ?string
    {
        $name = mb_strtolower($name);

        // Diccionario de palabras clave por categoría
        $categoryKeywords = [
            'Piernas' => ['pierna', 'piernas', 'cuádriceps', 'cuadriceps', 'femoral', 'femorales', 'glúteo', 'gluteo', 'glúteos', 'gluteos', 'pantorrilla', 'pantorrillas', 'gemelo', 'gemelos', 'sentadilla', 'squat', 'zancada', 'estocada', 'peso muerto', 'leg', 'aductor', 'abductor'],
            'Pecho' => ['pecho', 'pectoral', 'pectorales', 'press banca', 'press de banca', 'chest', 'flexión', 'flexion', 'flexiones', 'push up', 'pushup'],
            'Espalda' => ['espalda', 'dorsal', 'dorsales', 'remo', 'pull', 'jalón', 'jalon', 'dominada', 'dominadas', 'trapecio', 'trapecios', 'back', 'lat'],
            'Brazos' => ['brazo', 'brazos', 'bíceps', 'biceps', 'tríceps', 'triceps', 'curl', 'antebrazo', 'antebrazos', 'muñeca', 'muñecas', 'arm'],
            'Hombros' => ['hombro', 'hombros', 'deltoides', 'shoulder', 'press militar', 'elevación', 'elevacion', 'lateral'],
            'Core' => ['core', 'abdomen', 'abdominal', 'abdominales', 'plancha', 'plank', 'oblicuo', 'oblicuos', 'lumbar', 'lumbares'],
            'Cardio' => ['cardio', 'correr', 'trotar', 'saltar', 'burpee', 'burpees', 'jump', 'sprint', 'hiit', 'running', 'bicicleta', 'elíptica', 'eliptica', 'remo cardio'],
            'Estiramiento' => ['estiramiento', 'estirar', 'elongación', 'elongacion', 'flexibilidad', 'movilidad', 'stretch', 'yoga'],
        ];

        // Buscar coincidencias en orden de prioridad
        foreach ($categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($name, $keyword)) {
                    return $category;
                }
            }
        }

        // Si no se encontró coincidencia, retornar null
        return null;
    }

    public function render()
    {
        return view('livewire.tenant.training-plan.form');
    }
}
