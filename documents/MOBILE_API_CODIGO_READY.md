# FitTrack Mobile API - Código Ready-to-Use (Copy-Paste)

Ejemplos listos para implementar inmediatamente. Solo copiar y pegar.

---

## 1. MIDDLEWARE DE API TENANCY

**Archivo:** `app/Http/Middleware/Api/ApiTenancy.php`

```php
<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class ApiTenancy
{
    public function handle(Request $request, Closure $next)
    {
        // Obtener tenant ID del header
        $tenantId = $request->header('X-Tenant-ID');
        
        // Validar que el header esté presente
        if (!$tenantId) {
            return response()->json([
                'error' => 'X-Tenant-ID header is required'
            ], 400);
        }
        
        // Buscar el tenant
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant not found'
            ], 404);
        }
        
        // Inicializar tenancia en el contexto
        tenancy()->initialize($tenant);
        
        return $next($request);
    }
}
```

**Registrar en:** `bootstrap/app.php`

```php
// En el método 'middleware' de la aplicación:
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'api.tenancy' => \App\Http\Middleware\Api\ApiTenancy::class,
    ]);
})
```

---

## 2. COMPLETAR LOGIN RESPONSE

**Archivo:** `app/Http/Controllers/Central/AuthController.php` (MODIFICAR LÍNEAS 75-86)

```php
// REEMPLAZAR ESTO:
/*
return response()->json([
    'tenant'        => $tenant->id,
    // ...resto incompleto
]);
*/

// POR ESTO:
tenancy()->initialize($tenant);
$student = \App\Models\Tenant\Student::where('email', $user->email)->first();

if (!$student) {
    return response()->json([
        'error' => 'Student record not found',
    ], 404);
}

if (!$student->is_user_enabled) {
    return response()->json([
        'error' => 'Student access is not enabled. Please contact your trainer.',
    ], 403);
}

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
        'name' => $user->name ?? $user->email,
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

---

## 3. ENDPOINT LOGOUT

**Agregar a:** `app/Http/Controllers/Central/AuthController.php`

```php
public function logout(Request $request)
{
    try {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al cerrar sesión',
        ], 500);
    }
}
```

**Agregar ruta en:** `routes/api.php`

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [\App\Http\Controllers\Central\AuthController::class, 'logout']);
});
```

---

## 4. STUDENT API CONTROLLER

**Archivo:** `app/Http/Controllers/Api/StudentApiController.php`

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
            'last_login_at' => $student->last_login_at?->toIso8601String(),
            'personal' => [
                'birth_date' => $student->personal_data['birth_date'] ?? null,
                'gender' => $student->personal_data['gender'] ?? null,
                'height_cm' => $student->personal_data['height_cm'] ?? null,
                'weight_kg' => $student->personal_data['weight_kg'] ?? null,
                'imc' => $student->imc,
            ],
            'health' => $student->health_data ?? [],
            'training' => $student->training_data ?? [],
            'communication' => $student->communication_data ?? [],
            'emergency_contact' => $student->extra_data['emergency_contact'] ?? null,
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
            'phone' => 'nullable|string|max:20',
            'personal.birth_date' => 'nullable|date_format:Y-m-d',
            'personal.gender' => 'nullable|in:M,F,O',
            'personal.height_cm' => 'nullable|numeric|min:100|max:250',
            'personal.weight_kg' => 'nullable|numeric|min:30|max:300',
            'health.injuries' => 'nullable|string|max:500',
            'training.experience' => 'nullable|in:principiante,intermedio,avanzado',
            'training.days_per_week' => 'nullable|integer|min:1|max:7',
            'communication.language' => 'nullable|in:es,en,pt',
            'communication.notifications.new_plan' => 'nullable|boolean',
            'communication.notifications.session_reminder' => 'nullable|boolean',
            'communication.notifications.payment_reminder' => 'nullable|boolean',
        ]);
        
        // Actualizar teléfono
        if (isset($validated['phone'])) {
            $student->phone = $validated['phone'];
        }
        
        // Actualizar datos personales
        if (isset($validated['personal'])) {
            $personal = $student->personal_data ?? [];
            $personal = array_merge($personal, array_filter($validated['personal']));
            $student->personal_data = $personal;
        }
        
        // Actualizar datos de salud
        if (isset($validated['health'])) {
            $health = $student->health_data ?? [];
            $health = array_merge($health, array_filter($validated['health']));
            $student->health_data = $health;
        }
        
        // Actualizar datos de entrenamiento
        if (isset($validated['training'])) {
            $training = $student->training_data ?? [];
            $training = array_merge($training, array_filter($validated['training']));
            $student->training_data = $training;
        }
        
        // Actualizar datos de comunicación
        if (isset($validated['communication'])) {
            $communication = $student->communication_data ?? [];
            $communication = array_merge($communication, array_filter($validated['communication']));
            $student->communication_data = $communication;
        }
        
        $student->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'student' => [
                'id' => $student->id,
                'phone' => $student->phone,
                'personal' => $student->personal_data,
                'health' => $student->health_data,
                'training' => $student->training_data,
                'communication' => $student->communication_data,
            ],
        ]);
    }
}
```

---

## 5. TRAINING PLAN API CONTROLLER

**Archivo:** `app/Http/Controllers/Api/TrainingPlanApiController.php`

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
     * Listar planes del alumno
     */
    public function indexByStudent(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        
        $query = TrainingPlan::where('student_id', $student->id);
        
        // Filtro: solo activos
        if ($request->query('active') == '1') {
            $query->where('is_active', true);
        }
        
        // Búsqueda
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Ordenamiento
        $sort = $request->query('sort', 'assigned_from');
        $direction = in_array($request->query('direction'), ['asc', 'desc']) 
            ? $request->query('direction') 
            : 'desc';
        
        $plans = $query->orderBy($sort, $direction)->paginate(10);
        
        return response()->json(
            $plans->through(fn ($plan) => [
                'id' => $plan->id,
                'uuid' => $plan->uuid,
                'name' => $plan->name,
                'description' => $plan->description,
                'goal' => $plan->goal,
                'duration' => $plan->duration,
                'is_active' => $plan->is_active,
                'assigned_from' => $plan->assigned_from?->format('Y-m-d'),
                'assigned_until' => $plan->assigned_until?->format('Y-m-d'),
                'exercise_count' => $plan->exercises()->count(),
            ])
        );
    }
    
    /**
     * GET /api/plans/{plan_id}
     * Detalle de plan con ejercicios
     */
    public function show(Request $request, $planId)
    {
        $plan = TrainingPlan::with('exercises:id,uuid,name,description,category')->findOrFail($planId);
        
        // Verificar que el plan pertenezca al estudiante logueado
        $user = $request->user();
        $student = Student::where('email', $user->email)->firstOrFail();
        
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
            'assigned_from' => $plan->assigned_from?->format('Y-m-d'),
            'assigned_until' => $plan->assigned_until?->format('Y-m-d'),
            'exercises' => $plan->exercises->map(function ($exercise) {
                return [
                    'id' => $exercise->id,
                    'uuid' => $exercise->uuid,
                    'name' => $exercise->name,
                    'description' => $exercise->description,
                    'category' => $exercise->category,
                    'day' => $exercise->pivot->day,
                    'detail' => $exercise->pivot->detail,
                    'notes' => $exercise->pivot->notes,
                ];
            })->values(),
        ]);
    }
}
```

---

## 6. RUTAS API

**Agregar a:** `routes/api.php`

```php
<?php

use Illuminate\Support\Facades\Route;

// Autenticación (NO requiere tenancy)
Route::middleware('universal')->group(function () {
    Route::post('/auth/login', [\App\Http\Controllers\Central\AuthController::class, 'login']);
    Route::post('/auth/list-tenants', [\App\Http\Controllers\Central\AuthController::class, 'listTenants']);
});

// Rutas protegidas (requieren autenticación + tenancy)
Route::middleware(['auth:sanctum', 'api.tenancy'])->group(function () {
    // Auth
    Route::post('/auth/logout', [\App\Http\Controllers\Central\AuthController::class, 'logout']);
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\Api\StudentApiController::class, 'profile']);
    Route::patch('/profile', [\App\Http\Controllers\Api\StudentApiController::class, 'updateProfile']);
    
    // Plans
    Route::get('/students/{student_id}/plans', [\App\Http\Controllers\Api\TrainingPlanApiController::class, 'indexByStudent']);
    Route::get('/plans/{plan_id}', [\App\Http\Controllers\Api\TrainingPlanApiController::class, 'show']);
});
```

---

## 7. CLIENT AXIOS (EXPO)

**Archivo:** `src/api/client.js`

```javascript
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = __DEV__ 
  ? 'http://localhost:8000/api'
  : 'https://api.fittrack.com.ar/api';

const client = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
});

// Request interceptor
client.interceptors.request.use(async (config) => {
  try {
    const token = await AsyncStorage.getItem('fittrack_token');
    const tenantId = await AsyncStorage.getItem('fittrack_tenant_id');

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    if (tenantId) {
      config.headers['X-Tenant-ID'] = tenantId;
    }
  } catch (error) {
    console.warn('Error reading AsyncStorage in request interceptor:', error);
  }

  return config;
});

// Response interceptor
client.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Token inválido, limpiar y redirigir a login
      await AsyncStorage.removeItem('fittrack_token');
      await AsyncStorage.removeItem('fittrack_tenant_id');
      // Aquí puedes disparar un evento para redirigir a login
    }
    return Promise.reject(error);
  }
);

export default client;
```

---

## 8. AUTH API SERVICE (EXPO)

**Archivo:** `src/api/auth.js`

```javascript
import client from './client';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const loginStudent = async (email, password) => {
  try {
    const response = await client.post('/auth/login', {
      email,
      password,
    });

    // Guardar datos localmente
    await AsyncStorage.multiSet([
      ['fittrack_token', response.data.token],
      ['fittrack_tenant_id', response.data.tenant.id],
      ['fittrack_user_email', response.data.user.email],
      ['fittrack_student_data', JSON.stringify(response.data.student)],
    ]);

    return response.data;
  } catch (error) {
    const errorMessage = error.response?.data?.error || 'Login failed';
    throw new Error(errorMessage);
  }
};

export const logoutStudent = async () => {
  try {
    await client.post('/auth/logout');
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    // Limpiar almacenamiento local sin importar si el servidor respondió
    await AsyncStorage.multiRemove([
      'fittrack_token',
      'fittrack_tenant_id',
      'fittrack_user_email',
      'fittrack_student_data',
    ]);
  }
};

export const listTenants = async (email) => {
  try {
    const response = await client.post('/auth/list-tenants', { email });
    return response.data;
  } catch (error) {
    const errorMessage = error.response?.data?.error || 'Failed to list tenants';
    throw new Error(errorMessage);
  }
};
```

---

## 9. AUTH CONTEXT (EXPO)

**Archivo:** `src/context/AuthContext.js`

```javascript
import React, { createContext, useReducer, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as authAPI from '../api/auth';

export const AuthContext = createContext();

const initialState = {
  isLoading: true,
  isSignout: false,
  user: null,
  tenant: null,
  token: null,
};

const authReducer = (state, action) => {
  switch (action.type) {
    case 'RESTORE_TOKEN':
      return {
        ...state,
        isLoading: false,
        isSignout: false,
        token: action.payload.token,
        user: action.payload.user,
        tenant: action.payload.tenant,
      };
    case 'SIGN_IN':
      return {
        ...state,
        isSignout: false,
        token: action.payload.token,
        user: action.payload.user,
        tenant: action.payload.tenant,
      };
    case 'SIGN_OUT':
      return {
        ...state,
        isSignout: true,
        user: null,
        token: null,
        tenant: null,
      };
    default:
      return state;
  }
};

export const AuthProvider = ({ children }) => {
  const [state, dispatch] = useReducer(authReducer, initialState);

  // Restaurar sesión al cargar la app
  useEffect(() => {
    const bootstrapAsync = async () => {
      try {
        const token = await AsyncStorage.getItem('fittrack_token');
        const userEmail = await AsyncStorage.getItem('fittrack_user_email');
        const tenantId = await AsyncStorage.getItem('fittrack_tenant_id');
        const studentData = await AsyncStorage.getItem('fittrack_student_data');

        dispatch({
          type: 'RESTORE_TOKEN',
          payload: {
            token,
            user: userEmail ? { email: userEmail } : null,
            tenant: tenantId ? { id: tenantId } : null,
          },
        });
      } catch (e) {
        console.error('Error restoring token:', e);
      }
    };

    bootstrapAsync();
  }, []);

  const authContext = {
    state,
    signIn: async (email, password) => {
      try {
        const response = await authAPI.loginStudent(email, password);
        dispatch({
          type: 'SIGN_IN',
          payload: {
            token: response.token,
            user: response.user,
            tenant: response.tenant,
          },
        });
        return response;
      } catch (error) {
        throw error;
      }
    },
    signOut: async () => {
      await authAPI.logoutStudent();
      dispatch({ type: 'SIGN_OUT' });
    },
  };

  return (
    <AuthContext.Provider value={authContext}>
      {children}
    </AuthContext.Provider>
  );
};
```

---

## 10. LOGIN SCREEN (EXPO) - BÁSICO

**Archivo:** `src/screens/LoginScreen.js`

```javascript
import React, { useState, useContext } from 'react';
import {
  View,
  TextInput,
  TouchableOpacity,
  Text,
  StyleSheet,
  ActivityIndicator,
  Alert,
} from 'react-native';
import { AuthContext } from '../context/AuthContext';

export default function LoginScreen() {
  const { signIn } = useContext(AuthContext);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Error', 'Por favor ingresa email y contraseña');
      return;
    }

    setLoading(true);
    try {
      await signIn(email, password);
      // La navegación se manejará automáticamente en el contexto
    } catch (error) {
      Alert.alert('Error de login', error.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>FitTrack</Text>

      <TextInput
        style={styles.input}
        placeholder="Email"
        value={email}
        onChangeText={setEmail}
        editable={!loading}
        keyboardType="email-address"
      />

      <TextInput
        style={styles.input}
        placeholder="Contraseña"
        value={password}
        onChangeText={setPassword}
        secureTextEntry
        editable={!loading}
      />

      <TouchableOpacity
        style={[styles.button, loading && styles.buttonDisabled]}
        onPress={handleLogin}
        disabled={loading}
      >
        {loading ? (
          <ActivityIndicator color="#fff" />
        ) : (
          <Text style={styles.buttonText}>Ingresar</Text>
        )}
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    justifyContent: 'center',
    backgroundColor: '#fff',
  },
  title: {
    fontSize: 32,
    fontWeight: 'bold',
    marginBottom: 40,
    textAlign: 'center',
    color: '#263d83',
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    padding: 12,
    marginBottom: 16,
    borderRadius: 8,
    fontSize: 16,
  },
  button: {
    backgroundColor: '#263d83',
    padding: 16,
    borderRadius: 8,
    alignItems: 'center',
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});
```

---

## Resumen de cambios

| Archivo | Acción | Complejidad |
|---------|--------|-------------|
| `AuthController.php` | Modificar respuesta login | ⚡ Fácil |
| `api/ApiTenancy.php` | Crear nuevo middleware | ⚡ Fácil |
| `api.php` | Agregar rutas | ⚡ Fácil |
| `StudentApiController.php` | Crear controlador | 🟡 Medio |
| `TrainingPlanApiController.php` | Crear controlador | 🟡 Medio |
| Cliente Expo | Configurar axios + contexto | 🟡 Medio |
| LoginScreen.js | Pantalla de login básica | 🟡 Medio |

---

**Total de código a escribir:** ~30 KB  
**Tiempo de implementación:** 3-4 horas  
**Resultado:** App móvil funcional con login y lectura de planes
