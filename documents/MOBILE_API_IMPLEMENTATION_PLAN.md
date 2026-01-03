# Plan de Implementación - FitTrack Mobile API
## Orden de Tareas y Prioridades

**Duración estimada:** 3-4 semanas (1-2 semanas por fase)  
**Complejidad:** Media-Alta  
**Requiere:** Conocimiento de Laravel + React Native (básico)

---

## Fase 1: Autenticación Completa (SEMANA 1)
*Dependencia crítica para todo lo demás*

### 1.1 Completar respuesta de `POST /api/auth/login` ⚡ URGENTE

**Archivo:** `app/Http/Controllers/Central/AuthController.php`

**Cambio requerido:**
```php
// Línea 75-86, cambiar:
return response()->json([
    'tenant'        => $tenant->id,
    // ...falta resto
]);

// Por:
return response()->json([
    'success' => true,
    'tenant' => [
        'id' => $tenant->id,
        'name' => $tenant->name,
        'domain' => $tenant->domain,
    ],
    'user' => [
        'id' => $user->id,
        'email' => $user->email,
        'name' => $user->name,
    ],
    'student' => [
        'id' => $student->id,
        'uuid' => $student->uuid,
        'first_name' => $student->first_name,
        'last_name' => $student->last_name,
        'email' => $student->email,
        'phone' => $student->phone,
        'status' => $student->status,
        'goal' => $student->goal,
        'is_user_enabled' => $student->is_user_enabled,
    ],
    'token' => $token,
]);
```

**Validaciones adicionales:**
- Verificar que `$student->is_user_enabled === true`
- Si no está habilitado, retornar error 403

**Tiempo estimado:** 30 min

---

### 1.2 Crear Middleware de API Tenancy

**Archivo:** Crear `app/Http/Middleware/Api/ApiTenancy.php`

```php
<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;

class ApiTenancy extends Closure
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = $request->header('X-Tenant-ID');
        
        if (!$tenantId) {
            return response()->json(['error' => 'X-Tenant-ID header required'], 400);
        }
        
        $tenant = \App\Models\Tenant::find($tenantId);
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
        
        tenancy()->initialize($tenant);
        
        return $next($request);
    }
}
```

**Registrar en:** `bootstrap/app.php` o `app/Http/Kernel.php`

**Tiempo estimado:** 20 min

---

### 1.3 Crear endpoint `POST /api/auth/logout`

**Archivo:** Agregar método a `app/Http/Controllers/Central/AuthController.php`

```php
public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();
    
    return response()->json([
        'success' => true,
        'message' => 'Sesión cerrada'
    ]);
}
```

**Ruta:** Agregar a `routes/api.php`
```php
Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');
```

**Tiempo estimado:** 15 min

---

### 1.4 Crear endpoint `POST /api/auth/list-tenants` (OPCIONAL)

**Para:** Soportar usuarios en múltiples tenants

**Archivo:** Agregar método a `AuthController`

```php
public function listTenants(Request $request)
{
    $request->validate(['email' => 'required|email']);
    
    $tenants = [];
    
    foreach (Tenant::all() as $tenant) {
        tenancy()->initialize($tenant);
        
        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            $tenants[] = [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domain' => $tenant->domain,
            ];
        }
    }
    
    if (empty($tenants)) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }
    
    return response()->json([
        'email' => $request->email,
        'tenants' => $tenants,
    ]);
}
```

**Tiempo estimado:** 20 min

---

### 1.5 Pruebas de Fase 1

**Usando Postman:**

1. `POST /api/auth/login` → Debe retornar token + tenant + student
2. `GET /api/user` (con token) → Debe retornar usuario
3. `POST /api/auth/logout` (con token) → Token se invalida
4. `POST /api/auth/list-tenants` → Retorna lista de tenants

**Tiempo estimado:** 30 min

---

## Fase 2: Controlador de Estudiante (SEMANA 1-2)

### 2.1 Crear `StudentApiController`

**Archivo:** Crear `app/Http/Controllers/Api/StudentApiController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Student;

class StudentApiController extends Controller
{
    /**
     * GET /api/profile
     * Obtener perfil del alumno logueado
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $student = Student::where('email', $user->email)->firstOrFail();
        
        return response()->json([
            'id' => $student->id,
            'uuid' => $student->uuid,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'phone' => $student->phone,
            'status' => $student->status,
            'goal' => $student->goal,
            'is_user_enabled' => $student->is_user_enabled,
            'last_login_at' => $student->last_login_at,
            'personal' => $student->personal_data ?? [],
            'health' => $student->health_data ?? [],
            'training' => $student->training_data ?? [],
            'communication' => $student->communication_data ?? [],
            'extra' => $student->extra_data ?? [],
        ]);
    }
    
    /**
     * PATCH /api/profile
     * Actualizar perfil del alumno
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $student = Student::where('email', $user->email)->firstOrFail();
        
        $validated = $request->validate([
            'phone' => 'nullable|string',
            'personal.birth_date' => 'nullable|date',
            'personal.gender' => 'nullable|in:M,F,O',
            'personal.height_cm' => 'nullable|numeric',
            'personal.weight_kg' => 'nullable|numeric',
            'health.injuries' => 'nullable|string',
            'training.days_per_week' => 'nullable|integer',
            'communication.language' => 'nullable|in:es,en,pt',
            'communication.notifications' => 'nullable|array',
        ]);
        
        $student->update([
            'phone' => $validated['phone'] ?? $student->phone,
            'personal_data' => array_merge(
                $student->personal_data ?? [],
                $validated['personal'] ?? []
            ),
            'health_data' => array_merge(
                $student->health_data ?? [],
                $validated['health'] ?? []
            ),
            'training_data' => array_merge(
                $student->training_data ?? [],
                $validated['training'] ?? []
            ),
            'communication_data' => array_merge(
                $student->communication_data ?? [],
                $validated['communication'] ?? []
            ),
        ]);
        
        return response()->json([
            'success' => true,
            'student' => $student,
        ]);
    }
}
```

**Rutas:** Agregar a `routes/api.php`
```php
Route::middleware(['auth:sanctum', ApiTenancy::class])->group(function () {
    Route::get('/profile', [StudentApiController::class, 'profile']);
    Route::patch('/profile', [StudentApiController::class, 'updateProfile']);
});
```

**Tiempo estimado:** 1 hora

---

### 2.2 Crear `TrainingPlanApiController`

**Archivo:** Crear `app/Http/Controllers/Api/TrainingPlanApiController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Student;

class TrainingPlanApiController extends Controller
{
    /**
     * GET /api/students/{student_id}/plans
     */
    public function indexByStudent(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        
        $query = TrainingPlan::where('student_id', $student->id);
        
        // Filtro: solo activos
        if ($request->query('active')) {
            $query->where('is_active', true);
        }
        
        // Ordenamiento
        $sort = $request->query('sort', 'assigned_from');
        $direction = $request->query('direction', 'desc');
        $query->orderBy($sort, $direction);
        
        $plans = $query->paginate(10);
        
        return response()->json($plans);
    }
    
    /**
     * GET /api/plans/{plan_id}
     */
    public function show(Request $request, $planId)
    {
        $plan = TrainingPlan::with('exercises')->findOrFail($planId);
        
        // Verificar que el plan pertenezca al estudiante logueado
        $user = $request->user();
        $student = Student::where('email', $user->email)->first();
        
        if ($plan->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'id' => $plan->id,
            'uuid' => $plan->uuid,
            'name' => $plan->name,
            'description' => $plan->description,
            'goal' => $plan->goal,
            'duration' => $plan->duration,
            'is_active' => $plan->is_active,
            'assigned_from' => $plan->assigned_from,
            'assigned_until' => $plan->assigned_until,
            'exercises' => $plan->exercises->map(function ($exercise) {
                return [
                    'id' => $exercise->id,
                    'uuid' => $exercise->uuid,
                    'name' => $exercise->name,
                    'description' => $exercise->description,
                    'category' => $exercise->category,
                    'equipment' => $exercise->equipment,
                    'detail' => $exercise->pivot->detail,
                    'day' => $exercise->pivot->day,
                    'notes' => $exercise->pivot->notes,
                ];
            }),
        ]);
    }
}
```

**Rutas:** Agregar a `routes/api.php`
```php
Route::middleware(['auth:sanctum', ApiTenancy::class])->group(function () {
    Route::get('/students/{student_id}/plans', [TrainingPlanApiController::class, 'indexByStudent']);
    Route::get('/plans/{plan_id}', [TrainingPlanApiController::class, 'show']);
});
```

**Tiempo estimado:** 1.5 horas

---

## Fase 3: Modelos y Controlador de Workouts (SEMANA 2)

### 3.1 Crear modelo `Workout`

**Archivo:** Crear `app/Models/Tenant/Workout.php`

```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Workout extends Model
{
    protected $fillable = [
        'uuid',
        'student_id',
        'plan_id',
        'date',
        'duration_minutes',
        'completed_at',
        'notes',
    ];
    
    protected $casts = [
        'date' => 'date',
        'completed_at' => 'datetime',
    ];
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    
    public function plan()
    {
        return $this->belongsTo(TrainingPlan::class);
    }
    
    public function exercises()
    {
        return $this->hasMany(WorkoutExercise::class);
    }
    
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }
}
```

**Tiempo estimado:** 20 min

---

### 3.2 Crear modelo `WorkoutExercise`

**Archivo:** Crear `app/Models/Tenant/WorkoutExercise.php`

```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class WorkoutExercise extends Model
{
    protected $table = 'workout_exercises';
    
    protected $fillable = [
        'workout_id',
        'exercise_id',
        'sets_completed',
        'reps_per_set',
        'weight_used_kg',
        'notes',
        'completed_at',
    ];
    
    protected $casts = [
        'reps_per_set' => 'array',
        'completed_at' => 'datetime',
    ];
    
    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }
    
    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
```

**Tiempo estimado:** 15 min

---

### 3.3 Crear migraciones

**Archivo:** Crear migration `database/migrations/tenant/****_create_workouts_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workouts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('training_plans')->onDelete('cascade');
            $table->date('date');
            $table->integer('duration_minutes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'date']);
        });
        
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_id')->constrained('workouts')->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained('exercises')->onDelete('cascade');
            $table->integer('sets_completed')->nullable();
            $table->json('reps_per_set')->nullable(); // [10, 10, 8, 8]
            $table->decimal('weight_used_kg', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('workout_id');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('workout_exercises');
        Schema::dropIfExists('workouts');
    }
};
```

**Ejecutar:**
```bash
php artisan migrate
```

**Tiempo estimado:** 30 min

---

### 3.4 Crear `WorkoutApiController`

**Archivo:** Crear `app/Http/Controllers/Api/WorkoutApiController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Workout;
use App\Models\Tenant\WorkoutExercise;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;

class WorkoutApiController extends Controller
{
    /**
     * POST /api/workouts
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $student = Student::where('email', $user->email)->firstOrFail();
        
        $validated = $request->validate([
            'plan_id' => 'required|exists:training_plans,id',
            'date' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:0',
            'completed_at' => 'nullable|date_format:Y-m-d H:i:s',
            'notes' => 'nullable|string',
            'exercises' => 'required|array',
            'exercises.*.exercise_id' => 'required|exists:exercises,id',
            'exercises.*.sets_completed' => 'nullable|integer',
            'exercises.*.reps_per_set' => 'nullable|array',
            'exercises.*.weight_used_kg' => 'nullable|numeric',
            'exercises.*.notes' => 'nullable|string',
        ]);
        
        // Verificar que el plan pertenece al estudiante
        $plan = TrainingPlan::findOrFail($validated['plan_id']);
        if ($plan->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Crear workout
        $workout = Workout::create([
            'student_id' => $student->id,
            'plan_id' => $validated['plan_id'],
            'date' => $validated['date'],
            'duration_minutes' => $validated['duration_minutes'],
            'completed_at' => $validated['completed_at'] ?? now(),
            'notes' => $validated['notes'],
        ]);
        
        // Crear ejercicios del workout
        foreach ($validated['exercises'] as $exerciseData) {
            WorkoutExercise::create([
                'workout_id' => $workout->id,
                'exercise_id' => $exerciseData['exercise_id'],
                'sets_completed' => $exerciseData['sets_completed'],
                'reps_per_set' => $exerciseData['reps_per_set'],
                'weight_used_kg' => $exerciseData['weight_used_kg'],
                'notes' => $exerciseData['notes'],
                'completed_at' => $validated['completed_at'] ?? now(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'workout' => $workout,
        ], 201);
    }
    
    /**
     * GET /api/workouts
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $student = Student::where('email', $user->email)->firstOrFail();
        
        $query = Workout::where('student_id', $student->id);
        
        // Filtro: fecha desde
        if ($request->query('from')) {
            $query->where('date', '>=', $request->query('from'));
        }
        
        // Filtro: fecha hasta
        if ($request->query('to')) {
            $query->where('date', '<=', $request->query('to'));
        }
        
        // Filtro: plan_id
        if ($request->query('plan_id')) {
            $query->where('plan_id', $request->query('plan_id'));
        }
        
        $workouts = $query
            ->with('plan', 'exercises')
            ->orderBy('date', 'desc')
            ->paginate(10);
        
        return response()->json($workouts);
    }
    
    /**
     * GET /api/workouts/{workout_id}
     */
    public function show(Request $request, $workoutId)
    {
        $user = $request->user();
        $student = Student::where('email', $user->email)->firstOrFail();
        
        $workout = Workout::with('exercises', 'plan')
            ->where('id', $workoutId)
            ->where('student_id', $student->id)
            ->firstOrFail();
        
        return response()->json($workout);
    }
}
```

**Rutas:** Agregar a `routes/api.php`
```php
Route::middleware(['auth:sanctum', ApiTenancy::class])->group(function () {
    Route::post('/workouts', [WorkoutApiController::class, 'store']);
    Route::get('/workouts', [WorkoutApiController::class, 'index']);
    Route::get('/workouts/{workout_id}', [WorkoutApiController::class, 'show']);
});
```

**Tiempo estimado:** 2 horas

---

## Fase 4: Documentación API (SEMANA 2-3)

### 4.1 Instalar L5 Swagger

```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

### 4.2 Documentar endpoints con anotaciones

Agregar a los controladores:

```php
/**
 * @OA\Post(
 *     path="/api/auth/login",
 *     tags={"Auth"},
 *     summary="Login del alumno",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", example="juan@example.com"),
 *             @OA\Property(property="password", type="string", example="123456")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login exitoso",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean"),
 *             @OA\Property(property="tenant", type="object"),
 *             @OA\Property(property="token", type="string")
 *         )
 *     )
 * )
 */
public function login(Request $request) { ... }
```

**Generar docs:**
```bash
php artisan l5-swagger:generate
```

**Acceder a:** `https://fittrack.com.ar/api/documentation`

**Tiempo estimado:** 3 horas

---

## Fase 5: Configuración en Expo (SEMANA 3-4)

### 5.1 Setup inicial

```bash
npx create-expo-app fittrack-mobile
cd fittrack-mobile
npm install axios @react-navigation/native @react-navigation/bottom-tabs @react-navigation/stack react-native-screens react-native-safe-area-context react-native-async-storage
```

### 5.2 Estructura de carpetas

```
fittrack-mobile/
├── src/
│   ├── api/
│   │   ├── client.js
│   │   ├── auth.js
│   │   ├── profile.js
│   │   ├── plans.js
│   │   └── workouts.js
│   ├── context/
│   │   └── AuthContext.js
│   ├── screens/
│   │   ├── LoginScreen.js
│   │   ├── HomeScreen.js
│   │   ├── PlansScreen.js
│   │   ├── WorkoutScreen.js
│   │   ├── ProfileScreen.js
│   │   └── PlanDetailScreen.js
│   ├── navigation/
│   │   ├── RootNavigator.js
│   │   ├── AuthStack.js
│   │   └── AppStack.js
│   ├── components/
│   │   ├── Button.js
│   │   ├── Input.js
│   │   ├── Card.js
│   │   └── Loading.js
│   └── constants/
│       └── config.js
└── App.js
```

### 5.3 Implementar AuthContext

Ver MOBILE_API_EXPO_SPEC.md sección "Configuración en Expo"

### 5.4 Screens principales

- LoginScreen: Form de login
- HomeScreen: Dashboard con últimas sesiones
- PlansScreen: Listado de planes asignados
- PlanDetailScreen: Detalle de plan con ejercicios
- WorkoutScreen: Registrar sesión de entrenamiento
- ProfileScreen: Ver/editar perfil

**Tiempo estimado:** 10-15 horas

---

## Testing y QA (SEMANA 4)

### Pruebas Backend
- [ ] Unitarias (Models, Controllers)
- [ ] Funcionales (API endpoints)
- [ ] Seguridad (auth, tenancy)

### Pruebas Frontend
- [ ] Login flow
- [ ] Navegación
- [ ] Consumo de APIs
- [ ] Persistencia local

### Documentación
- [ ] README.md
- [ ] API docs (Swagger)
- [ ] Guía de instalación
- [ ] Troubleshooting

---

## Resumen de archivos a crear/modificar

### Backend
```
Crear:
✓ app/Http/Controllers/Api/StudentApiController.php
✓ app/Http/Controllers/Api/TrainingPlanApiController.php
✓ app/Http/Controllers/Api/WorkoutApiController.php
✓ app/Http/Middleware/Api/ApiTenancy.php
✓ app/Models/Tenant/Workout.php
✓ app/Models/Tenant/WorkoutExercise.php
✓ database/migrations/tenant/****_create_workouts_table.php

Modificar:
✓ app/Http/Controllers/Central/AuthController.php (login(), logout())
✓ routes/api.php (agregar rutas)
✓ bootstrap/app.php (registrar middleware)
```

### Frontend (Expo)
```
Crear:
✓ src/api/client.js
✓ src/api/auth.js
✓ src/api/profile.js
✓ src/api/plans.js
✓ src/api/workouts.js
✓ src/context/AuthContext.js
✓ src/screens/LoginScreen.js
✓ src/screens/HomeScreen.js
✓ src/screens/PlansScreen.js
✓ src/screens/ProfileScreen.js
✓ src/navigation/RootNavigator.js
✓ src/components/Button.js
✓ src/components/Input.js
✓ src/constants/config.js
✓ App.js
```

---

## Estimación de Tiempo Total

| Fase | Tareas | Horas | Semana |
|------|--------|-------|--------|
| 1 | Auth (login, logout, list-tenants, middleware) | 2-3 | 1 |
| 2 | Student & Plan APIs | 3-4 | 1-2 |
| 3 | Workouts (models, migrations, controller) | 3-4 | 2 |
| 4 | Documentación API | 3-4 | 2-3 |
| 5 | Setup Expo + Screens | 10-15 | 3-4 |
| QA | Testing y ajustes | 5-8 | 4 |
| **TOTAL** | | **27-38 horas** | **3-4 semanas** |

---

## Consideraciones de Seguridad

1. **HTTPS obligatorio:** Cambiar API_BASE_URL a https
2. **Token expiration:** Considerar agregar expiración a Sanctum tokens
3. **Rate limiting:** Agregar middleware de rate limiting a rutas de auth
4. **Validación de input:** Todas las rutas deben validar
5. **CORS:** Restringir orígenes permitidos en producción

---

## Próximos pasos

1. ✅ Leer y entender MOBILE_API_EXPO_SPEC.md
2. ✅ Implementar Fase 1 (Auth)
3. ✅ Implementar Fase 2 (Estudiante)
4. ✅ Implementar Fase 3 (Workouts)
5. ✅ Documentar API
6. ✅ Setup Expo project
7. ✅ Implementar Frontend
8. ✅ Testing integral

---

**Generado:** Enero 2026  
**Autor:** Asistente técnico
