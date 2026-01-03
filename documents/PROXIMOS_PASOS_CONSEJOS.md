## 💡 Consejos Útiles

### Para el Backend:
- Usa `.env` para configurar URLs y tokens
- Verifica que CORS permita tu IP local para pruebas
- Usa `php artisan route:list` para ver todas las rutas registradas
- Logs en `storage/logs/laravel.log` si hay errores

### Para el Frontend:
- **Importante:** Cambia `BASE_URL` en `client.js` a tu IP local (no localhost)
- Para obtener tu IP: `ipconfig` en Windows, `ifconfig` en Mac/Linux
- Usa `console.log()` liberalmente para debug
- Expo Go debe estar en la misma red WiFi que tu PC
- Para ver logs: Terminal de Expo muestra todos los console.log

### Troubleshooting Común:

**❌ Error: "Network request failed"**
- Verifica que `BASE_URL` sea tu IP local, no `localhost`
- Verifica que tu teléfono y PC estén en la misma red
- Verifica que el servidor Laravel esté corriendo

**❌ Error: "Tenant ID requerido"**
- Asegúrate de enviar header `X-Tenant-ID`
- Verifica que el tenant_id esté guardado en AsyncStorage

**❌ Error: "Unauthenticated"**
- Token expirado o inválido
- Verifica que el token esté en el header `Authorization: Bearer {token}`

---

## 🚀 Comando Rápido para Empezar HOY

```bash
# 1. Ejecutar migraciones
php artisan tenants:migrate

# 2. Ver lista de tenants (para saber cuáles existen)
php artisan tenants:list

# 3. Iniciar servidor (si no está corriendo)
php artisan serve
# O si usas Laragon, ya está corriendo en http://localhost

# 4. Probar login con Postman/Thunder Client
# POST http://localhost/api/auth/login
# Headers: Content-Type: application/json
# Body: 
# {
#   "email": "usuario@example.com",
#   "password": "password"
# }

# 5. Copiar token y tenant.id de la respuesta

# 6. Probar endpoint protegido
# GET http://localhost/api/profile
# Headers:
#   Authorization: Bearer {token}
#   X-Tenant-ID: {tenant_id}

# Si todo funciona ✅, el backend está listo
```

---

## 🔧 Entendiendo Multi-Tenancy en FitTrack

Tu aplicación usa **multi-tenancy con middleware personalizado** para API:

### ✅ Cómo funciona:

1. **Login** (`/api/auth/login`):
   - NO requiere tenant
   - Usa middleware `universal`
   - Detecta automáticamente el tenant del usuario
   - Retorna el `tenant.id` en la respuesta

2. **Rutas protegidas** (`/api/profile`, `/api/plans`, etc.):
   - Requieren autenticación: `Authorization: Bearer {token}`
   - Requieren identificación de tenant: `X-Tenant-ID: {tenant_id}`
   - El middleware `ApiTenancy` lee el header y activa el tenant correcto
   
### ⚠️ Errores comunes:

**"Tenant ID requerido"**
- Falta el header `X-Tenant-ID`
- Solución: Agregar header con el tenant.id del login

**"Tenant no encontrado"**
- El tenant_id no existe en la base de datos
- Solución: Verificar con `php artisan tenants:list`

**"Unauthenticated"**
- Token inválido o expirado
- Solución: Hacer login nuevamente

---

**🎉 ¡Felicidades!** El backend está 100% implementado.  
**👉 Siguiente paso:** Ejecutar las migraciones y probar la API.  
**🎯 Meta final:** App móvil funcional en Expo Go.
