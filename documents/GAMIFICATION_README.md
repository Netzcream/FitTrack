# Sistema de Gamificación - FitTrack

## 📚 Documentación

Este directorio contiene la documentación completa del sistema de gamificación de FitTrack.

### 📄 Archivos disponibles

1. **[GAMIFICATION_SYSTEM.md](./GAMIFICATION_SYSTEM.md)** - Documentación técnica completa
   - Arquitectura del sistema
   - Modelos y base de datos
   - Eventos y listeners
   - Servicio de gamificación
   - Fórmulas y cálculos
   - Testing
   - Troubleshooting

2. **[GAMIFICATION_QUICKSTART.md](./GAMIFICATION_QUICKSTART.md)** - Guía de inicio rápido
   - Setup inicial
   - Uso básico
   - Ejemplos de código
   - Integración en vistas
   - Testing rápido

---

## 🎯 Resumen del sistema

Sistema de gamificación **simple, acumulativo y no punitivo** que incentiva la adherencia del alumno mediante:

- ✅ **XP (Experience Points)** por ejercicios completados (10/15/20 según dificultad)
- ✅ **Niveles** progresivos basados en XP acumulado
- ✅ **Tiers** (6 rangos: Not Rated → Experto)
- ✅ **Badges** visuales por tier
- ✅ **Anti-farming** garantizado a nivel de base de datos

---

## 🚀 Quick Start

```bash
# 1. Migrar tablas
php artisan tenants:migrate

# 2. Disparar evento al completar ejercicio
use App\Events\Tenant\ExerciseCompleted;

event(new ExerciseCompleted(
    student: $student,
    exercise: $exercise,
    workout: $workout // opcional
));

# 3. Mostrar stats en vista
$service = new GamificationService();
$stats = $service->getStudentStats($student);
```

---

## 📊 Estructura de archivos

### Modelos
- `app/Models/Tenant/StudentGamificationProfile.php`
- `app/Models/Tenant/ExerciseCompletionLog.php`

### Eventos y Listeners
- `app/Events/Tenant/ExerciseCompleted.php`
- `app/Listeners/Tenant/AwardExperiencePoints.php`

### Servicio
- `app/Services/Tenant/GamificationService.php`

### Migraciones
- `database/migrations/tenant/2026_01_18_000001_create_student_gamification_profiles_table.php`
- `database/migrations/tenant/2026_01_18_000002_create_exercise_completion_logs_table.php`

### Configuración
- `config/gamification.php`

### Traducciones
- `resources/lang/es/gamification.php`
- `resources/lang/en/gamification.php`

---

## 🎮 Mecánica del sistema

### XP por dificultad
- **Beginner**: 10 XP
- **Intermediate**: 15 XP
- **Advanced**: 20 XP

### Progresión de niveles
```
Nivel 0:  0 XP     (Not Rated)
Nivel 1:  100 XP   (Principiante)
Nivel 5:  180 XP
Nivel 10: 390 XP
Nivel 15: 760 XP   (Competente)
Nivel 20: 1480 XP  (Avanzado)
Nivel 25: 2890 XP  (Experto)
```

### Tiers
| Tier | Niveles | Nombre | Badge |
|------|---------|--------|-------|
| 0 | 0 | Not Rated | not_rated |
| 1 | 1-5 | Principiante | beginner |
| 2 | 6-10 | Aprendiz | apprentice |
| 3 | 11-15 | Competente | competent |
| 4 | 16-20 | Avanzado | advanced |
| 5 | 21+ | Experto | expert |

---

## 🛡️ Anti-farming

**Regla crítica:** Un mismo ejercicio NO puede otorgar XP más de una vez por día por alumno.

Implementación:
- ✅ Constraint UNIQUE en base de datos: `(student_id, exercise_id, completed_date)`
- ✅ Validación en lógica de negocio
- ✅ Log de intentos bloqueados

---

## 🧪 Testing

```bash
# Test manual en tinker
php artisan tinker
>>> $student = App\Models\Tenant\Student::first();
>>> $exercise = App\Models\Tenant\Exercise::first();
>>> event(new App\Events\Tenant\ExerciseCompleted($student, $exercise));
>>> php artisan queue:work --once
>>> $service = new App\Services\Tenant\GamificationService();
>>> $service->getStudentStats($student);
```

---

## 📈 Próximas features (no implementadas)

- [ ] Streaks (rachas consecutivas)
- [ ] Achievements (logros especiales)
- [ ] Leaderboards (rankings opcionales)
- [ ] Multiplicadores de XP
- [ ] Recompensas simbólicas
- [ ] UI/UX widgets y animaciones
- [ ] Mobile API endpoints
- [ ] Notificaciones push

---

## 📞 Soporte

Para más información, consulta:
- [Documentación completa](./GAMIFICATION_SYSTEM.md)
- [Guía de inicio rápido](./GAMIFICATION_QUICKSTART.md)
- Código fuente en `app/Models/Tenant/` y `app/Services/Tenant/`

---

**Versión:** 1.0.0  
**Fecha:** 18 de enero, 2026
