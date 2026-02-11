# FitTrack - Plataforma Web

FitTrack es una plataforma SaaS multi-tenant para entrenadores personales. Permite gestionar alumnos, rutinas, métricas corporales, comunicación, progreso y administración comercial. Esta Web App cubre vistas de Owner, Entrenador y Alumno según sus roles.

---

## Descripción del proyecto

La plataforma ofrece:
- Gestion completa de alumnos.
- Registro, actualizacion y analisis de metricas corporales.
- Creacion y edicion de rutinas manuales.
- Generacion automatica de rutinas mediante API externa.
- Visualizacion de progreso del alumno.
- Administracion de planes comerciales y medios de pago.
- Acceso separado por roles: Owner, Entrenador y Alumno.
- Arquitectura multi-tenant con bases de datos aisladas por entrenador.
- API movil para app React Native / Next.go (20 endpoints).

---

## Requerimientos previos

- PHP 8.3
- Composer 2.x
- Node.js 18+
- MariaDB 10.6+ o MySQL compatible
- Extensiones PHP recomendadas: pdo_mysql, mbstring, openssl, curl, json, xml
- Servidor con soporte HTTPS en produccion

---

## Manual de instalacion (local)

### 1) Clonar el repositorio
```
git clone git@github.com:Netzcream/FitTrack.git
cd FitTrack
```

### 2) Instalar dependencias
```
composer install
npm install
```

### 3) Configurar entorno
```
copy .env.example .env
```

Configura la base central y el dominio principal de tenancy:
```
APP_NAME=FitTrack
APP_ENV=local
APP_URL=http://fittrack.test
APP_DOMAIN=fittrack.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fittrack_central
DB_USERNAME=usuario
DB_PASSWORD=clave

TENANCY_PRIMARY_DOMAIN=fittrack.test
```

### 3.1) Virtual host y subdominios

Necesitas un virtual host apuntando a 127.0.0.1 para el dominio principal y los subdominios.

Apache (ejemplo):
```
<VirtualHost *:80>
  ServerName fittrack.test
  ServerAlias *.fittrack.test
  DocumentRoot "C:/laragon/www/FitTrack/public"

  <Directory "C:/laragon/www/FitTrack/public">
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
```

DNS local / hosts:
- Fittrack principal: mapear `fittrack.test` -> 127.0.0.1
- Subdominios: mapear `*.fittrack.test` -> 127.0.0.1 (requiere DNS local con wildcard) o agregar cada subdominio que uses (ej: `sabrina.fittrack.test`).

### 4) Generar key y enlaces
```
php artisan key:generate
php artisan storage:link
```

### 5) Migraciones
```
php artisan migrate
php artisan tenants:migrate
```

Si necesitas datos de ejemplo:
```
php artisan db:seed
php artisan tenants:seed
```

### 6) Compilar assets
```
npm run build
```

---

## Como correr el proyecto en local

1. Iniciar servidor Laravel
```
php artisan serve
```

2. Iniciar compilacion de assets para desarrollo
```
npm run dev
```

3. Acceder desde navegador
```
http://fittrack.test
```

4. Acceso a tenants
```
http://<tenant>.fittrack.test
```

---

## Cron y queues

### Scheduler (cron)

Linux/macOS:
```
* * * * * cd /ruta/a/FitTrack && php artisan schedule:run >> /dev/null 2>&1
```

Windows (Task Scheduler):
- Accion: `php artisan schedule:run`
- Frecuencia: cada 1 minuto
- Working directory: `C:\laragon\www\FitTrack`

### Queue worker

Con `QUEUE_CONNECTION=database`:
```
php artisan queue:work --tries=3 --timeout=90
```

Si la tabla de jobs no existe:
```
php artisan queue:table
php artisan migrate
```

---

## Variables de entorno necesarias

- Base de datos central y tenancy (`DB_*`, `DB_TENANT_*`, `APP_DOMAIN`, `TENANCY_PRIMARY_DOMAIN`).
- Mail (SMTP):
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=mail@example.com
MAIL_PASSWORD=password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notifications@fittrack.test
MAIL_FROM_NAME="FitTrack"
```
- Google SSO:
```
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://fittrack.test/auth/google/callback
```
- OpenAI (si se usa generacion de rutinas):
```
OPENAI_API_KEY=
OPENAI_ORGANIZATION=
OPENAI_PROJECT=
OPENAI_BASE_URL=https://api.openai.com/v1
```
- Mercado Pago (pagos):
```
MERCADOPAGO_BASE_URL=https://api.mercadopago.com
MERCADOPAGO_RUNTIME_ENV=local
MERCADOPAGO_PUBLIC=
MERCADOPAGO_ACCESS_TOKEN=
```
- Expo push (notificaciones):
```
EXPO_PUSH_ENABLED=false
EXPO_PUSH_ACCESS_TOKEN=
```

---

## Librerias principales utilizadas

- Laravel 12
- Livewire 3
- Tailwind CSS 4
- Flux UI Components
- Spatie Permission
- Stancl Tenancy
- Laravel Sanctum
- MariaDB / MySQL
- Vite

---

## Estructura general del proyecto

```
app/
  Http/
  Models/
  Livewire/
  Tenant/
bootstrap/
config/
database/
public/
resources/
routes/
```

---

## Arquitectura tecnica (resumen)

- Multi-tenant basado en subdominios.
- Bases separadas por tenant administradas dinamicamente.
- Logica compartida en un nucleo central.
- Roles y permisos administrados por Spatie Permission.
- API REST para sincronizacion con la app movil (20 endpoints).

---

## Estado actual del proyecto

La API movil esta completa y documentada. El core web sigue en desarrollo activo con roadmap a MVP 2Q 2026.

---

## Documentacion

Punto de entrada: [documents/INICIO.md](documents/INICIO.md)

Indice general: [documents/DOCUMENTATION_INDEX.md](documents/DOCUMENTATION_INDEX.md)

Documentos clave:
- [documents/FINAL_STATUS.md](documents/FINAL_STATUS.md)
- [documents/API_README.md](documents/API_README.md)
- [documents/MOBILE_API_NEXTGO_COMPLETE.md](documents/MOBILE_API_NEXTGO_COMPLETE.md)
- [documents/NEXTGO_INTEGRATION_CHECKLIST.md](documents/NEXTGO_INTEGRATION_CHECKLIST.md)
- [documents/BRANDING_CONFIG_GUIDE.md](documents/BRANDING_CONFIG_GUIDE.md)

Actualizado: Enero 2026
