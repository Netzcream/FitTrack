# FitTrack — Plataforma Web

FitTrack es un sistema multi-tenant orientado a entrenadores personales. Permite gestionar alumnos, rutinas, métricas corporales, comunicación, progreso y administración comercial. Esta Web App corresponde a la vista del Owner, Entrenador y Alumno según sus roles.

---

## Descripción del proyecto

La plataforma ofrece:
- Gestión completa de alumnos.
- Registro, actualización y análisis de métricas corporales.
- Creación y edición de rutinas manuales.
- Generación automática de rutinas mediante una API externa.
- Visualización de progreso del alumno.
- Administración de planes comerciales y medios de pago.
- Acceso separado por roles: Owner, Entrenador y Alumno.
- Arquitectura multi-tenant con bases de datos aisladas por entrenador.

---

## Requerimientos previos

- PHP 8.3
- Composer 2.x
- Node.js 18+
- MariaDB 10.6+ o MySQL compatible
- Extensiones PHP recomendadas: pdo_mysql, mbstring, openssl, curl, json, xml
- Servidor con soporte HTTPS en producción

---

## Instalación de dependencias

1. Clonar el repositorio  
   `git clone git@github.com:Netzcream/FitTrack.git`
2. Ingresar a la carpeta  
   `cd fittrack-web`
3. Instalar dependencias PHP  
   `composer install`
4. Instalar dependencias frontend  
   `npm install`
5. Compilar assets  
   `npm run build`

---

## Configuración del entorno

1. Copiar el archivo de ejemplo  
   `cp .env.example .env`
2. Configurar base de datos central  
   ```
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=fittrack_central
   DB_USERNAME=usuario
   DB_PASSWORD=clave
   ```
3. Configuración multi-tenant (Stancl Tenancy)  
   Variables según el dominio usado:
   ```
   TENANCY_PRIMARY_DOMAIN=fittrack.test
   ```
4. Generar key de Laravel  
   `php artisan key:generate`
5. Crear link simbólico
   `php artisan storage:link`
6. Ejecutar migraciones  
   `php artisan migrate`

---

## Cómo correr el proyecto en local

1. Iniciar servidor Laravel  
   `php artisan serve`
2. Iniciar compilación de assets para desarrollo  
   `npm run dev`
3. Acceder desde navegador:  
   `http://fittrack.test`
4. Acceso a tenants:  
   `http://<tenant>.fittrack.test`

---

## Variables de entorno necesarias

- Configuración base de datos central  
- Configuración de tenants  
- Credenciales de correo para recuperar contraseña  
  ```
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.example.com
  MAIL_PORT=587
  MAIL_USERNAME=mail@example.com
  MAIL_PASSWORD=password
  MAIL_ENCRYPTION=tls
  ```

---

## Librerías principales utilizadas

- Laravel 12
- Livewire 3
- Tailwind CSS
- Flux UI Components
- Spatie Permission
- Stancl Tenancy
- Laravel Sanctum
- MariaDB / MySQL
- Axios
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

## Arquitectura técnica (resumen)

- Multi-tenant basado en subdominios.
- Bases separadas por tenant administradas dinámicamente.
- Lógica compartida en un núcleo central.
- Roles y permisos administrados por Spatie Permission.
- API REST para sincronización con la app móvil.

---

## Estado actual del proyecto

El proyecto se encuentra aún en etapa de desarrollo; se estima llegar a un MVP para el 2Q 2026.
