# FitTrack Mobile API - Documentación

**Versión:** 1.0  
**Fecha:** Enero 2026  
**Estado:** ✅ Implementado

---

## 🚀 Configuración Rápida

### Headers Requeridos

**Para autenticación (login):**
```
Content-Type: application/json
```

**Para todas las demás rutas:**
```
Content-Type: application/json
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
```

### Base URL
```
Local: http://localhost/api
Producción: https://api.fittrack.com/api
```

---

## 🔐 Autenticación

### POST /api/auth/login

Iniciar sesión (detecta automáticamente el tenant).

**Request:**
```json
{
  "email": "juan@example.com",
  "password": "password123"
}
```

**Response 200:**
```json
{
  "tenant": {
    "id": "fittrack_client1",
    "name": "Client 1",
    "domain": "http://fittrack_client1.fittrack.test"
  },
  "user": {
    "id": 1,
    "email": "juan@example.com",
    "name": "Juan Pérez"
  },
  "student": {
    "id": 1,
    "uuid": "abc-123",
    "email": "juan@example.com",
    "first_name": "Juan",
    "last_name": "Pérez",
    "full_name": "Juan Pérez",
    "phone": "+54 9 11 1234 5678",
    "goal": "hipertrofia",
    "status": "active",
    "height_cm": 178,
    "weight_kg": 85.5,
    "imc": 27.80,
    "training_experience": "Intermedio",
    "days_per_week": 4
  },
  "token": "1|abc123xyz..."
}
```

### POST /api/auth/logout

Cerrar sesión.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "message": "Sesión cerrada correctamente"
}
```

---

## 👤 Perfil del Estudiante

### GET /api/profile

Obtener datos del perfil del estudiante autenticado.

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: fittrack_client1
```

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "uuid": "abc-123",
    "email": "juan@example.com",
    "first_name": "Juan",
    "last_name": "Pérez",
    "full_name": "Juan Pérez",
    "phone": "+54 9 11 1234 5678",
    "goal": "hipertrofia",
    "status": "active",
    "height_cm": 178,
    "weight_kg": 85.5,
    "imc": 27.80,
    "language": "es",
    "training_experience": "Intermedio",
    "days_per_week": 4
  }
}
```

### PATCH /api/profile

Actualizar datos del perfil.

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: fittrack_client1
```

**Request:**
```json
{
  "first_name": "Juan Carlos",
  "weight_kg": 84.0,
  "goal": "definición"
}
```

**Response 200:**
```json
{
  "message": "Perfil actualizado correctamente",
  "data": { /* datos actualizados */ }
}
```

---

## 🏋️ Planes de Entrenamiento

### GET /api/plans

Listar todos los planes asignados al estudiante.

**Headers:**
```
Authorization: Bearer {token}
X-Tenant-ID: fittrack_client1
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "def-456",
      "name": "Hipertrofia A",
      "description": "Plan de hipertrofia para principiantes",
      "goal": "hipertrofia",
      "duration": "4 semanas",
      "is_active": true,
      "assigned_from": "2026-01-02",
      "assigned_until": "2026-01-30",
      "exercises_count": 12,
      "created_at": "2026-01-02 10:00:00"
    }
  ]
}
```

### GET /api/plans/current

Obtener el plan de entrenamiento activo actual.

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "name": "Hipertrofia A",
    "goal": "hipertrofia",
    "assigned_from": "2026-01-02",
    "assigned_until": "2026-01-30",
    "exercises": [
      {
        "id": 1,
        "name": "Press de Banca",
        "day": "Monday",
        "sets": 4,
        "reps": "8-10",
        "weight": 80,
        "video_url": "https://..."
      }
    ]
  }
}
```

### GET /api/plans/{id}

Obtener detalles completos de un plan específico.

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "uuid": "def-456",
    "name": "Hipertrofia A",
    "description": "Plan completo",
    "goal": "hipertrofia",
    "is_active": true,
    "assigned_from": "2026-01-02",
    "assigned_until": "2026-01-30",
    "exercises": [
      {
        "id": 1,
        "name": "Press de Banca",
        "description": "Ejercicio compuesto para pecho",
        "category": "fuerza",
        "muscle_group": "pecho",
        "difficulty": "intermedio",
        "day": "Monday",
        "sets": 4,
        "reps": "8-10",
        "weight": 80,
        "duration": null,
        "rest_time": "90",
        "tempo": "2-0-2-0",
        "notes": "Bajar controlado",
        "video_url": "https://...",
        "image_url": "https://..."
      }
    ]
  }
}
```

---

## 💪 Sesiones de Entrenamiento (Workouts)

### POST /api/workouts

Registrar una nueva sesión de entrenamiento.

**Request:**
```json
{
  "training_plan_id": 1,
  "date": "2026-01-02",
  "duration_minutes": 45,
  "status": "completed",
  "rating": 4,
  "notes": "Buen entrenamiento",
  "exercises": [
    {
      "exercise_id": 1,
      "sets_completed": 4,
      "reps_per_set": [10, 10, 8, 8],
      "weight_used_kg": 80,
      "notes": "Última serie con ayuda"
    },
    {
      "exercise_id": 2,
      "sets_completed": 3,
      "reps_per_set": [12, 10, 10],
      "weight_used_kg": 25
    }
  ]
}
```

**Response 201:**
```json
{
  "message": "Sesión de entrenamiento registrada correctamente",
  "data": {
    "id": 1,
    "uuid": "xyz-789",
    "date": "2026-01-02",
    "duration_minutes": 45,
    "status": "completed",
    "rating": 4,
    "exercises_count": 2,
    "training_plan": {
      "id": 1,
      "name": "Hipertrofia A",
      "goal": "hipertrofia"
    },
    "exercises": [/* detalles */]
  }
}
```

### GET /api/workouts

Listar todas las sesiones de entrenamiento.

**Query params opcionales:**
- `from_date`: Filtrar desde fecha (YYYY-MM-DD)
- `to_date`: Filtrar hasta fecha (YYYY-MM-DD)
- `training_plan_id`: Filtrar por plan
- `limit`: Número máximo de resultados (default: 50)

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "xyz-789",
      "date": "2026-01-02",
      "duration_minutes": 45,
      "status": "completed",
      "rating": 4,
      "exercises_count": 2,
      "training_plan": {
        "id": 1,
        "name": "Hipertrofia A"
      }
    }
  ]
}
```

### GET /api/workouts/{id}

Obtener detalles de una sesión específica.

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "uuid": "xyz-789",
    "date": "2026-01-02",
    "duration_minutes": 45,
    "status": "completed",
    "rating": 4,
    "notes": "Buen entrenamiento",
    "exercises_count": 2,
    "training_plan": {
      "id": 1,
      "name": "Hipertrofia A",
      "goal": "hipertrofia"
    },
    "exercises": [
      {
        "id": 1,
        "exercise_id": 1,
        "exercise_name": "Press de Banca",
        "sets_completed": 4,
        "reps_per_set": [10, 10, 8, 8],
        "weight_used_kg": 80,
        "notes": "Última serie con ayuda",
        "completed_at": "2026-01-02 15:30:00"
      }
    ]
  }
}
```

---

## 🛠️ Códigos de Error

| Código | Descripción |
|--------|-------------|
| 200 | OK - Éxito |
| 201 | Created - Recurso creado |
| 400 | Bad Request - Falta header X-Tenant-ID |
| 401 | Unauthorized - Token inválido o expirado |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Datos de validación inválidos |
| 500 | Internal Server Error - Error del servidor |

---

## 📝 Notas Importantes

### Multi-tenancy
- Todas las rutas (excepto login/logout) requieren el header `X-Tenant-ID`
- El tenant ID se obtiene en la respuesta del login
- Cada tenant tiene su propia base de datos aislada

### Tokens
- Los tokens son generados con Laravel Sanctum
- Los tokens nunca expiran (configuración actual)
- Para cerrar sesión, usar `/api/auth/logout`

### Validaciones
- Los campos numéricos (peso, altura, etc.) aceptan decimales
- Las fechas deben estar en formato `YYYY-MM-DD`
- Los arrays `reps_per_set` pueden tener cualquier longitud

---

## 🔄 Próximos Pasos

1. **Ejecutar migraciones en cada tenant:**
   ```bash
   php artisan tenants:migrate
   ```

2. **Probar endpoints con Postman/Thunder Client**

3. **Implementar app móvil en Expo**

4. **Agregar tests unitarios**

5. **Documentar con Swagger/OpenAPI**

---

## 📞 Soporte

Para dudas o consultas, revisar los documentos:
- `MOBILE_API_EXPO_SPEC.md`
- `MOBILE_API_IMPLEMENTATION_PLAN.md`
- `RESUMEN_MOBILE_API.md`
