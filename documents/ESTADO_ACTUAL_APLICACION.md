# FitTrack - Referencia técnica

## Stack
- Laravel 12 + Livewire 3
- MySQL multi-tenant (Stancl)
- API REST (Sanctum)
- Tailwind + Flux UI

## Módulos operativos
1. **Autenticación:** Central + Per-tenant + API
2. **Estudiantes:** CRUD, asignación de planes, gamificación
3. **Planes:** CRUD, snapshot en asignación, +100 ejercicios
4. **Workouts:** Generación automática, logging de ejercicios
5. **Métricas:** Peso, progreso, estadísticas
6. **Mensajería:** Chat entrerador-estudiante
7. **Branding:** Automático en respuestas API

## Archivos clave
- `app/Models/Central/` - Modelos compartidos
- `app/Models/Tenant/` - Modelos por tenant
- `app/Http/Controllers/Api/` - API REST
- `app/Services/Tenant/` - Lógica de negocio
- `routes/api.php` - 20 endpoints

## Base de datos
- Central: 1 DB
- Tenants: N DBs (database-per-tenant)
- Migrations en `database/migrations/` y `database/migrations/tenant/`

## Próximos features opcionales
- Gamificación avanzada
- Integración OpenAI para planes automáticos
- Mercado Pago para pagos
- Media Library para imágenes
├── tenant-auth.php           ← Autenticación tenant
└── tenant-student.php        ← Rutas estudiante

resources/
├── views/                    ← Blade templates
├── css/                      ← Tailwind + custom
└── js/                       ← JavaScript/Alpine
```

---

## 🔑 Módulos Principales

### 1️⃣ Autenticación & Tenancy

**Status:** ✅ Completamente funcional

**Archivos clave:**
- `app/Models/Central/User.php` - Modelo central de usuarios
- `app/Models/Tenant.php` - Configuración de tenants
- `app/Http/Middleware/` - Inicialización automática de tenancy

**Capacidades:**
- Multi-tenant completamente aislado
- Autenticación central + per-tenant
- API authentication con Sanctum (tokens sin expiración)
- Roles y permisos por tenant

**Endpoints:**
```
POST   /api/auth/login          ✅ Auto-detecta tenant por email
POST   /api/auth/logout         ✅
```

---

### 2️⃣ Gestión de Estudiantes

**Status:** ✅ Producción

**Modelos:**
- `Student.php` - Datos estudiante (peso, metrics)
- `StudentPlanAssignment.php` - Asignación con snapshot
- `StudentGamificationProfile.php` - Puntos y logros

**Capacidades:**
- CRUD completo de estudiantes
- Asignación de planes con snapshot
- Historial de peso/métricas
- Gamificación (puntos, logros)
- Avatar con Media Library

**Endpoints API:**
```
GET    /api/profile             ✅ Datos estudiante
PATCH  /api/profile             ✅ Actualizar
```

---

### 3️⃣ Planes de Entrenamiento

**Status:** ✅ Producción

**Modelos:**
- `TrainingPlan.php` - Template de plan
- `Exercise.php` - Ejercicios individuales
- `StudentPlanAssignment.php` - Snapshot del plan asignado

**Capacidades:**
- CRUD de planes
- +100 ejercicios predefinidos
- Asignación con snapshot (cambios en template no afectan planes activos)
- Generación automática con OpenAI (experimental)
- Período de inicio y fin configurable

**Endpoints API:**
```
GET    /api/plans               ✅ Listar planes
GET    /api/plans/current       ✅ Plan activo
GET    /api/plans/{id}          ✅ Detalles con ejercicios
```

---

### 4️⃣ Workouts (Sistema de Entrenamiento)

**Status:** ✅ Producción

**Modelos:**
- `Workout.php` - Sesión de entrenamiento
- `ExerciseCompletionLog.php` - Log de ejercicios completados

**Capacidades:**
- Generación automática de workouts por día
- Log detallado de ejercicios (sets, reps, peso)
- Estados: pending, in_progress, completed
- Inicio/finalización de sesión
- Estadísticas de ejecución

**Endpoints API:**
```
GET    /api/workouts            ✅ Listar todos
GET    /api/workouts/today      ✅ Obtener/crear del día
GET    /api/workouts/stats      ✅ Estadísticas
GET    /api/workouts/{id}       ✅ Detalles
POST   /api/workouts/{id}/start    ✅ Iniciar
PATCH  /api/workouts/{id}       ✅ Actualizar ejercicios
POST   /api/workouts/{id}/complete ✅ Finalizar
```

---

### 5️⃣ Métricas & Peso

**Status:** ✅ Producción

**Modelos:**
- `StudentWeightEntry.php` - Registro de peso

**Capacidades:**
- Registro de peso con fecha
- Historial y gráficas
- Cálculo de progreso
- Alertas por pérdida/ganancia

**Endpoints API:**
```
GET    /api/profile/weight      ✅ Historial
POST   /api/profile/weight      ✅ Registrar nuevo peso
```

---

### 6️⃣ Progreso & Estadísticas

**Status:** ✅ Producción

**Capacidades:**
- Cálculo de progreso general
- Estadísticas de workouts
- Gráficas de métricas
- Comparativas período a período

**Endpoints API:**
```
GET    /api/progress            ✅ Resumen progreso
GET    /api/progress/details    ✅ Detalles
```

---

### 7️⃣ Mensajería (Chat)

**Status:** ✅ Producción

**Modelos:**
- `Conversation.php` - Conversaciones
- `Message.php` - Mensajes
- `ConversationParticipant.php` - Participantes

**Capacidades:**
- Chat entrerador ↔ estudiante
- Conversaciones con múltiples participantes
- Historial persistente
- Event-driven notifications

**Endpoints API:**
```
POST   /api/messages/send       ✅ Enviar mensaje
GET    /api/messages/conversation ✅ Historial
```

---

### 8️⃣ Pagos & Facturación

**Status:** ✅ Producción

**Modelos:**
- `Payment.php` - Pagos registrados
- `Invoice.php` - Facturas
- `CommercialPlan.php` - Planes comerciales
- `PaymentMethod.php` - Métodos de pago

**Integraciones:**
- Mercado Pago API (pagos automáticos)
- Webhooks para confirmación
- Cálculo de cuotas

**Capacidades:**
- Crear facturas automáticamente
- Procesar pagos con Mercado Pago
- Generar PDFs con DomPDF
- Historial de transacciones

---

### 9️⃣ Branding Dinámico

**Status:** ✅ Producción

**Archivos:**
- `app/Services/Tenant/BrandingService.php` - Servicio centralizado
- `app/Http/Middleware/Api/AddBrandingToResponse.php` - Middleware automático

**Capacidades:**
- Logo por entrenador
- Colores personalizados
- Datos (nombre, email, phone)
- Injección automática en respuestas API

**Configuración:**
- Dashboard para subir logo
- Color picker integrado
- Almacenamiento en Media Library

---

### 🔟 Dashboard & Admin Central

**Status:** ✅ Producción

**Capacidades:**
- Vista de todos los tenants
- Estadísticas globales
- Deploy logs
- Manuals (documentación para trainers)
- Landing page customizable

**Livewire Components:**
- Tablas con filtros y búsqueda
- Modales de confirmación
- Gráficos con ApexCharts

---

## 📊 Estadísticas del Código

### Modelos (15 + 11)
**Central:**
- User, Tenant, TenantConfiguration, Permission, Configuration, Contact, DeployLog, LandingBanner, LandingBooklet, LandingCard

**Tenant:**
- Student, TrainingPlan, Exercise, Workout, ExerciseCompletionLog
- StudentPlanAssignment, StudentWeightEntry, StudentGamificationProfile
- CommercialPlan, Payment, PaymentMethod
- Invoice, Message, Conversation, ConversationParticipant

### Controllers (20+)
- **API:** Auth, Student, TrainingPlan, Workout, Weight, Progress, Messages
- **Auth:** Login, Register
- **Central:** Dashboard, Tenants, Manuals, Contacts, DeployLogs, etc
- **Tenant:** Dashboard, Students, TrainingPlans, Exercises, Payments, Messages, etc

### Livewire Components (30+)
- Gestión de estudiantes, planes, ejercicios
- Formularios de billing y pagos
- Configuración y perfil
- Landing pages editable
- Tablas con búsqueda y filtros

### Rutas (50+)
```
- Central (web.php): Dashboard, manuals, contacts, landing
- Tenant (tenant.php): Entrenador workflows
- API (api.php): 20 endpoints REST
- Auth: Login/logout para ambos contextos
```

### Base de datos
- **Central:** 16 tablas
- **Tenant:** 13 tablas por tenant
- **Total:** 29 tablas + índices

---

## ⚙️ Features Implementadas

### ✅ Completamente Funcionales

| Feature | Modulo | Status |
|---------|--------|--------|
| CRUD Estudiantes | Student | ✅ |
| Asignación de Planes | StudentPlanAssignment | ✅ |
| Generación Workouts | Workout Orchestration | ✅ |
| Log de Ejercicios | ExerciseCompletionLog | ✅ |
| Tracking de Peso | StudentWeightEntry | ✅ |
| Chat Entrenador-Estudiante | Messaging | ✅ |
| Facturación | Invoice + Payment | ✅ |
| Pagos (Mercado Pago) | PaymentService | ✅ |
| Branding Dinámico | BrandingService | ✅ |
| Gamificación (Puntos) | StudentGamificationProfile | ✅ |
| API REST (20 endpoints) | ApiControllers | ✅ |
| Multi-tenant | Stancl Tenancy | ✅ |
| Autenticación JWT | Sanctum | ✅ |
| Media Library | Spatie + S3 | ✅ |

### 🔲 Experimental/Parcial

| Feature | Modulo | Status |
|---------|--------|--------|
| Generación automática de planes | OpenAI | 🔲 Beta |
| Sistema de alertas | Notifications | 🔲 Parcial |

---

## 🚨 Consideraciones Técnicas

### Fortalezas
✅ **Arquitectura robusta:** Multi-tenant completamente aislado  
✅ **API producción-lista:** 20 endpoints documentados  
✅ **Escalabilidad:** Base de datos por tenant  
✅ **Seguridad:** Sanctum + Spatie Permissions  
✅ **UX moderna:** Livewire + Flux + Tailwind CSS  
✅ **Documentación exhaustiva:** Guías de integración y estándares  

### Puntos de Mejora Potencial
⚠️ **Caché:** Implementar Redis para sesiones y datos frecuentes  
⚠️ **Queue jobs:** Refactorizar pagos pesados a jobs asincronos  
⚠️ **Testing:** Ampliar cobertura de tests unitarios y E2E  
⚠️ **Monitoring:** Integrar Sentry o similar para error tracking  
⚠️ **CI/CD:** Automatizar deploy y testing  

---

## 📱 API Mobile (20 Endpoints)

**Documentación completa en:** [MOBILE_API_NEXTGO_COMPLETE.md](MOBILE_API_NEXTGO_COMPLETE.md)

### Categorías

| Categoría | Endpoints | Status |
|-----------|-----------|--------|
| Autenticación | 2 | ✅ |
| Perfil | 2 | ✅ |
| Planes | 3 | ✅ |
| Workouts | 8 | ✅ |
| Peso/Métricas | 2 | ✅ |
| Progreso | 2 | ✅ |
| Mensajes | 1 | ✅ |
| **Total** | **20** | **✅** |

---

## 🎯 Próximas Prioridades

### Corto Plazo (1-2 semanas)
1. ✅ Limpiar documentación (completado)
2. 📋 Escribir tests unitarios para servicios críticos
3. 📋 Implementar caché en endpoints frecuentes
4. 📋 Añadir rate limiting a API

### Mediano Plazo (1 mes)
1. 📋 Refactorizar pagos a jobs asincronos
2. 📋 Mejorar gráficas de progreso
3. 📋 Implementar notificaciones push
4. 📋 Crear dashboard mobile web-responsive

### Largo Plazo (2-3 meses)
1. 📋 CI/CD automatizado (GitHub Actions)
2. 📋 Monitoring y alertas (Sentry)
3. 📋 Tests E2E con Dusk
4. 📋 Optimización de imágenes y caché

---

## 📚 Documentación Disponible

| Documento | Propósito |
|-----------|-----------|
| [INDEX.md](INDEX.md) | 👈 Guía de navegación |
| [FINAL_STATUS.md](FINAL_STATUS.md) | Resumen ejecutivo |
| [API_README.md](API_README.md) | Quick start API |
| [MOBILE_API_NEXTGO_COMPLETE.md](MOBILE_API_NEXTGO_COMPLETE.md) | Referencia técnica completa |
| [BRANDING_CONFIG_GUIDE.md](BRANDING_CONFIG_GUIDE.md) | Configuración de marca |
| [NEXTGO_INTEGRATION_CHECKLIST.md](NEXTGO_INTEGRATION_CHECKLIST.md) | Integración paso a paso |
| `/disenio_ux/` | Estándares y patrones |
| `/diagramas_arquitectura/` | Diagramas técnicos |

---

## 🔍 Cómo Comenzar

### 1. Entender la Arquitectura
```
Lee: FINAL_STATUS.md (10 min)
```

### 2. Explorar la API
```
Lee: API_README.md + MOBILE_API_NEXTGO_COMPLETE.md (45 min)
Prueba: curl/Postman con ejemplos
```

### 3. Implementar Feature Nueva
```
1. Abre /disenio_ux/ para estándares
2. Crea modelo en app/Models/Tenant/
3. Crea servicio en app/Services/Tenant/
4. Crea Livewire component en app/Livewire/Tenant/
5. Añade ruta en routes/tenant.php
6. Prueba y documenta
```

### 4. Contribuir a la API Mobile
```
1. Revisa NEXTGO_INTEGRATION_CHECKLIST.md
2. Implementa endpoint en app/Http/Controllers/Api/
3. Documenta en MOBILE_API_NEXTGO_COMPLETE.md
4. Prueba con ejemplos de curl
```

---

**Generado:** Enero 29, 2026  
**Versión:** FitTrack 1.0 - Producción Ready  
**Mantenedor:** AI Coding Agent
