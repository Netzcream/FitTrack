# ✅ Checklist de Integración - Sistema de Gamificación

## Fase 1: Setup inicial (Backend) ⚙️

### Base de datos
- [ ] Ejecutar migraciones tenant
  ```bash
  php artisan tenants:migrate
  ```
- [ ] Verificar que las tablas fueron creadas
  ```bash
  php artisan tinker
  >>> Schema::hasTable('student_gamification_profiles')
  >>> Schema::hasTable('exercise_completion_logs')
  ```

### Verificación de archivos
- [ ] Verificar que el evento está registrado en `EventServiceProvider.php`
- [ ] Verificar que las relaciones existen en `Student.php`
- [ ] Verificar que el servicio `GamificationService` está disponible

---

## Fase 2: Integración en código existente 🔌

### Identificar punto de integración
- [ ] Localizar dónde se marca un ejercicio como completado
  - Controller: `_____________________`
  - Livewire: `_____________________`
  - Servicio: `_____________________`

### Disparar evento
- [ ] Agregar `use App\Events\Tenant\ExerciseCompleted;` al archivo
- [ ] Disparar evento cuando se completa ejercicio:
  ```php
  event(new ExerciseCompleted(
      student: $student,
      exercise: $exercise,
      workout: $workout, // opcional
      completedAt: now() // opcional
  ));
  ```
- [ ] Probar que el evento se dispara correctamente

### Configurar queue
- [ ] Verificar que el queue worker está corriendo
  ```bash
  php artisan queue:work
  ```
- [ ] Probar procesamiento asíncrono
- [ ] (Opcional) Configurar Supervisor para producción

---

## Fase 3: UI/UX - Dashboard del alumno 🎨

### Widget de gamificación
- [ ] Agregar widget en dashboard principal del alumno
  ```blade
  <x-gamification-widget size="large" />
  ```
- [ ] Probar responsive (mobile/desktop)
- [ ] Verificar dark mode

### Badge en perfil
- [ ] Agregar badge en header del perfil
  ```blade
  <x-gamification-badge :student="$student" />
  ```
- [ ] Agregar badge en avatar/card del alumno

### Feedback visual al completar ejercicio
- [ ] Mostrar notificación de XP ganado
- [ ] (Opcional) Animación al completar
- [ ] (Opcional) Confetti al subir de nivel

---

## Fase 4: Testing funcional 🧪

### Test manual básico
- [ ] Crear un alumno de prueba
- [ ] Asignar un plan con ejercicios
- [ ] Completar un ejercicio
- [ ] Verificar que se creó el log en `exercise_completion_logs`
- [ ] Verificar que se creó el perfil en `student_gamification_profiles`
- [ ] Verificar que el XP se sumó correctamente

### Test anti-farming
- [ ] Intentar completar el mismo ejercicio dos veces el mismo día
- [ ] Verificar que NO se otorga XP la segunda vez
- [ ] Verificar log en `storage/logs/laravel.log`
- [ ] Completar el mismo ejercicio al día siguiente
- [ ] Verificar que SÍ se otorga XP

### Test de progresión
- [ ] Completar suficientes ejercicios para subir de nivel
- [ ] Verificar que el nivel se actualiza automáticamente
- [ ] Verificar que el tier cambia cuando corresponde
- [ ] Verificar que el badge se actualiza

---

## Fase 5: Refinamiento 🎯

### Personalización visual
- [ ] Ajustar colores de badges según branding
- [ ] Ajustar iconos/emojis de tiers (si es necesario)
- [ ] Agregar animaciones (opcional)

### Traducciones
- [ ] Verificar traducciones en español
- [ ] Verificar traducciones en inglés
- [ ] (Opcional) Agregar más idiomas

### Performance
- [ ] Verificar queries N+1 en stats
- [ ] Agregar eager loading si es necesario
- [ ] Considerar cache para stats (opcional)

---

## Fase 6: Documentación y training 📚

### Documentación interna
- [ ] Documentar dónde se dispara el evento
- [ ] Documentar cómo mostrar stats en nuevas vistas
- [ ] Agregar ejemplos al README del proyecto

### Training del equipo
- [ ] Explicar sistema a desarrolladores
- [ ] Explicar sistema a diseñadores (para UI)
- [ ] Explicar sistema a stakeholders (feature demo)

---

## Fase 7: Mobile API (si aplica) 📱

### Endpoints
- [ ] Crear endpoint `GET /api/gamification/stats`
- [ ] Crear endpoint `GET /api/gamification/history`
- [ ] Documentar endpoints en `MOBILE_API_DOCUMENTATION.md`

### Testing móvil
- [ ] Probar desde Expo/React Native
- [ ] Verificar formato de respuesta JSON
- [ ] Verificar permisos y autenticación

---

## Fase 8: Monitoreo y optimización 📊

### Logs y métricas
- [ ] Configurar alertas para errores en gamificación
- [ ] Monitorear tiempo de procesamiento de eventos
- [ ] Revisar logs de intentos bloqueados (anti-farming)

### Optimizaciones
- [ ] Revisar performance después de 1 semana
- [ ] Ajustar progresión de niveles si es necesario
- [ ] Ajustar XP por dificultad si es necesario

---

## Fase 9: Features futuras (opcional) 🚀

### Notificaciones
- [ ] Notificación push al subir de nivel
- [ ] Email semanal con resumen de progreso
- [ ] Badge de "¡Nivel up!" en notificaciones

### Streaks (rachas)
- [ ] Implementar contador de días consecutivos
- [ ] Mostrar streak en widget
- [ ] Bonus por mantener streak

### Achievements (logros)
- [ ] Definir lista de logros
- [ ] Implementar sistema de achievements
- [ ] Página de achievements desbloqueados

### Leaderboards (rankings)
- [ ] Implementar rankings opcionales
- [ ] Filtros por período (semanal/mensual)
- [ ] Opt-in para alumnos que quieran participar

---

## Troubleshooting común 🔧

### El evento no se procesa
- [ ] Verificar que queue worker está corriendo
- [ ] Revisar `storage/logs/laravel.log`
- [ ] Verificar configuración de queue en `.env`

### No se otorga XP
- [ ] Verificar que el ejercicio tiene un `level` válido
- [ ] Verificar que no fue completado hoy (anti-farming)
- [ ] Verificar tenancy (¿estás en la BD correcta?)
- [ ] Revisar logs del listener

### Error de unique constraint
- [ ] Es esperado si se intenta completar 2 veces el mismo día
- [ ] Revisar lógica de frontend para prevenir doble submit
- [ ] Verificar que la fecha se calcula correctamente

### Widget no se muestra
- [ ] Verificar que el alumno tiene perfil (o que se crea automáticamente)
- [ ] Verificar helpers en `TenantHelpers.php`
- [ ] Revisar errores en consola del navegador

---

## Sign-off ✍️

### Desarrollo
- [ ] Código revisado y aprobado
- [ ] Tests pasando
- [ ] Sin errores de linter
- [ ] Documentación completa

### QA
- [ ] Testing funcional completado
- [ ] Testing de regresión OK
- [ ] Performance aceptable
- [ ] Mobile OK (si aplica)

### Producto
- [ ] Feature cumple con requisitos
- [ ] UX validada
- [ ] Feedback de usuarios beta positivo
- [ ] Listo para producción

---

## Comandos útiles 🛠️

```bash
# Migrar tablas
php artisan tenants:migrate

# Queue worker
php artisan queue:work

# Ver logs en tiempo real
php artisan pail

# Tinker para testing
php artisan tinker

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Ver tabla de niveles
php artisan tinker
>>> (new \App\Services\Tenant\GamificationService())->getLevelTable(30);

# Crear perfil manual para un alumno
php artisan tinker
>>> $student = \App\Models\Tenant\Student::first();
>>> (new \App\Services\Tenant\GamificationService())->getOrCreateProfile($student);
```

---

## Notas importantes 📝

1. **Anti-farming es crítico**: No modificar el índice único en BD sin consultar
2. **Queue debe estar corriendo**: El listener es asíncrono
3. **Tenancy**: Siempre verificar que estás en el tenant correcto
4. **XP nunca decrece**: Es acumulativo, no punitivo
5. **Los niveles son derivados**: Se calculan desde XP, no se guardan independientemente

---

## Recursos adicionales 📖

- [Documentación completa](./GAMIFICATION_SYSTEM.md)
- [Quick start](./GAMIFICATION_QUICKSTART.md)
- [Ejemplos de código](./examples/)
- [FAQ](./GAMIFICATION_FAQ.md) *(crear si es necesario)*

---

**Última actualización:** 18 de enero, 2026  
**Versión del checklist:** 1.0
