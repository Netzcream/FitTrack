# FitTrack API - Next.go (Guía corta)

## 1) Configuración básica
- Base URL: `NEXT_PUBLIC_API_URL`
- Persistir `token` y `tenant_id` tras login

## 2) Cliente HTTP
Usa un cliente central que inyecte headers `Authorization` y `X-Tenant-ID` en cada request.

## 3) Flujo mínimo
1. `POST /api/auth/login`
2. Guardar `token` y `tenant.id`
3. Consumir:
   - `GET /api/profile`
   - `GET /api/plans/current`
   - `GET /api/workouts/today`
   - `POST /api/workouts/{id}/complete`
   - `GET /api/messages/conversation`

## 4) Branding
Todas las respuestas traen `branding`. Aplicar a CSS variables:

```css
:root {
  --primary-color: #3B82F6;
  --secondary-color: #10B981;
  --accent-color: #F59E0B;
}
```

## 5) Errores
En errores, la respuesta usa `error` y `details` (validaciones). Mostrar mensaje y seguir.

## 6) Proyección
- Push notifications para recordatorios
- Offline caching de workouts
- Métricas avanzadas en progreso# 🚀 FitTrack Mobile API - Documentación Completa (Next.go Edition)

## 📋 Índice

1. [Autenticación](#autenticación)
2. [Estructura de Respuestas](#estructura-de-respuestas)
3. [Branding & Personalización](#branding--personalización)
4. [Endpoints](#endpoints)
   - [Perfil](#perfil)
   - [Planes](#planes)
   - [Workouts](#workouts)
   - [Peso](#peso)
   - [Progreso](#progreso)
   - [Mensajería](#mensajería)
5. [Ejemplos de Uso](#ejemplos-de-uso)
6. [Códigos de Error](#códigos-de-error)

---

## 🔐 Autenticación

### Login (Detecta tenant automáticamente)

**Endpoint:**
```
POST /api/auth/login
Content-Type: application/json
```

**Body:**
```json
{
  "email": "student@trainer.com",
  "password": "password123"
}
```

**Respuesta exitosa (200):**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "email": "student@trainer.com"
  },
  "student": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "email": "student@trainer.com",
    "first_name": "Juan",
    "last_name": "Pérez",
    "full_name": "Juan Pérez",
    "phone": "+54 9 11 2345-6789",
    "goal": "Muscle Gain",
    "status": "active",
    "timezone": "America/Argentina/Buenos_Aires",
    "current_level": "intermediate"
  },
  "tenant": {
    "id": "trainer-01",
    "name": "Juan's Coaching"
  },
  "branding": {
    "brand_name": "Juan's Coaching",
    "trainer_email": "trainer@example.com",
    "trainer_name": "Juan Pérez",
    "logo_url": "https://example.com/logo.png",
    "logo_light_url": "https://example.com/logo-light.png",
    "primary_color": "#3B82F6",
    "secondary_color": "#10B981",
    "accent_color": "#F59E0B"
  }
}
```

### Logout

**Endpoint:**
```
POST /api/auth/logout
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
```

**Respuesta:**
```json
{
  "message": "Logged out successfully"
}
```

---

## 🎨 Estructura de Respuestas

### Formato Estándar

Todas las respuestas incluyen automáticamente datos de **branding** del trainer:

```json
{
  "data": {
    // Datos de la respuesta (student, plan, workout, etc)
  },
  "message": "Success message (optional)",
  "branding": {
    "brand_name": "Juan's Coaching",
    "trainer_email": "trainer@example.com",
    "trainer_name": "Juan Pérez",
    "logo_url": "https://example.com/logo.png",
    "logo_light_url": "https://example.com/logo-light.png",
    "primary_color": "#3B82F6",
    "secondary_color": "#10B981",
    "accent_color": "#F59E0B"
  }
}
```

### Errores

```json
{
  "error": "Error message",
  "details": {
    "field_name": ["Validation error"]
  },
  "branding": { ... }
}
```

---

## 🎨 Branding & Personalización

### Configuración de Branding

El trainer puede configurar los siguientes valores en el dashboard:

| Key | Descripción | Default |
|-----|-------------|---------|
| `brand_name` | Nombre de la marca/gym | Tenant name |
| `trainer_name` | Nombre completo del trainer | - |
| `trainer_email` | Email de contacto | - |
| `logo_url` | URL del logo (PNG/SVG) | - |
| `logo_light_url` | URL del logo para modo claro | Usa `logo_url` |
| `primary_color` | Color primario (hex) | #3B82F6 (azul) |
| `secondary_color` | Color secundario (hex) | #10B981 (verde) |
| `accent_color` | Color de acento (hex) | #F59E0B (ámbar) |

### Uso en Next.go

```javascript
const response = await fetch('/api/profile', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'X-Tenant-ID': tenantId
  }
});

const { data, branding } = await response.json();

// Usar branding
document.documentElement.style.setProperty('--primary-color', branding.primary_color);
document.documentElement.style.setProperty('--secondary-color', branding.secondary_color);
```

---

## 📡 Endpoints

### Headers Requeridos (Todas las rutas excepto login/logout)

```
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
Content-Type: application/json
```

---

## 👤 Perfil

### GET /api/profile

Obtener datos del estudiante autenticado.

**Respuesta:**
```json
{
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "email": "student@trainer.com",
    "first_name": "Juan",
    "last_name": "Pérez",
    "full_name": "Juan Pérez",
    "phone": "+54 9 11 2345-6789",
    "goal": "Muscle Gain",
    "status": "active",
    "timezone": "America/Argentina/Buenos_Aires",
    "current_level": "intermediate",
    
    // Datos personales
    "birth_date": "1990-05-15",
    "gender": "male",
    "height_cm": 180,
    "weight_kg": 85.5,
    "imc": 26.4,
    
    // Comunicación
    "language": "es",
    "notifications": {
      "workout_reminders_enabled": true,
      "preferred_days": ["monday", "wednesday", "friday"],
      "preferred_times": ["08:00", "18:00"]
    },
    
    // Entrenamiento
    "training_experience": "3 years",
    "days_per_week": 4,
    
    "commercial_plan_id": 1,
    "billing_frequency": "monthly",
    "account_status": "active"
  },
  "branding": { ... }
}
```

### PATCH /api/profile

Actualizar datos del perfil.

**Body:**
```json
{
  "first_name": "Juan",
  "last_name": "Pérez",
  "phone": "+54 9 11 2345-6789",
  "goal": "Muscle Gain",
  "birth_date": "1990-05-15",
  "gender": "male",
  "height_cm": 180,
  "weight_kg": 85.5,
  "timezone": "America/Argentina/Buenos_Aires",
  "language": "es",
  "notifications": {
    "workout_reminders_enabled": true,
    "preferred_days": ["monday", "wednesday", "friday"],
    "preferred_times": ["08:00", "18:00"],
    "channels": ["push", "email"],
    "reminder_minutes_before": 30
  },
  "training_experience": "3 years",
  "days_per_week": 4
}
```

**Respuesta:** Datos del perfil actualizado (mismo formato que GET)

---

## 📋 Planes

### GET /api/plans

Listar todos los planes de entrenamiento asignados.

**Query Parameters:**
- `status` (opcional): `active`, `pending`, `completed`, `cancelled`

**Respuesta:**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "name": "6-Week Muscle Building",
      "description": "Gain 5kg of muscle",
      "goal": "Muscle Gain",
      "duration": 6,
      "is_active": true,
      "assigned_from": "2026-01-15",
      "assigned_until": "2026-02-26",
      "exercises_count": 24,
      "created_at": "2026-01-15T10:00:00Z"
    }
  ],
  "branding": { ... }
}
```

### GET /api/plans/current

Obtener el plan activo actual con todos sus ejercicios.

**Respuesta:**
```json
{
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "name": "6-Week Muscle Building",
    "goal": "Muscle Gain",
    "assigned_from": "2026-01-15",
    "assigned_until": "2026-02-26",
    "exercises": [
      {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440001",
        "name": "Barbell Bench Press",
        "day": 1,
        "sets": 4,
        "reps": 8,
        "weight": 100,
        "duration": null,
        "rest_time": 90,
        "notes": "Heavy day"
      }
    ]
  },
  "branding": { ... }
}
```

### GET /api/plans/{id}

Obtener detalles completos de un plan específico.

**Respuesta:** Mismo formato que `/plans/current` pero con el ID específico.

---

## 💪 Workouts

### GET /api/workouts

Listar todos los workouts del estudiante.

**Query Parameters:**
- `status` (opcional): `pending`, `in_progress`, `completed`, `skipped`

**Respuesta:**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "plan_day": 1,
      "cycle_index": 1,
      "status": "completed",
      "started_at": "2026-01-15T08:00:00Z",
      "completed_at": "2026-01-15T08:45:00Z",
      "duration_minutes": 45,
      "rating": 4,
      "notes": "Great workout!",
      "is_completed": true,
      "is_in_progress": false,
      "created_at": "2026-01-15T07:50:00Z"
    }
  ],
  "branding": { ... }
}
```

### GET /api/workouts/today

Obtener o crear automáticamente el workout de hoy.

**Respuesta:**
```json
{
  "data": {
    "id": 5,
    "uuid": "550e8400-e29b-41d4-a716-446655440005",
    "plan_day": 3,
    "cycle_index": 1,
    "status": "pending",
    "started_at": null,
    "completed_at": null,
    "duration_minutes": null,
    "rating": null,
    "notes": null,
    "is_completed": false,
    "is_in_progress": false,
    "exercises": [
      {
        "id": 3,
        "name": "Squats",
        "description": "Heavy compound movement",
        "category": "legs",
        "level": "intermediate",
        "equipment": "barbell",
        "image_url": "https://example.com/squats.jpg",
        "images": [
          {
            "url": "https://example.com/squats-1.jpg",
            "thumb": "https://example.com/squats-1-thumb.jpg"
          }
        ],
        "completed": false,
        "sets": [
          {
            "reps": 6,
            "weight": 150,
            "duration_seconds": null,
            "completed": false
          }
        ]
      }
    ],
    "meta": null
  },
  "branding": { ... }
}
```

### GET /api/workouts/{id}

Obtener detalles completos de un workout.

**Respuesta:** Mismo formato que `/workouts/today` pero con datos específicos del workout.

### POST /api/workouts/{id}/start

Iniciar un workout (cambiar status a `in_progress`).

**Respuesta:**
```json
{
  "message": "Workout started",
  "data": { ... },
  "branding": { ... }
}
```

### PATCH /api/workouts/{id}

Actualizar ejercicios durante la sesión (sincroniza progreso).

**Body:**
```json
{
  "exercises": [
    {
      "id": 3,
      "name": "Squats",
      "completed": true,
      "sets": [
        {
          "reps": 6,
          "weight": 150,
          "duration_seconds": 45,
          "completed": true
        },
        {
          "reps": 6,
          "weight": 150,
          "duration_seconds": 50,
          "completed": true
        }
      ]
    }
  ]
}
```

**Respuesta:**
```json
{
  "message": "Exercises updated",
  "data": { ... },
  "branding": { ... }
}
```

### POST /api/workouts/{id}/complete

Finalizar un workout con duración, rating y survey.

**Body:**
```json
{
  "duration_minutes": 45,
  "rating": 4,
  "notes": "Great workout, felt strong",
  "survey": {
    "fatigue": 3,
    "rpe": 8,
    "pain": 0,
    "mood": "great"
  }
}
```

**Respuesta:**
```json
{
  "message": "Workout completed",
  "data": { ... },
  "branding": { ... }
}
```

### POST /api/workouts/{id}/skip

Saltar un workout.

**Body:**
```json
{
  "reason": "Feeling unwell"
}
```

**Respuesta:**
```json
{
  "message": "Workout skipped",
  "data": { ... },
  "branding": { ... }
}
```

### GET /api/workouts/stats

Obtener estadísticas generales de workouts.

**Respuesta:**
```json
{
  "data": {
    "completed_workouts": 45,
    "pending_workouts": 3,
    "skipped_workouts": 2,
    "average_duration_minutes": 42,
    "average_rating": 4.2,
    "total_duration_minutes": 1890
  },
  "branding": { ... }
}
```

---

## ⚖️ Peso

### GET /api/weight

Obtener historial de peso.

**Query Parameters:**
- `limit` (default: 30): Número máximo de registros
- `days` (opcional): Filtrar por últimos N días

**Respuesta:**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "weight_kg": 85.5,
      "recorded_at": "2026-01-15T10:00:00Z",
      "source": "manual",
      "notes": "After breakfast",
      "meta": {},
      "created_at": "2026-01-15T10:00:00Z"
    }
  ],
  "branding": { ... }
}
```

### GET /api/weight/latest

Obtener el último registro de peso.

**Respuesta:**
```json
{
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "weight_kg": 85.5,
    "recorded_at": "2026-01-15T10:00:00Z",
    "source": "manual",
    "notes": "After breakfast"
  },
  "branding": { ... }
}
```

### POST /api/weight

Registrar un nuevo peso.

**Body:**
```json
{
  "weight_kg": 85.5,
  "recorded_at": "2026-01-15",
  "source": "manual",
  "notes": "After breakfast"
}
```

**Respuesta:**
```json
{
  "message": "Weight recorded successfully",
  "data": { ... },
  "branding": { ... }
}
```

### GET /api/weight/change

Obtener cambio de peso en un período.

**Query Parameters:**
- `days` (default: 7): Período de análisis en días

**Respuesta:**
```json
{
  "data": {
    "period_days": 7,
    "initial_weight_kg": 88.0,
    "current_weight_kg": 85.5,
    "change_kg": -2.5,
    "change_percentage": -2.84,
    "direction": "down"
  },
  "branding": { ... }
}
```

### GET /api/weight/average

Obtener peso promedio en un período.

**Query Parameters:**
- `days` (default: 30): Período de análisis en días

**Respuesta:**
```json
{
  "data": {
    "period_days": 30,
    "average_weight_kg": 86.2
  },
  "branding": { ... }
}
```

---

## 📈 Progreso

### GET /api/progress

Obtener resumen completo de progreso del plan actual.

**Respuesta:**
```json
{
  "data": {
    "has_active_plan": true,
    "plan_name": "6-Week Muscle Building",
    "plan_starts_at": "2026-01-15",
    "plan_ends_at": "2026-02-26",
    "total_plan_days": 6,
    "current_cycle": 1,
    "next_plan_day": 3,
    "progress": {
      "completed_workouts": 12,
      "expected_sessions": 18,
      "progress_percentage": 66.7,
      "is_on_track": false,
      "is_bonus": false
    },
    "current_cycle_complete": false
  },
  "branding": { ... }
}
```

### GET /api/progress/recent

Obtener últimos workouts completados.

**Query Parameters:**
- `limit` (default: 10): Número de workouts a retornar

**Respuesta:**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "plan_day": 1,
      "cycle_index": 1,
      "completed_at": "2026-01-15T08:45:00Z",
      "duration_minutes": 45,
      "rating": 4,
      "notes": "Great workout!"
    }
  ],
  "branding": { ... }
}
```

---

## 💬 Mensajería

### GET /api/messages/conversation

Obtener conversación con el trainer.

**Query Parameters:**
- `per_page` (default: 50): Mensajes por página

**Respuesta:**
```json
{
  "conversation": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "subject": "Chat with trainer",
    "participants": [
      {
        "id": 1,
        "type": "trainer",
        "name": "Juan Pérez"
      }
    ],
    "last_message": {
      "id": 100,
      "body": "Keep pushing!",
      "sender_type": "trainer"
    }
  },
  "messages": [
    {
      "id": 1,
      "body": "Hi trainer, how am I doing?",
      "sender_type": "student",
      "created_at": "2026-01-15T10:00:00Z"
    }
  ],
  "branding": { ... }
}
```

### POST /api/messages/send

Enviar un mensaje al trainer.

**Body:**
```json
{
  "body": "Hi trainer, I have a question about the workout",
  "attachments": [
    {
      "path": "s3://bucket/video.mp4",
      "name": "workout-form.mp4",
      "mime": "video/mp4",
      "size": 5242880
    }
  ]
}
```

**Respuesta:**
```json
{
  "message": "Message sent",
  "data": { ... },
  "branding": { ... }
}
```

### POST /api/messages/read

Marcar conversación como leída.

**Respuesta:**
```json
{
  "message": "Marked as read",
  "branding": { ... }
}
```

### GET /api/messages/unread-count

Obtener cantidad de mensajes no leídos.

**Respuesta:**
```json
{
  "count": 3,
  "branding": { ... }
}
```

### POST /api/messages/mute

Mutear/desmutear conversación.

**Body:**
```json
{
  "mute": true
}
```

**Respuesta:**
```json
{
  "message": "Mute status updated",
  "branding": { ... }
}
```

---

## 📚 Ejemplos de Uso

### Flujo Completo: Entrenar y Registrar

```javascript
const token = "1|abc123...";
const tenantId = "trainer-01";
const headers = {
  'Authorization': `Bearer ${token}`,
  'X-Tenant-ID': tenantId
};

// 1. Obtener workout de hoy
const todayRes = await fetch('/api/workouts/today', { headers });
const { data: workout, branding } = await todayRes.json();

console.log(`Trainer: ${branding.trainer_name}`);
console.log(`Color primario: ${branding.primary_color}`);
console.log(`Ejercicios para hoy: ${workout.exercises.length}`);

// 2. Iniciar workout
await fetch(`/api/workouts/${workout.id}/start`, {
  method: 'POST',
  headers
});

// 3. Durante la sesión, actualizar ejercicios
await fetch(`/api/workouts/${workout.id}`, {
  method: 'PATCH',
  headers,
  body: JSON.stringify({
    exercises: workout.exercises.map((ex, idx) => ({
      id: ex.id,
      completed: idx === 0, // El primero está completado
      sets: ex.sets.map(set => ({
        ...set,
        completed: true
      }))
    }))
  })
});

// 4. Completar workout
await fetch(`/api/workouts/${workout.id}/complete`, {
  method: 'POST',
  headers,
  body: JSON.stringify({
    duration_minutes: 45,
    rating: 4,
    notes: 'Felt great!',
    survey: {
      fatigue: 3,
      rpe: 7,
      pain: 0,
      mood: 'excellent'
    }
  })
});

// 5. Registrar peso
await fetch('/api/weight', {
  method: 'POST',
  headers,
  body: JSON.stringify({
    weight_kg: 85.5,
    source: 'manual'
  })
});

// 6. Ver progreso
const progressRes = await fetch('/api/progress', { headers });
const { data: progress } = await progressRes.json();
console.log(`Progreso: ${progress.progress.progress_percentage}%`);
```

---

## ❌ Códigos de Error

| Código | Significado |
|--------|-------------|
| 200 | OK - Solicitud exitosa |
| 201 | Created - Recurso creado |
| 400 | Bad Request - Datos inválidos |
| 401 | Unauthorized - Token inválido o faltante |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Validación fallida |
| 500 | Internal Server Error - Error del servidor |

### Ejemplo de error:

```json
{
  "error": "Invalid email or password",
  "branding": { ... }
}
```

---

## 🚀 Setup en Next.go

### 1. Instalar dependencias

```bash
npm install axios zustand
```

### 2. Configurar cliente API

```javascript
// lib/api.ts
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api'
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('fittrack_token');
  const tenantId = localStorage.getItem('fittrack_tenant_id');
  
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  if (tenantId) {
    config.headers['X-Tenant-ID'] = tenantId;
  }
  
  return config;
});

export default api;
```

### 3. Crear hook de autenticación

```javascript
// hooks/useAuth.ts
import { create } from 'zustand';
import api from '@/lib/api';

export const useAuth = create((set) => ({
  login: async (email, password) => {
    const res = await api.post('/auth/login', { email, password });
    const { token, student, tenant, branding } = res.data;
    
    localStorage.setItem('fittrack_token', token);
    localStorage.setItem('fittrack_tenant_id', tenant.id);
    localStorage.setItem('fittrack_branding', JSON.stringify(branding));
    
    return { student, branding };
  },
  logout: async () => {
    await api.post('/auth/logout');
    localStorage.clear();
  }
}));
```

---

## 📝 Notas Importantes

1. **Branding**: Se incluye automáticamente en TODAS las respuestas. Guárdalo en el cliente al login.
2. **Timestamps**: Todos los timestamps están en formato ISO 8601 UTC.
3. **X-Tenant-ID**: Requerido en todas las rutas excepto `/auth/login` y `/auth/logout`.
4. **Rate Limiting**: No especificado (implementar según necesidad).
5. **CORS**: Configurado para aceptar requests desde el dominio del cliente.

---

**Última actualización:** Enero 2026
