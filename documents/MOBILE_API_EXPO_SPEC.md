# FitTrack Mobile API - Especificación para Expo Go
## Integración de App Mobile con Backend Laravel

**Fecha:** Enero 2026  
**Estado:** En Desarrollo (API incompleta)  
**Versión:** 1.0  

---

## Índice
1. [Estado Actual](#estado-actual)
2. [Arquitectura General](#arquitectura-general)
3. [Flujo de Autenticación](#flujo-de-autenticación)
4. [API Endpoints Planeados](#api-endpoints-planeados)
5. [Modelos de Datos Disponibles](#modelos-de-datos-disponibles)
6. [Tareas Pendientes](#tareas-pendientes)
7. [Configuración en Expo](#configuración-en-expo)

---

## Estado Actual

### ✅ Lo que YA EXISTE

1. **Sistema de Autenticación Central (Parcialmente)**
   - Archivo: `app/Http/Controllers/Central/AuthController.php`
   - Ruta: `POST /api/auth/login`
   - **Función:** Login que detecta automáticamente a qué tenant pertenece el usuario
   - **Implementación:** Itera sobre todos los tenants, busca el usuario y valida la contraseña
   - **Respuesta:** Retorna `tenant_id` + token Sanctum
   - **Estado:** ✅ IMPLEMENTADO pero **INCOMPLETO** (ver abajo)

2. **Modelo Student (Bien estructurado)**
   - Archivo: `app/Models/Tenant/Student.php`
   - Campos disponibles: email, first_name, last_name, phone, goal, status, is_user_enabled
   - Datos JSON: personal_data (birth_date, gender, height_cm, weight_kg), health_data, training_data, communication_data
   - Relaciones: `hasMany TrainingPlan`
   - **Estado:** ✅ READY para consumo por API

3. **Modelo TrainingPlan (Bien estructurado)**
   - Archivo: `app/Models/Tenant/TrainingPlan.php`
   - Campos: name, description, goal, duration, is_active, assigned_from, assigned_until
   - Relaciones: `hasMany Exercise` (relación pivote con `plan_exercise`)
   - **Estado:** ✅ READY para consumo por API

4. **Middleware de Tenancia**
   - `InitializeTenancyByDomain` para web routes
   - `TenantAuthenticate` para rutas protegidas
   - **Nota:** NO hay middleware de tenancia para API routes aún

5. **Sanctum Configurado**
   - Archivo: `config/sanctum.php`
   - Guard: `['api']`
   - Tokens sin expiración
   - **Estado:** ✅ READY

6. **CORS Habilitado**
   - Archivo: `config/cors.php`
   - Acepta `['*']` en métodos y orígenes
   - **Estado:** ✅ READY

### ❌ Lo que FALTA

1. **API de Student Data (CRUD)**
   - No existe controlador para GET `/api/students/{id}`
   - No existe endpoint para obtener datos del alumno logueado
   - No existen scopes para filtros por tenant

2. **API de Training Plans**
   - No existe GET `/api/students/{id}/plans`
   - No existe GET `/api/plans/{id}/exercises`
   - No existe POST para loguear sesión de entrenamiento

3. **Selección de Tenant en Login** (ALTERNATIVA)
   - El login actual busca el tenant automáticamente por email
   - Si el usuario está en múltiples tenants, se necesita una forma de seleccionar
   - Opción A: Retornar lista de tenants disponibles
   - Opción B: Parámetro `tenant_id` en el login

4. **Middleware de API Tenancy**
   - Las rutas `/api/*` no tienen middleware que inicialice tenancy por header
   - Se puede usar `Authorization: Bearer TOKEN` + extraer `tenant_id` del token

5. **Validación de Student Access**
   - Middleware `EnsureStudentAccessEnabled` existe pero solo para web
   - Se necesita versión para API

6. **Documentación de API (OpenAPI/Swagger)**
   - No existe documentación interactiva
   - Se recomienda L5 Swagger

---

## Arquitectura General

### Flujo de Integración Expo ↔ Laravel

```
┌─────────────────────┐
│   Expo Go App       │
│   (React Native)    │
└──────────┬──────────┘
           │
           │ HTTPS
           ▼
┌─────────────────────────────────────────┐
│  Laravel API (api.fittrack.com.ar)     │
│                                         │
│  POST /api/auth/login                  │
│    ├─ Detecta tenant por email         │
│    └─ Retorna: tenant_id + token       │
│                                         │
│  GET /api/students/{id}                │
│    ├─ Middleware: API Tenancy          │
│    └─ Retorna: Student data            │
│                                         │
│  GET /api/students/{id}/plans          │
│  POST /api/workouts                    │
│  GET /api/profile                      │
│                                         │
└────────────┬────────────────────────────┘
             │
             │ Tenancy Middleware
             ▼
    ┌────────────────────┐
    │  DB Tenant Actual  │
    │  fittrack_{uuid}   │
    └────────────────────┘
```

### Almacenamiento en Expo

```javascript
// AsyncStorage (en el dispositivo)
{
  "fittrack_tenant_id": "uuid-del-tenant",
  "fittrack_token": "plaintext-token-sanctum",
  "fittrack_user_email": "alumno@example.com",
  "fittrack_user_data": {
    "id": 1,
    "email": "...",
    "first_name": "...",
    ...
  }
}
```

---

## Flujo de Autenticación

### Opción 1: Login con Email (RECOMENDADO - Actual)

El sistema actual ya tiene implementado este flujo:

```
1. Alumno escribe: email + password
2. App envía:
   POST /api/auth/login
   {
     "email": "juan@example.com",
     "password": "123456"
   }

3. Backend:
   ├─ Valida que sea email + password
   ├─ Itera todos los tenants
   ├─ Busca el usuario en cada uno
   ├─ Valida Hash(password)
   ├─ Crea token Sanctum en el tenant correcto
   └─ Retorna respuesta

4. Respuesta HTTP 200:
   {
     "success": true,
     "tenant": {
       "id": "uuid-1234",
       "name": "Juan's Gym"
     },
     "user": {
       "id": 1,
       "email": "juan@example.com",
       "name": "Juan Pérez"
     },
     "student": {
       "id": 1,
       "uuid": "student-uuid",
       "first_name": "Juan",
       "last_name": "Pérez",
       "goal": "hipertrofia",
       ...
     },
     "token": "1|abcdef1234567890..."
   }

5. App almacena:
   - token (AsyncStorage)
   - tenant_id (AsyncStorage)
   - user data (AsyncStorage)
```

**Ventaja:** Búsqueda automática por email  
**Desventaja:** Si el usuario está en múltiples tenants, solo obtiene uno  

### Opción 2: Login con Selección de Tenant (ALTERNATIVA)

Para soportar alumnos en múltiples tenants:

```
1. App envía:
   POST /api/auth/list-tenants
   {
     "email": "alumno@example.com"
   }

2. Retorna lista de tenants:
   {
     "email": "alumno@example.com",
     "tenants": [
       {
         "id": "uuid-gym-1",
         "name": "Gym Downtown"
       },
       {
         "id": "uuid-gym-2",
         "name": "Gym North"
       }
     ]
   }

3. Alumno selecciona uno

4. App envía:
   POST /api/auth/login
   {
     "email": "alumno@example.com",
     "password": "123456",
     "tenant_id": "uuid-gym-1"  // ← NUEVO
   }

5. Backend valida en el tenant específico y retorna token
```

---

## API Endpoints Planeados

### Grupo 1: Autenticación

#### `POST /api/auth/login`
**Estado:** ✅ PARCIALMENTE IMPLEMENTADO (falta respuesta completa)

**Request:**
```json
{
  "email": "juan@example.com",
  "password": "123456"
}
```

**Response (200):**
```json
{
  "success": true,
  "tenant": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Gym Juan"
  },
  "user": {
    "id": 1,
    "email": "juan@example.com"
  },
  "student": {
    "id": 1,
    "uuid": "student-uuid",
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "juan@example.com",
    "phone": "+54 9 11 1234 5678",
    "status": "active",
    "goal": "hipertrofia"
  },
  "token": "1|abcdef..."
}
```

**Error (401):**
```json
{
  "error": "Credenciales inválidas"
}
```

---

#### `POST /api/auth/logout`
**Status:** ❌ NO IMPLEMENTADO

**Request:**
```
Headers:
  Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Sesión cerrada"
}
```

---

### Grupo 2: Perfil del Alumno

#### `GET /api/profile`
**Status:** ❌ NO IMPLEMENTADO

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}  // ← Necesario para identificar tenant en API
```

**Response (200):**
```json
{
  "id": 1,
  "uuid": "student-uuid",
  "first_name": "Juan",
  "last_name": "Pérez",
  "email": "juan@example.com",
  "phone": "+54 9 11 1234 5678",
  "status": "active",
  "goal": "hipertrofia",
  "is_user_enabled": true,
  "last_login_at": "2026-01-02T10:30:00Z",
  "personal": {
    "birth_date": "1990-05-15",
    "gender": "M",
    "height_cm": 178,
    "weight_kg": 85.5,
    "imc": 27.80
  },
  "health": {
    "injuries": "Lesión en hombro izquierdo"
  },
  "training": {
    "experience": "Intermedio",
    "days_per_week": 4
  },
  "communication": {
    "language": "es",
    "notifications": {
      "new_plan": true,
      "session_reminder": true
    }
  }
}
```

---

#### `PATCH /api/profile`
**Status:** ❌ NO IMPLEMENTADO

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
Content-Type: application/json
```

**Request:**
```json
{
  "phone": "+54 9 11 9999 9999",
  "personal": {
    "weight_kg": 82.0
  },
  "communication": {
    "notifications": {
      "session_reminder": false
    }
  }
}
```

**Response (200):** El perfil actualizado

---

### Grupo 3: Planes de Entrenamiento

#### `GET /api/students/{student_id}/plans`
**Status:** ❌ NO IMPLEMENTADO

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
```

**Query Parameters:**
- `active=1` → Solo planes activos
- `sort=assigned_from` → Ordenar por fecha

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "plan-uuid",
      "name": "Hipertrofia A",
      "description": "Enfoque en pecho, espalda y brazos",
      "goal": "hipertrofia",
      "duration": "4 semanas",
      "is_active": true,
      "assigned_from": "2026-01-02",
      "assigned_until": "2026-01-30",
      "exercise_count": 12,
      "trainer": {
        "id": 1,
        "name": "Mario"
      }
    }
  ],
  "pagination": {
    "total": 5,
    "per_page": 10,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

#### `GET /api/plans/{plan_id}`
**Status:** ❌ NO IMPLEMENTADO

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
```

**Response (200):**
```json
{
  "id": 1,
  "uuid": "plan-uuid",
  "name": "Hipertrofia A",
  "description": "...",
  "goal": "hipertrofia",
  "duration": "4 semanas",
  "is_active": true,
  "assigned_from": "2026-01-02",
  "assigned_until": "2026-01-30",
  "exercises": [
    {
      "id": 1,
      "uuid": "exercise-uuid",
      "name": "Press de Banca",
      "category": "Upper Body",
      "day": "Monday",
      "sets": 4,
      "reps": "8-10",
      "rest_seconds": 90,
      "notes": "Mantener control en la bajada",
      "detail": {
        "video_url": "https://...",
        "image_url": "https://..."
      }
    }
  ]
}
```

---

#### `GET /api/plans/{plan_id}/download` 
**Status:** ⚠️ PARCIALMENTE IMPLEMENTADO (solo web)

**Ruta existente:** `tenant.student.download-plan`  
**Controlador:** `StudentPlanController@download`

**Modificar para:** Retornar PDF o JSON con plan

---

### Grupo 4: Sesiones de Entrenamiento (Workouts)

#### `POST /api/workouts`
**Status:** ❌ NO IMPLEMENTADO

**Registrar que el alumno completó una sesión**

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
```

**Request:**
```json
{
  "plan_id": 1,
  "date": "2026-01-02",
  "exercises": [
    {
      "exercise_id": 1,
      "sets_completed": 4,
      "reps_per_set": [10, 10, 8, 8],
      "weight_used_kg": 80,
      "notes": "Muy bueno, sentí mucho pump",
      "completed_at": "2026-01-02T18:30:00Z"
    }
  ],
  "duration_minutes": 45,
  "completed_at": "2026-01-02T19:15:00Z",
  "notes": "Muy buena sesión"
}
```

**Response (201):**
```json
{
  "id": 1,
  "uuid": "workout-uuid",
  "plan_id": 1,
  "date": "2026-01-02",
  "duration_minutes": 45,
  "completed_exercises": 5,
  "completed_at": "2026-01-02T19:15:00Z"
}
```

---

#### `GET /api/workouts`
**Status:** ❌ NO IMPLEMENTADO

**Obtener historial de sesiones del alumno**

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
```

**Query Parameters:**
- `from=2026-01-01`
- `to=2026-01-31`
- `plan_id=1`

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "workout-uuid",
      "date": "2026-01-02",
      "plan_name": "Hipertrofia A",
      "exercises_completed": 5,
      "duration_minutes": 45,
      "completed_at": "2026-01-02T19:15:00Z"
    }
  ],
  "pagination": { ... }
}
```

---

### Grupo 5: Mensajes/Comunicación

#### `GET /api/messages`
**Status:** ❌ NO IMPLEMENTADO

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
```

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "from_user_id": 2,
      "from_name": "Mario Entrenador",
      "subject": "Sobre tu rutina",
      "body": "He visto que completas bien...",
      "read": false,
      "created_at": "2026-01-02T10:30:00Z"
    }
  ]
}
```

---

#### `POST /api/messages`
**Status:** ❌ NO IMPLEMENTADO

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
```

**Request:**
```json
{
  "to_user_id": 2,
  "subject": "Pregunta sobre ejercicio",
  "body": "¿Puedo cambiar el press de banca por mancuernas?"
}
```

---

### Grupo 6: Pagos

#### `GET /api/payments`
**Status:** ❌ NO IMPLEMENTADO (Existe `PaymentController` pero no API)

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
```

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "amount": 5000,
      "currency": "ARS",
      "status": "completed",
      "method": "mercadopago",
      "completed_at": "2025-12-30T15:00:00Z",
      "invoice_url": "..."
    }
  ],
  "pending": {
    "amount": 2500,
    "due_date": "2026-01-10"
  }
}
```

---

## Modelos de Datos Disponibles

### Student (Completamente mappeable)
```php
{
  "id": 1,
  "uuid": "550e8400-e29b-41d4-a716-446655440000",
  "status": "active|paused|inactive|prospect",
  "email": "juan@example.com",
  "first_name": "Juan",
  "last_name": "Pérez",
  "phone": "+54 9 11 1234 5678",
  "timezone": "America/Argentina/Buenos_Aires",
  "goal": "hipertrofia|perdida_grasa|resistencia|etc",
  "is_user_enabled": true,
  "current_level": "principiante|intermedio|avanzado",
  "commercial_plan_id": 1,
  "billing_frequency": "monthly|quarterly|yearly",
  "account_status": "ok|suspended|expired",
  
  // JSON columns (virtuales vía accessors)
  "personal_data": {
    "birth_date": "1990-05-15",
    "gender": "M|F|O",
    "height_cm": 178.5,
    "weight_kg": 85.5
  },
  "health_data": {
    "injuries": "Lesión en hombro izquierdo",
    "allergies": [...]
  },
  "training_data": {
    "experience": "Principiante|Intermedio|Avanzado",
    "days_per_week": 4,
    "equipment_access": ["dumbbells", "barbell", ...]
  },
  "communication_data": {
    "language": "es|en|pt",
    "notifications": {
      "new_plan": true,
      "session_reminder": true,
      "payment_reminder": true
    }
  },
  "extra_data": {
    "emergency_contact": {
      "name": "María Pérez",
      "phone": "+54 9 11 9999 9999"
    }
  },
  
  "last_login_at": "2026-01-02T10:30:00Z",
  "created_at": "2025-12-01T08:00:00Z",
  "updated_at": "2026-01-02T14:22:00Z"
}
```

### TrainingPlan (Completamente mappeable)
```php
{
  "id": 1,
  "uuid": "plan-uuid-1234",
  "name": "Hipertrofia A",
  "description": "Enfoque en pecho, espalda y brazos",
  "goal": "hipertrofia",
  "duration": "4 semanas",
  "is_active": true,
  "student_id": 1,
  "assigned_from": "2026-01-02",
  "assigned_until": "2026-01-30",
  "meta": {
    "version": 1.0,
    "origin": "new|cloned|template",
    "notes": "..."
  },
  
  "exercises": [
    {
      "id": 1,
      "uuid": "exercise-uuid",
      "name": "Press de Banca",
      "description": "Ejercicio para pecho",
      "category": "Upper Body",
      "equipment": ["barbell", "bench"],
      
      // Datos del pivote (plan_exercise)
      "pivot": {
        "day": "Monday|1", // Día de la semana o número
        "detail": {
          "sets": 4,
          "reps": "8-10",
          "rest_seconds": 90,
          "tempo": "3-1-2",
          "video_url": "https://..."
        },
        "notes": "Mantener control en la bajada",
        "meta": { ... }
      }
    }
  ],
  
  "created_at": "2026-01-01T12:00:00Z",
  "updated_at": "2026-01-02T14:22:00Z"
}
```

### Exercise (Apenas usado pero disponible)
```php
{
  "id": 1,
  "uuid": "exercise-uuid",
  "name": "Press de Banca",
  "description": "Levanta peso hacia arriba desde el pecho",
  "category": "Upper Body",
  "primary_muscle": "Chest",
  "secondary_muscles": ["Front Deltoids", "Triceps"],
  "equipment": ["barbell", "bench"],
  "difficulty": "beginner|intermediate|advanced",
  "instructions": "1. Acuéstate en el banco...",
  "is_active": true
}
```

---

## Tareas Pendientes

### Fase 1: API de Autenticación (CRÍTICA)
- [ ] **Completar respuesta de login** ✋ (parcialmente hecho)
  - Retornar completo: tenant, user, student, token
  - Validar que `student.is_user_enabled === true`
  
- [ ] **Crear endpoint `POST /api/auth/logout`**
  - Revocar token
  
- [ ] **Crear endpoint `POST /api/auth/list-tenants`** (OPCIONAL)
  - Para soportar usuarios en múltiples tenants
  
- [ ] **Crear Middleware de API Tenancy**
  - Leer `X-Tenant-ID` desde header
  - Inicializar tenancy en el contexto

### Fase 2: API de Estudiante
- [ ] **Crear controlador `StudentApiController`**
  - `GET /api/profile` → Obtener perfil del alumno logueado
  - `PATCH /api/profile` → Actualizar perfil
  - `GET /api/students/{id}` → Obtener datos del estudiante (si es admin)
  
### Fase 3: API de Planes
- [ ] **Crear controlador `TrainingPlanApiController`**
  - `GET /api/students/{id}/plans` → Listar planes del alumno
  - `GET /api/plans/{id}` → Detalle de plan con ejercicios
  
### Fase 4: API de Workouts (Sesiones)
- [ ] **Crear modelo `Workout`** (no existe)
  - Relación: `belongsTo(TrainingPlan)`, `belongsTo(Student)`
  - Campos: plan_id, student_id, date, duration_minutes, completed_at, notes
  
- [ ] **Crear modelo `WorkoutExercise`** (no existe)
  - Relación: `belongsTo(Workout)`, `belongsTo(Exercise)`
  - Campos: sets_completed, reps_per_set (JSON), weight_used_kg, notes
  
- [ ] **Crear migraciones** para Workout y WorkoutExercise
  
- [ ] **Crear controlador `WorkoutApiController`**
  - `POST /api/workouts` → Registrar sesión
  - `GET /api/workouts` → Historial
  
### Fase 5: API de Mensajes
- [ ] **Crear controlador `MessageApiController`**
  - `GET /api/messages` → Listar mensajes
  - `POST /api/messages` → Enviar mensaje
  - Marcar como leído
  
### Fase 6: Documentación
- [ ] **Instalar L5 Swagger**
  - `composer require darkaonline/l5-swagger`
  - Documentar todos los endpoints
  
- [ ] **Crear Postman Collection**

---

## Configuración en Expo

### 1. Instalación de dependencias

```bash
npx create-expo-app fittrack-mobile
cd fittrack-mobile
npm install axios react-native-async-storage axios
# O con expo CLI
expo install axios async-storage
```

### 2. Estructura base de carpetas

```
fittrack-mobile/
├── app/
│   ├── (auth)/
│   │   ├── login.js
│   │   └── select-tenant.js
│   ├── (app)/
│   │   ├── home.js
│   │   ├── profile.js
│   │   ├── plans.js
│   │   ├── workout.js
│   │   └── messages.js
│   └── index.js
├── api/
│   ├── client.js        // Instancia de axios
│   ├── auth.js          // auth/login, auth/logout
│   ├── profile.js       // GET/PATCH /profile
│   ├── plans.js         // GET plans, GET plan/{id}
│   └── workouts.js      // POST/GET workouts
├── context/
│   └── AuthContext.js   // State management
├── constants/
│   └── config.js        // URLs, credentials
└── screens/
    ├── LoginScreen.js
    └── ... etc
```

### 3. Instancia de API (axios)

```javascript
// api/client.js
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'https://api.fittrack.com.ar';

const client = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
});

// Interceptor: agregar token y tenant_id a cada request
client.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('fittrack_token');
  const tenantId = await AsyncStorage.getItem('fittrack_tenant_id');

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  if (tenantId) {
    config.headers['X-Tenant-ID'] = tenantId;
  }

  return config;
});

export default client;
```

### 4. Funciones de API

```javascript
// api/auth.js
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
    throw error.response?.data || error;
  }
};

export const logoutStudent = async () => {
  try {
    await client.post('/auth/logout');
    await AsyncStorage.removeItem('fittrack_token');
    await AsyncStorage.removeItem('fittrack_tenant_id');
    await AsyncStorage.removeItem('fittrack_student_data');
  } catch (error) {
    console.error('Logout error:', error);
  }
};
```

### 5. Context/State Management

```javascript
// context/AuthContext.js
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
      let token, user, tenant;
      try {
        token = await AsyncStorage.getItem('fittrack_token');
        user = await AsyncStorage.getItem('fittrack_user_email');
        tenant = await AsyncStorage.getItem('fittrack_tenant_id');
      } catch (e) {
        // Ignorar errores
      }
      dispatch({
        type: 'RESTORE_TOKEN',
        payload: { token, user, tenant },
      });
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

### 6. Navegación

```javascript
// app/index.js (usando Expo Router)
import React, { useContext } from 'react';
import { AuthContext } from '../context/AuthContext';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

const Stack = createNativeStackNavigator();

export default function RootLayout() {
  const { state } = useContext(AuthContext);
  const { isLoading, isSignout, token } = state;

  if (isLoading) {
    return <LoadingScreen />;
  }

  return (
    <NavigationContainer>
      <Stack.Navigator>
        {isSignout || !token ? (
          <Stack.Group>
            <Stack.Screen name="Login" component={LoginScreen} />
          </Stack.Group>
        ) : (
          <Stack.Group>
            <Stack.Screen name="Home" component={HomeScreen} />
            <Stack.Screen name="Plans" component={PlansScreen} />
            <Stack.Screen name="Profile" component={ProfileScreen} />
          </Stack.Group>
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}
```

---

## Checklist de Implementación

### Backend (Laravel)
- [ ] Completar respuesta de `/api/auth/login`
- [ ] Crear middleware de tenancia para API
- [ ] Crear controladores API para:
  - [ ] StudentApiController
  - [ ] TrainingPlanApiController
  - [ ] WorkoutApiController
  - [ ] MessageApiController
- [ ] Crear modelos Workout y WorkoutExercise
- [ ] Crear migraciones para Workout y WorkoutExercise
- [ ] Documentar API (L5 Swagger)

### Frontend (Expo)
- [ ] Setup inicial de proyecto
- [ ] Implementar AuthContext
- [ ] Crear screens:
  - [ ] LoginScreen
  - [ ] HomeScreen (Dashboard)
  - [ ] PlansScreen
  - [ ] WorkoutScreen
  - [ ] ProfileScreen
- [ ] Implementar servicios de API
- [ ] Implementar persistencia con AsyncStorage

---

## Notas Importantes

1. **Tenancia en API:** Las rutas `/api/*` NO tienen soporte de tenancia automática. Se DEBE enviar `X-Tenant-ID` en headers o usar un middleware especial.

2. **Token sin expiración:** Sanctum está configurado con `'expiration' => null`, lo que significa tokens indefinidos. Para producción, considerar agregar expiración.

3. **CORS:** Actualmente acepta `['*']` en orígenes. Para producción, restringir a dominios específicos.

4. **Email único por sistema:** El email del alumno es único GLOBALMENTE, pero el alumno puede estar en múltiples tenants. Se debe soportar esto en login.

5. **Media Library:** Student y TrainingPlan tienen fotos. Asegurarse de que las URLs se retornen en endpoints API.

---

## Referencias de Código

- Controlador actual: `app/Http/Controllers/Central/AuthController.php`
- Modelo Student: `app/Models/Tenant/Student.php`
- Modelo TrainingPlan: `app/Models/Tenant/TrainingPlan.php`
- Rutas web: `routes/tenant-student.php`
- Configuración CORS: `config/cors.php`
- Configuración Sanctum: `config/sanctum.php`

---

**Próximo paso:** Implementar Fase 1 (Autenticación completa) y luego Fase 2 (Perfil del alumno).
