<?php

namespace Database\Seeders;

use App\Enums\ManualCategory;
use App\Models\Central\Manual;
use Illuminate\Database\Seeder;

class ManualSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $manuals = [
            [
                'title' => 'Primeros pasos en FitTrack',
                'slug' => 'primeros-pasos-fittrack',
                'category' => ManualCategory::GENERAL,
                'summary' => 'Guia rapida para empezar: perfil, alumnos, planes y acceso del alumno.',
                'content' => '<h2>Bienvenido a FitTrack</h2><p>Esta guia resume el flujo basico para dejar el sistema listo.</p><h3>Checklist inicial</h3><ol><li>Completa tu perfil y logo en Configuracion.</li><li>Configura los datos de contacto y redes.</li><li>Carga tus primeros alumnos.</li><li>Crea un plan base y asignalo.</li><li>Verifica el acceso del alumno y su plan activo.</li></ol><p>Cuando termines, tus alumnos ya podran ver su plan y registrar entrenamientos.</p>',
                'is_active' => true,
                'published_at' => $now,
                'sort_order' => 1,
            ],
            [
                'title' => 'Configuracion y branding del sitio',
                'slug' => 'configuracion-y-branding',
                'category' => ManualCategory::CONFIGURATION,
                'summary' => 'Personaliza colores, portada y datos visibles para tus alumnos.',
                'content' => '<h2>Configuracion general</h2><p>En la seccion Configuracion puedes definir identidad visual y datos del sitio.</p><h3>Recomendado</h3><ul><li>Sube un logo cuadrado y una imagen de portada.</li><li>Define color base y color oscuro para botones.</li><li>Configura redes sociales y WhatsApp.</li><li>Completa el texto del footer con tu info de contacto.</li></ul><p>Estos cambios se ven en la landing del tenant y en los mensajes enviados a alumnos.</p>',
                'is_active' => true,
                'published_at' => $now,
                'sort_order' => 2,
            ],
            [
                'title' => 'Gestion de alumnos y accesos',
                'slug' => 'gestion-de-alumnos-y-accesos',
                'category' => ManualCategory::GENERAL,
                'summary' => 'Como cargar alumnos, invitarlos y controlar su acceso.',
                'content' => '<h2>Alta de alumnos</h2><p>Puedes cargar alumnos manualmente o invitarlos con un enlace de activacion.</p><h3>Buenas practicas</h3><ul><li>Verifica email y telefono antes de crear el usuario.</li><li>Activa el acceso cuando el alumno este listo.</li><li>Usa etiquetas o notas internas para segmentar.</li></ul><p>El alumno recibira un email para definir su clave y acceder al area alumno.</p>',
                'is_active' => true,
                'published_at' => $now,
                'sort_order' => 3,
            ],
            [
                'title' => 'Planes, rutinas y asignaciones',
                'slug' => 'planes-rutinas-y-asignaciones',
                'category' => ManualCategory::TRAINING,
                'summary' => 'Crea planes, asignalos y controla la vigencia.',
                'content' => '<h2>Planificar entrenamientos</h2><p>Los planes se crean una sola vez y se asignan a cada alumno.</p><h3>Flujo recomendado</h3><ol><li>Crea un plan base con ejercicios y dias.</li><li>Asigna el plan al alumno con fecha de inicio y fin.</li><li>Revisa que solo haya una asignacion activa.</li><li>Actualiza el plan usando nuevas versiones.</li></ol><p>Al asignar, FitTrack guarda una copia del plan para que no cambie si editas el original.</p>',
                'is_active' => true,
                'published_at' => $now,
                'sort_order' => 4,
            ],
            [
                'title' => 'Seguimiento y metricas del alumno',
                'slug' => 'seguimiento-y-metricas',
                'category' => ManualCategory::GENERAL,
                'summary' => 'Registros de entrenamientos, progreso y reportes.',
                'content' => '<h2>Seguimiento</h2><p>El alumno registra entrenamientos y progresos desde su area o la app.</p><h3>Que revisar</h3><ul><li>Entrenamientos completados por semana.</li><li>Comentarios del alumno por sesion.</li><li>Cambios en medidas y objetivos.</li></ul><p>Estos datos ayudan a ajustar el plan y mejorar la adherencia.</p>',
                'is_active' => true,
                'published_at' => $now,
                'sort_order' => 5,
            ],
            [
                'title' => 'Pagos y facturacion',
                'slug' => 'pagos-y-facturacion',
                'category' => ManualCategory::GENERAL,
                'summary' => 'Gestiona invoices, estados de pago y recordatorios.',
                'content' => '<h2>Pagos</h2><p>FitTrack permite registrar invoices y pagos para cada alumno.</p><h3>Recomendaciones</h3><ul><li>Define planes comerciales con montos claros.</li><li>Usa vencimientos para ordenar cobros.</li><li>Revisa el estado de pago en la ficha del alumno.</li></ul><p>El alumno recibe notificaciones cuando hay nuevos invoices o pagos aplicados.</p>',
                'is_active' => true,
                'published_at' => $now,
                'sort_order' => 6,
            ],
            [
                'title' => 'Comunicacion y notificaciones',
                'slug' => 'comunicacion-y-notificaciones',
                'category' => ManualCategory::SUPPORT,
                'summary' => 'Mensajes, recordatorios y emails automatizados.',
                'content' => '<h2>Mensajeria</h2><p>Puedes enviar mensajes directos y activar recordatorios automaticos.</p><h3>Casos comunes</h3><ul><li>Recordar sesiones pendientes.</li><li>Enviar bienvenida al alumno.</li><li>Notificar cambios de plan.</li></ul><p>Revisa el canal de notificaciones en Configuracion si necesitas ajustar el tono o el remitente.</p>',
                'is_active' => true,
                'published_at' => $now,
                'sort_order' => 7,
            ],
            [
                'title' => 'App Android para alumnos',
                'slug' => 'app-android-alumnos',
                'category' => ManualCategory::GENERAL,
                'summary' => 'Descarga, acceso y funciones principales para alumnos.',
                'content' => '<h2>Descargar la app</h2><p>Los alumnos pueden instalar la app Android desde este enlace:</p><p><a href="https://repository.netzcream.com.ar/fittrack/FitTrack.apk">Descargar FitTrack APK</a></p><h3>Funciones clave</h3><ul><li>Ver plan activo y sesiones del dia.</li><li>Registrar entrenamientos y comentarios.</li><li>Consultar mensajes del entrenador.</li></ul><p>La app usa las mismas credenciales del area alumno.</p>',
                'is_active' => true,
                'published_at' => $now,
                'sort_order' => 8,
            ],
            [
                'title' => 'Soporte tecnico y contacto',
                'slug' => 'soporte-tecnico-y-contacto',
                'category' => ManualCategory::SUPPORT,
                'summary' => 'Canales de ayuda para entrenadores y alumnos.',
                'content' => '<h2>Soporte</h2><p>Si necesitas ayuda, estos son los canales recomendados.</p><h3>Opciones de contacto</h3><ul><li>Chat de soporte dentro del dashboard.</li><li>Formulario de contacto en el sitio.</li><li>Correo de soporte del equipo FitTrack.</li></ul><p>Incluye capturas y detalles del problema para acelerar la respuesta.</p>',
                'is_active' => true,
                'published_at' => $now,
                'sort_order' => 9,
            ],
        ];

        foreach ($manuals as $manual) {
            Manual::updateOrCreate(
                ['slug' => $manual['slug']],
                $manual
            );
        }
    }
}
