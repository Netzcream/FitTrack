# FitTrack Mobile API - Diagrama de Flujo

## Flujo General: Login → Home → Ver Planes → Registrar Sesión

```
┌─────────────────────────────────────────────────────────────────────┐
│                         EXPO MOBILE APP                              │
│                                                                       │
│  1. LoginScreen (email + password)                                   │
│     │                                                                │
│     └──> POST /api/auth/login                                       │
│          (sin autenticación)                                         │
│          │                                                           │
│          ├─ response: token + tenant_id + student_data              │
│          └─ guardar en AsyncStorage                                 │
│                                                                       │
│  2. HomeScreen (dashboard)                                          │
│     │                                                                │
│     ├─ mostrar: nombre alumno, último plan, próxima sesión          │
│     └─ GET /api/profile (con Authorization header)                  │
│        X-Tenant-ID header                                           │
│        response: personal_data, health_data, etc                    │
│                                                                       │
│  3. PlansScreen (listado de planes)                                 │
│     │                                                                │
│     └─ GET /api/students/{id}/plans                                 │
│        (con filtros: active=1, sort=assigned_from)                  │
│        response: [{ id, name, goal, assigned_from, ... }, ...]      │
│                                                                       │
│  4. PlanDetailScreen                                                │
│     │                                                                │
│     └─ GET /api/plans/{plan_id}                                     │
│        response: {                                                  │
│          name, exercises: [                                        │
│            { name, day, sets, reps, weight, notes, ... }           │
│          ]                                                           │
│        }                                                            │
│                                                                       │
│  5. WorkoutScreen (registrar sesión)                                │
│     │                                                                │
│     └─ POST /api/workouts                                           │
│        {                                                            │
│          plan_id, date, duration_minutes,                           │
│          exercises: [                                              │
│            { exercise_id, sets_completed, reps_per_set, weight_kg}  │
│          ]                                                           │
│        }                                                            │
│        response: { success: true, workout: { id, uuid, ... } }      │
│                                                                       │
│  6. ProfileScreen (editar perfil)                                   │
│     │                                                                │
│     ├─ GET /api/profile (cargar datos actuales)                    │
│     └─ PATCH /api/profile                                           │
│        {                                                            │
│          phone, personal: { weight_kg, ... },                       │
│          communication: { language, notifications }                 │
│        }                                                            │
│        response: { success: true, student: { ... } }                │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
          │                          │
          │                          │
          │      HTTP/HTTPS          │
          │    (Torres + CORS)        │
          │                          │
          ▼                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    LARAVEL BACKEND (api.fittrack.com.ar)             │
│                                                                       │
│  Route: POST /api/auth/login                                        │
│  Controller: Central\AuthController@login                           │
│  └─ 1. Detecta tenant por email (itera todos)                       │
│  └─ 2. Valida contraseña                                            │
│  └─ 3. Crea token Sanctum                                           │
│  └─ 4. Retorna: tenant, user, student, token                        │
│                                                                       │
│  Routes protegidas (require: auth:sanctum + api.tenancy):           │
│                                                                       │
│  GET  /api/profile                                                  │
│  └─ StudentApiController@profile                                    │
│                                                                       │
│  PATCH /api/profile                                                 │
│  └─ StudentApiController@updateProfile                              │
│                                                                       │
│  GET  /api/students/{id}/plans                                      │
│  └─ TrainingPlanApiController@indexByStudent                        │
│                                                                       │
│  GET  /api/plans/{id}                                               │
│  └─ TrainingPlanApiController@show                                  │
│                                                                       │
│  POST /api/workouts                                                 │
│  └─ WorkoutApiController@store                                      │
│                                                                       │
│  Middleware: api.tenancy                                            │
│  └─ Lee X-Tenant-ID del header                                      │
│  └─ Inicializa tenancy para el tenant correcto                      │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
          │                          │
          │                          │
          │      Tenancy Init        │
          │      (multi-DB)          │
          │                          │
          ▼                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│              MULTI-TENANT DATABASE (fittrack_{tenant_id})            │
│                                                                       │
│  tables:                                                            │
│  ├─ users                                                           │
│  ├─ students                                                        │
│  ├─ training_plans                                                  │
│  ├─ exercises                                                       │
│  ├─ plan_exercise (pivote)                                          │
│  ├─ workouts (registros de sesiones)                                │
│  └─ workout_exercises (ejercicios realizados)                       │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Flujo de Autenticación Detallado

```
PASO 1: Alumno escribe credenciales
┌──────────────────────────────┐
│ LoginScreen                  │
│ ┌────────────────────────────┐│
│ │ email: juan@example.com    ││
│ │ password: ••••••           ││
│ │ [Ingresar]                 ││
│ └────────────────────────────┘│
└──────────────┬───────────────┘
               │
               ▼
PASO 2: App envía POST /api/auth/login
┌─────────────────────────────────────────────────┐
│ HTTP Request                                    │
│ POST https://api.fittrack.com.ar/api/auth/login │
│                                                  │
│ Headers:                                        │
│   Content-Type: application/json                │
│   Accept: application/json                      │
│                                                  │
│ Body:                                           │
│ {                                               │
│   "email": "juan@example.com",                  │
│   "password": "123456"                          │
│ }                                               │
└──────────────┬──────────────────────────────────┘
               │
               ▼
PASO 3: Backend busca el tenant (iteración)
┌────────────────────────────────────────────────┐
│ Central\AuthController@login                   │
│                                                │
│ foreach (Tenant::all() as $t) {               │
│   tenancy()->initialize($t)                    │
│   $user = User::where('email', $email)->first()│
│   if ($user) break                             │
│ }                                              │
│                                                │
│ ✓ Encuentra: tenant_id = "uuid-123"           │
│              user_id = 1                       │
└──────────────┬────────────────────────────────┘
               │
               ▼
PASO 4: Valida contraseña dentro del tenant
┌───────────────────────────────────────────────┐
│ Hash::check($password, $user->password)       │
│ ✓ Contraseña válida                           │
│                                               │
│ Obtiene student data:                         │
│ $student = Student::where('email', $email)    │
│             ->first()                         │
│                                               │
│ ✓ Verifica is_user_enabled = true             │
└──────────────┬────────────────────────────────┘
               │
               ▼
PASO 5: Crea token Sanctum
┌─────────────────────────────────────────────┐
│ $token = $user->createToken('pwa')           │
│          ->plainTextToken                    │
│                                              │
│ ✓ Token: "1|abcdef1234567890xyz..."         │
└──────────────┬──────────────────────────────┘
               │
               ▼
PASO 6: Responde con datos completos
┌─────────────────────────────────────────────────────┐
│ HTTP 200                                            │
│                                                     │
│ {                                                   │
│   "success": true,                                  │
│   "tenant": {                                       │
│     "id": "550e8400-e29b-41d4-a716",               │
│     "name": "Gym Juan",                             │
│     "domain": "juangym.fittrack.com.ar"            │
│   },                                                │
│   "user": {                                         │
│     "id": 1,                                        │
│     "email": "juan@example.com",                    │
│     "name": "Juan Pérez"                            │
│   },                                                │
│   "student": {                                      │
│     "id": 1,                                        │
│     "uuid": "student-uuid-456",                     │
│     "first_name": "Juan",                           │
│     "last_name": "Pérez",                           │
│     "email": "juan@example.com",                    │
│     "phone": "+54 9 11 1234 5678",                  │
│     "status": "active",                             │
│     "goal": "hipertrofia",                          │
│     "is_user_enabled": true                         │
│   },                                                │
│   "token": "1|abcdef1234567890xyz..."              │
│ }                                                   │
└──────────────┬──────────────────────────────────────┘
               │
               ▼
PASO 7: App almacena datos locales
┌────────────────────────────────────────────┐
│ AsyncStorage.multiSet([                    │
│   ['fittrack_token', '1|abc...'],          │
│   ['fittrack_tenant_id', '550e8400...'],   │
│   ['fittrack_user_email', 'juan@...'],     │
│   ['fittrack_student_data', JSON.stringify] │
│ ])                                          │
│                                             │
│ ✓ Datos guardados en dispositivo            │
└──────────────┬─────────────────────────────┘
               │
               ▼
┌────────────────────────────────┐
│ HomeScreen                     │
│ ¡Bienvenido Juan Pérez!        │
│                                │
│ Última sesión: Ayer            │
│ Próximo entrenamiento: Hoy     │
│ Plan activo: Hipertrofia A     │
└────────────────────────────────┘
```

---

## Flujo de Lectura de Planes

```
HomeScreen
  │
  └─> useEffect(() => fetchPlans())
      │
      ├─ GET /api/students/{id}/plans
      │  Headers:
      │    Authorization: Bearer 1|abc...
      │    X-Tenant-ID: 550e8400...
      │
      └─> [
            { id: 1, name: "Hipertrofia A", exercises: 12, ... },
            { id: 2, name: "Fuerza B", exercises: 10, ... },
            { id: 3, name: "Cardio C", exercises: 8, ... }
          ]

PlansScreen
  │
  ├─ renderItem() para cada plan
  │
  └─> onPress(plan) → PlanDetailScreen

PlanDetailScreen
  │
  ├─ useEffect(() => fetchPlanDetail(plan_id))
  │
  ├─ GET /api/plans/{plan_id}
  │  Headers:
  │    Authorization: Bearer 1|abc...
  │    X-Tenant-ID: 550e8400...
  │
  └─> {
        id: 1,
        name: "Hipertrofia A",
        exercises: [
          {
            id: 1,
            name: "Press de Banca",
            day: "Lunes",
            sets: 4,
            reps: "8-10",
            weight: 80,
            notes: "Controlado en la bajada"
          },
          ...
        ]
      }

  │
  ├─ renderExercise() para cada ejercicio
  │
  └─> ListExercises
      │
      └─ [Press de Banca (Lunes, 4x8-10)]
         [Press Inclinado (Miércoles, 4x8-10)]
         [Aperturas (Viernes, 3x12)]
         ...
```

---

## Flujo de Registro de Sesión

```
WorkoutScreen
  │
  ├─ Plan seleccionado: "Hipertrofia A"
  │
  ├─ Renderizar ejercicios del plan
  │
  └─ Para cada ejercicio:
     ┌─────────────────────────────┐
     │ Exercise: Press de Banca    │
     │ Sets completados: 4         │
     │ Reps por set: [10, 10, 8, 8]│
     │ Peso usado: 80 kg           │
     │ Notas: Muy bueno            │
     └──────────────┬──────────────┘
                    │
                    └─ [Siguiente] → siguiente ejercicio
                    
     ... repite para todos ...
     
     └─ [Guardar Sesión]
        │
        ├─ POST /api/workouts
        │  Headers:
        │    Authorization: Bearer 1|abc...
        │    X-Tenant-ID: 550e8400...
        │
        │  Body: {
        │    plan_id: 1,
        │    date: "2026-01-02",
        │    duration_minutes: 45,
        │    completed_at: "2026-01-02T19:15:00Z",
        │    exercises: [
        │      {
        │        exercise_id: 1,
        │        sets_completed: 4,
        │        reps_per_set: [10, 10, 8, 8],
        │        weight_used_kg: 80,
        │        notes: "Muy bueno"
        │      },
        │      {
        │        exercise_id: 2,
        │        sets_completed: 4,
        │        reps_per_set: [10, 10, 9, 8],
        │        weight_used_kg: 75,
        │        notes: "Buen control"
        │      },
        │      ...
        │    ]
        │  }
        │
        └─> HTTP 201
            {
              success: true,
              workout: {
                id: 42,
                uuid: "workout-uuid",
                plan_id: 1,
                date: "2026-01-02",
                duration_minutes: 45,
                completed_exercises: 5,
                completed_at: "2026-01-02T19:15:00Z"
              }
            }

  │
  └─ ✓ Sesión guardada
     "¡Excelente sesión!"
     Volver a HomeScreen
```

---

## Estructura de Headers Explicada

### Login (SIN autenticación)
```
POST /api/auth/login

Headers:
  Content-Type: application/json
  Accept: application/json

Body: { email, password }

Response: { token, tenant_id, student, ... }
```

### Peticiones Protegidas (CON autenticación)
```
GET /api/profile

Headers:
  Authorization: Bearer 1|abcdef1234567890...
  X-Tenant-ID: 550e8400-e29b-41d4-a716-446655440000
  Content-Type: application/json
  Accept: application/json

Middleware:
  1. auth:sanctum → Valida que el token sea válido
  2. api.tenancy → Lee X-Tenant-ID, inicializa tenancy
  
Response: { student data }
```

---

## Ciclo de vida del Request

```
1. CLIENT (Expo)
   ├─ Prepara request
   ├─ Agrega Authorization header (interceptor)
   ├─ Agrega X-Tenant-ID header (interceptor)
   └─ Envía HTTP request

2. CORS MIDDLEWARE (Laravel)
   ├─ Valida origen (*)
   ├─ Valida método (POST, GET, PATCH, etc)
   └─ Permite el request

3. AUTH MIDDLEWARE (auth:sanctum)
   ├─ Valida Authorization header
   ├─ Obtiene token
   ├─ Busca usuario asociado al token
   └─ Si falla → 401 Unauthorized

4. TENANCY MIDDLEWARE (api.tenancy)
   ├─ Lee X-Tenant-ID del header
   ├─ Busca el tenant
   ├─ Llama tenancy()->initialize($tenant)
   └─ Si falla → 404/400

5. ROUTE HANDLER (Controller)
   ├─ Recibe request en contexto de tenancia
   ├─ Accede a DB del tenant específico
   ├─ Procesa lógica
   └─ Retorna respuesta

6. RESPONSE (JSON)
   ├─ Serializa datos
   ├─ Aplica CORS headers
   └─ Retorna al cliente

7. CLIENT (Expo)
   ├─ Recibe response
   ├─ Valida status (200, 401, etc)
   ├─ Si error → Maneja (ej: redirige a login)
   └─ Si éxito → Actualiza estado/UI
```

---

## Estados Posibles de Response

### ✅ Éxito (2xx)
```javascript
// 200 OK
{
  success: true,
  student: { ... }
}

// 201 Created
{
  success: true,
  workout: { ... }
}
```

### ❌ Error (4xx)
```javascript
// 400 Bad Request (falta header)
{
  error: "X-Tenant-ID header is required"
}

// 401 Unauthorized (token inválido)
{
  error: "Unauthenticated"
  // → App debe redirigir a login
}

// 403 Forbidden (acceso denegado)
{
  error: "Student access is not enabled"
}

// 404 Not Found
{
  error: "Tenant not found"
}
```

### 🔴 Error Servidor (5xx)
```javascript
// 500 Server Error
{
  error: "Internal Server Error",
  message: "..."
}
```

---

## Error Handling en Expo

```javascript
// client.js (axios interceptor)
client.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Token expirado o inválido
      // Limpiar AsyncStorage
      // Redirigir a LoginScreen
      await logout();
    }
    if (error.response?.status === 403) {
      // Acceso denegado
      // Mostrar mensaje al usuario
    }
    if (error.response?.status >= 500) {
      // Error del servidor
      // Reintentar después
    }
    
    return Promise.reject(error);
  }
);
```

---

**Nota:** Este diagrama cubre el 90% de los flujos. Los casos de error y edge cases se manejan con validaciones específicas en cada endpoint.
