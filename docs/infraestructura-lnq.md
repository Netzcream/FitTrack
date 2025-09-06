# Infraestructura del Servidor VPS - luniqo.com

## General

- **Sistema operativo**: Ubuntu 24.04.2 LTS
- **Servidor web**: Apache 2.4
- **PHP**: 8.3
- **Base de datos**: MariaDB
- **Framework**: Laravel 12
- **Tenancy**: Stancl v3 (multi-database)
- **Certificados SSL**: Certbot (Let's Encrypt)

---

## Apache + Laravel

- Dominio principal: `luniqo.com`
- Alias: `www.luniqo.com`
- DocumentRoot: `/var/www/luniqo.com/public`

### .htaccess

- Redirección de `www.luniqo.com` a `luniqo.com`
- Rewrite rules para Laravel
- Rewrite condicionales para limpieza de URL y headers

---

## Laravel Multitenant

- Estructura de base de datos: `lnq_{tenant}`
- Cada tenant tiene dominio tipo `cliente1.luniqo.com`
- Estados del tenant definidos en `TenantStatus` (ej: `ACTIVE`, `DELETED`)
- Rutas:
  - `routes/web.php` → central (no tenancy)
  - `routes/tenant.php` → subdominios (con middleware tenancy)

### 🧠 Importante: Manejo de Sesión en Multi-Tenancy

- **No se debe establecer `SESSION_DOMAIN`** en `.env`.
- Aunque parezca útil para compartir sesión entre subdominios (`.luniqo.com`), **esto rompe el aislamiento que Stancl Tenancy requiere**.
- Laravel guarda variables como `_tenant_id` en la sesión. Si la cookie se comparte entre central y tenant, eso **contamina la sesión del central** y causa que Laravel crea estar en modo tenant cuando no lo está.

**Configuración recomendada:**
```env
SESSION_DRIVER=database
SESSION_CONNECTION=mysql
SESSION_LIFETIME=43800
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
# NOTA: SESSION_DOMAIN debe estar vacío o directamente no definido
```

Con esto:
- Laravel guarda cookies de sesión limitadas al subdominio actual
- Cada tenant tiene su propia sesión separada
- No se producen conflictos entre `Auth`, `Session` ni `tenancy()->initialized`

### Alternativas si se necesita SSO:
- No usar sesiones compartidas: usar JWT o tokens firmados
- Implementar un endpoint de inicio de sesión centralizado que reenvíe cookies firmadas temporales al subdominio

---

## Certificados SSL

### Emisión automática

- Evento: `TenantCreatedSuccessfully`
- Listener: `GenerateSSLCertificateForTenant`
- Job: `GenerateTenantSSLCertificate`
- Ejecuta: `sudo certbot --apache -d cliente1.luniqo.com`

### Permiso sudo (visudo)
```bash
www-data ALL=(ALL) NOPASSWD: /usr/bin/certbot
```

### Validación desde Laravel

Métodos en `App\Models\Tenant`:
- `hasValidSslFor($domain)`
- `sslExpirationDateFor($domain)`
- `sslInfoFor($domain)`

---

## Mail de bienvenida al crear tenant

- Listener: `SendTenantWelcomeMail`
- Job: `SendTenantWelcomeEmail`
- Mail: `TenantWelcomeMail`
- Vista: `resources/views/emails/tenant/welcome.blade.php`
- Destinatario: `admin@{subdominio}`

---

## Mantenimiento automático de certificados SSL

### Comando personalizado
```bash
php artisan ssl:maintain
```
Este comando:
1. Ejecuta `sudo certbot renew`
2. Revisa certificados instalados
3. Elimina certificados de tenants con:
   - `status = DELETED`
   - `updated_at` > 30 días atrás

### Programación en Laravel 11
En `routes/console.php`:
```php
Schedule::command('ssl:maintain')->dailyAt('03:30');
```

### Crontab en servidor
```bash
* * * * * cd /var/www/luniqo.com && php artisan schedule:run >> /dev/null 2>&1
```

---

## Seguridad y buenas prácticas

- Los procesos pesados (mails, certbot) corren como Jobs
- Verificación previa antes de emitir SSL
- Eliminación automática de certificados vencidos y obsoletos
- Logs en `storage/logs/laravel.log`
