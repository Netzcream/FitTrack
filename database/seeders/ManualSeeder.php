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
        $manuals = [
            [
                'title' => 'Configuración de perfil',
                'category' => ManualCategory::CONFIGURATION,
                'summary' => 'Personaliza tu cuenta, branding y contacto paso a paso.',
                'content' => '<h2>Introducción</h2><p>Esta guía te ayudará a configurar tu perfil de Personal Trainer de manera completa.</p><h3>Pasos a seguir:</h3><ol><li>Accede a la sección de Configuración</li><li>Completa tu información personal</li><li>Sube tu foto de perfil</li><li>Configura tus preferencias de notificación</li></ol>',
                'is_active' => true,
                'published_at' => now(),
                'sort_order' => 1,
            ],
            [
                'title' => 'Cómo crear una rutina de entrenamiento',
                'category' => ManualCategory::TRAINING,
                'summary' => 'Aprende a diseñar rutinas efectivas para tus clientes.',
                'content' => '<h2>Diseño de rutinas</h2><p>Una buena rutina debe considerar los objetivos del cliente, su nivel de experiencia y disponibilidad.</p><h3>Elementos clave:</h3><ul><li>Calentamiento adecuado</li><li>Ejercicios progresivos</li><li>Tiempo de descanso</li><li>Enfriamiento y estiramiento</li></ul>',
                'is_active' => true,
                'published_at' => now(),
                'sort_order' => 2,
            ],
            [
                'title' => 'Guía de nutrición básica',
                'category' => ManualCategory::NUTRITION,
                'summary' => 'Conceptos fundamentales de nutrición deportiva.',
                'content' => '<h2>Nutrición deportiva</h2><p>La nutrición es fundamental para alcanzar los objetivos de tus clientes.</p><h3>Macronutrientes:</h3><ul><li><strong>Proteínas:</strong> Construcción muscular</li><li><strong>Carbohidratos:</strong> Energía</li><li><strong>Grasas:</strong> Funciones hormonales</li></ul>',
                'is_active' => true,
                'published_at' => now(),
                'sort_order' => 3,
            ],
            [
                'title' => 'Soporte técnico y contacto',
                'category' => ManualCategory::SUPPORT,
                'summary' => '¿Necesitas ayuda? Conoce los canales de soporte disponibles.',
                'content' => '<h2>Canales de soporte</h2><p>Estamos aquí para ayudarte con cualquier problema o consulta.</p><h3>Opciones de contacto:</h3><ul><li>Email: soporte@fittrack.com</li><li>Chat en vivo: Disponible 9-18hs</li><li>Centro de ayuda: Base de conocimiento online</li></ul>',
                'is_active' => true,
                'published_at' => now(),
                'sort_order' => 4,
            ],
            [
                'title' => 'Primeros pasos en FitTrack',
                'category' => ManualCategory::GENERAL,
                'summary' => 'Guía completa para comenzar a usar la plataforma.',
                'content' => '<h2>Bienvenido a FitTrack</h2><p>Esta guía te ayudará a dar tus primeros pasos en la plataforma.</p><h3>Pasos iniciales:</h3><ol><li>Completa tu perfil</li><li>Agrega tus primeros clientes</li><li>Crea tu primera rutina</li><li>Explora las métricas y reportes</li></ol><p>¡Estás listo para comenzar!</p>',
                'is_active' => true,
                'published_at' => now(),
                'sort_order' => 5,
            ],
        ];

        foreach ($manuals as $manual) {
            Manual::create($manual);
        }
    }
}
