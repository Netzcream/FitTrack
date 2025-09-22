<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\Tenant\Exercise\Exercise;
use App\Models\Tenant\Exercise\ExerciseLevel;
use App\Models\Tenant\Exercise\MovementPattern;
use App\Models\Tenant\Exercise\ExercisePlane;
use App\Models\Tenant\Exercise\MuscleGroup;
use App\Models\Tenant\Exercise\Muscle;
use App\Models\Tenant\Exercise\Equipment;

class MasterExercisesCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ---------------------------------------------------------------------
        // 1) UPsert: Exercise Levels
        // ---------------------------------------------------------------------
        $levels = [
            [
                'name' => 'Principiante',
                'code' => 'beginner',
                'description' => 'Ejercicios básicos para quienes inician su camino fitness.',
                'order' => 1,
            ],
            [
                'name' => 'Intermedio',
                'code' => 'intermediate',
                'description' => 'Ejercicios para quienes ya tienen experiencia y buscan un reto mayor.',
                'order' => 2,
            ],
            [
                'name' => 'Avanzado',
                'code' => 'advanced',
                'description' => 'Ejercicios desafiantes para atletas experimentados.',
                'order' => 3,
            ],
        ];

        foreach ($levels as $l) {
            ExerciseLevel::updateOrCreate(
                ['code' => $l['code']],
                [
                    'uuid' => ExerciseLevel::where('code', $l['code'])->value('uuid') ?: (string) Str::orderedUuid(),
                    'name' => $l['name'],
                    'description' => $l['description'],
                    'order' => $l['order'],
                    'updated_at' => $now,
                    'created_at' => ExerciseLevel::where('code', $l['code'])->exists() ? ExerciseLevel::where('code', $l['code'])->value('created_at') : $now,
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 2) UPsert: Movement Patterns
        // ---------------------------------------------------------------------
        $patterns = [
            ['name' => 'Sentadilla', 'code' => 'squat', 'description' => 'Patrón básico de flexión y extensión de rodillas/cadera.', 'order' => 1],
            ['name' => 'Bisagra de cadera', 'code' => 'hinge', 'description' => 'Patrón dominante de cadera (peso muerto, hip thrust).', 'order' => 2],
            ['name' => 'Zancada', 'code' => 'lunge', 'description' => 'Patrón unilateral de pierna (lunge, split squat).', 'order' => 3],
            ['name' => 'Empuje', 'code' => 'push', 'description' => 'Patrón de empuje (horizontal/vertical).', 'order' => 4],
            ['name' => 'Tracción', 'code' => 'pull', 'description' => 'Patrón de tracción (horizontal/vertical).', 'order' => 5],
            ['name' => 'Porteo / Levante', 'code' => 'carry', 'description' => 'Patrón de carga y transporte (farmer walk, suitcase carry).', 'order' => 6],
            ['name' => 'Rotación', 'code' => 'rotation', 'description' => 'Patrón rotacional del core (twists, lanzamientos).', 'order' => 7],
            ['name' => 'Antirotación', 'code' => 'anti_rotation', 'description' => 'Patrón de resistencia al movimiento rotacional (Pallof press).', 'order' => 8],
        ];
        foreach ($patterns as $p) {
            MovementPattern::updateOrCreate(
                ['code' => $p['code']],
                [
                    'uuid' => MovementPattern::where('code', $p['code'])->value('uuid') ?: (string) Str::orderedUuid(),
                    'name' => $p['name'],
                    'description' => $p['description'],
                    'order' => $p['order'],
                    'updated_at' => $now,
                    'created_at' => MovementPattern::where('code', $p['code'])->exists() ? MovementPattern::where('code', $p['code'])->value('created_at') : $now,
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 3) UPsert: Exercise Planes
        // ---------------------------------------------------------------------
        $planes = [
            ['name' => 'Sagital', 'code' => 'sagittal', 'description' => 'Movimientos hacia adelante y hacia atrás (flexión, extensión).', 'order' => 1],
            ['name' => 'Frontal', 'code' => 'frontal', 'description' => 'Movimientos laterales (abducción, aducción).', 'order' => 2],
            ['name' => 'Transversal', 'code' => 'transverse', 'description' => 'Movimientos de rotación (giros, rotaciones internas/externas).', 'order' => 3],
            ['name' => 'Multiplanar', 'code' => 'multi', 'description' => 'Movimientos que combinan más de un plano (ej. zancadas con rotación).', 'order' => 4],
        ];
        foreach ($planes as $pl) {
            ExercisePlane::updateOrCreate(
                ['code' => $pl['code']],
                [
                    'uuid' => ExercisePlane::where('code', $pl['code'])->value('uuid') ?: (string) Str::orderedUuid(),
                    'name' => $pl['name'],
                    'description' => $pl['description'],
                    'order' => $pl['order'],
                    'updated_at' => $now,
                    'created_at' => ExercisePlane::where('code', $pl['code'])->exists() ? ExercisePlane::where('code', $pl['code'])->value('created_at') : $now,
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 4) UPsert: Muscle Groups (y mapa code => id)
        // ---------------------------------------------------------------------
        $mg = [
            ['name' => 'Pecho', 'code' => 'chest', 'parent_id' => null, 'description' => 'Grupo muscular del tórax (empuje horizontal/vertical).', 'order' => 1],
            ['name' => 'Espalda', 'code' => 'back', 'parent_id' => null, 'description' => 'Tracción horizontal/vertical, estabilización de columna.', 'order' => 2],
            ['name' => 'Hombros', 'code' => 'shoulders', 'parent_id' => null, 'description' => 'Deltoides (anterior, medio, posterior).', 'order' => 3],
            ['name' => 'Brazos', 'code' => 'arms', 'parent_id' => null, 'description' => 'Bíceps, tríceps y antebrazos.', 'order' => 4],
            ['name' => 'Core', 'code' => 'core', 'parent_id' => null, 'description' => 'Abdominales, oblicuos, transverso y erectores.', 'order' => 5],
            ['name' => 'Glúteos', 'code' => 'glutes', 'parent_id' => null, 'description' => 'Glúteo mayor, medio y menor.', 'order' => 6],
            ['name' => 'Cuádriceps', 'code' => 'quadriceps', 'parent_id' => null, 'description' => 'Recto femoral y vastos.', 'order' => 7],
            ['name' => 'Isquiotibiales', 'code' => 'hamstrings', 'parent_id' => null, 'description' => 'Semitendinoso, semimembranoso y bíceps femoral.', 'order' => 8],
            ['name' => 'Pantorrillas', 'code' => 'calves', 'parent_id' => null, 'description' => 'Gastrocnemio y sóleo.', 'order' => 9],
            ['name' => 'Aductores', 'code' => 'adductors', 'parent_id' => null, 'description' => 'Aductor mayor, largo, corto, pectíneo, gracilis.', 'order' => 10],
            ['name' => 'Abductores / Cadera lateral', 'code' => 'abductors', 'parent_id' => null, 'description' => 'TFL y estabilizadores laterales de cadera.', 'order' => 11],
            ['name' => 'Trapecios', 'code' => 'traps', 'parent_id' => null, 'description' => 'Porción superior, media e inferior del trapecio.', 'order' => 12],
            ['name' => 'Antebrazos', 'code' => 'forearms', 'parent_id' => null, 'description' => 'Flexores y extensores del antebrazo.', 'order' => 13],
        ];

        foreach ($mg as $g) {
            MuscleGroup::updateOrCreate(
                ['code' => $g['code']],
                [
                    'uuid' => MuscleGroup::where('code', $g['code'])->value('uuid') ?: (string) Str::orderedUuid(),
                    'name' => $g['name'],
                    'parent_id' => $g['parent_id'],
                    'description' => $g['description'] ?? null,
                    'order' => $g['order'] ?? null,
                    'status' => 'published',
                    'meta' => null,
                    'updated_at' => $now,
                    'created_at' => MuscleGroup::where('code', $g['code'])->exists() ? MuscleGroup::where('code', $g['code'])->value('created_at') : $now,
                ]
            );
        }

        // Mapa code => id (por si cambió algo)
        $groupIds = MuscleGroup::pluck('id', 'code')->toArray();

        // ---------------------------------------------------------------------
        // 5) UPsert: Muscles (usando $groupIds por code)
        // ---------------------------------------------------------------------
        $muscles = [
            // Pecho
            ['name' => 'Pectoral mayor', 'code' => 'pectoralis_major', 'muscle_group_code' => 'chest', 'order' => 1, 'description' => null],
            ['name' => 'Pectoral menor', 'code' => 'pectoralis_minor', 'muscle_group_code' => 'chest', 'order' => 2, 'description' => null],
            // Espalda
            ['name' => 'Dorsal ancho', 'code' => 'latissimus_dorsi', 'muscle_group_code' => 'back', 'order' => 1, 'description' => null],
            ['name' => 'Romboides', 'code' => 'rhomboids', 'muscle_group_code' => 'back', 'order' => 2, 'description' => null],
            ['name' => 'Erectores espinales', 'code' => 'erector_spinae', 'muscle_group_code' => 'back', 'order' => 3, 'description' => null],
            // Hombros
            ['name' => 'Deltoides anterior', 'code' => 'anterior_deltoid', 'muscle_group_code' => 'shoulders', 'order' => 1, 'description' => null],
            ['name' => 'Deltoides medio', 'code' => 'lateral_deltoid', 'muscle_group_code' => 'shoulders', 'order' => 2, 'description' => null],
            ['name' => 'Deltoides posterior', 'code' => 'posterior_deltoid', 'muscle_group_code' => 'shoulders', 'order' => 3, 'description' => null],
            ['name' => 'Manguito rotador', 'code' => 'rotator_cuff', 'muscle_group_code' => 'shoulders', 'order' => 4, 'description' => 'Supraespinoso, infraespinoso, redondo menor, subescapular.'],
            // Brazos
            ['name' => 'Bíceps braquial', 'code' => 'biceps_brachii', 'muscle_group_code' => 'arms', 'order' => 1, 'description' => null],
            ['name' => 'Braquial anterior', 'code' => 'brachialis', 'muscle_group_code' => 'arms', 'order' => 2, 'description' => null],
            ['name' => 'Tríceps braquial', 'code' => 'triceps_brachii', 'muscle_group_code' => 'arms', 'order' => 3, 'description' => null],
            // Core
            ['name' => 'Recto abdominal', 'code' => 'rectus_abdominis', 'muscle_group_code' => 'core', 'order' => 1, 'description' => null],
            ['name' => 'Oblicuos externos', 'code' => 'external_obliques', 'muscle_group_code' => 'core', 'order' => 2, 'description' => null],
            ['name' => 'Oblicuos internos', 'code' => 'internal_obliques', 'muscle_group_code' => 'core', 'order' => 3, 'description' => null],
            ['name' => 'Transverso del abdomen', 'code' => 'transversus_abdominis', 'muscle_group_code' => 'core', 'order' => 4, 'description' => null],
            // Glúteos
            ['name' => 'Glúteo mayor', 'code' => 'gluteus_maximus', 'muscle_group_code' => 'glutes', 'order' => 1, 'description' => null],
            ['name' => 'Glúteo medio', 'code' => 'gluteus_medius', 'muscle_group_code' => 'glutes', 'order' => 2, 'description' => null],
            ['name' => 'Glúteo menor', 'code' => 'gluteus_minimus', 'muscle_group_code' => 'glutes', 'order' => 3, 'description' => null],
            // Cuádriceps
            ['name' => 'Recto femoral', 'code' => 'rectus_femoris', 'muscle_group_code' => 'quadriceps', 'order' => 1, 'description' => null],
            ['name' => 'Vasto lateral', 'code' => 'vastus_lateralis', 'muscle_group_code' => 'quadriceps', 'order' => 2, 'description' => null],
            ['name' => 'Vasto medial', 'code' => 'vastus_medialis', 'muscle_group_code' => 'quadriceps', 'order' => 3, 'description' => null],
            ['name' => 'Vasto intermedio', 'code' => 'vastus_intermedius', 'muscle_group_code' => 'quadriceps', 'order' => 4, 'description' => null],
            // Isquios
            ['name' => 'Bíceps femoral', 'code' => 'biceps_femoris', 'muscle_group_code' => 'hamstrings', 'order' => 1, 'description' => null],
            ['name' => 'Semitendinoso', 'code' => 'semitendinosus', 'muscle_group_code' => 'hamstrings', 'order' => 2, 'description' => null],
            ['name' => 'Semimembranoso', 'code' => 'semimembranosus', 'muscle_group_code' => 'hamstrings', 'order' => 3, 'description' => null],
            // Pantorrillas
            ['name' => 'Gastrocnemio', 'code' => 'gastrocnemius', 'muscle_group_code' => 'calves', 'order' => 1, 'description' => null],
            ['name' => 'Sóleo', 'code' => 'soleus', 'muscle_group_code' => 'calves', 'order' => 2, 'description' => null],
            // Aductores
            ['name' => 'Aductor mayor', 'code' => 'adductor_magnus', 'muscle_group_code' => 'adductors', 'order' => 1, 'description' => null],
            ['name' => 'Aductor largo', 'code' => 'adductor_longus', 'muscle_group_code' => 'adductors', 'order' => 2, 'description' => null],
            // Abductores
            ['name' => 'Tensor de la fascia lata', 'code' => 'tensor_fasciae_latae', 'muscle_group_code' => 'abductors', 'order' => 1, 'description' => null],
            // Trapecios
            ['name' => 'Trapecio', 'code' => 'trapezius', 'muscle_group_code' => 'traps', 'order' => 1, 'description' => 'Porción superior, media e inferior.'],
            // Antebrazos
            ['name' => 'Flexores del antebrazo', 'code' => 'forearm_flexors', 'muscle_group_code' => 'forearms', 'order' => 1, 'description' => null],
            ['name' => 'Extensores del antebrazo', 'code' => 'forearm_extensors', 'muscle_group_code' => 'forearms', 'order' => 2, 'description' => null],
            // Extras
            ['name' => 'Psoas-ilíaco (flexor de cadera)', 'code' => 'iliopsoas', 'muscle_group_code' => 'abductors', 'order' => 99, 'description' => null],
            ['name' => 'Tibial anterior', 'code' => 'tibialis_anterior', 'muscle_group_code' => 'calves', 'order' => 99, 'description' => null],
        ];

        foreach ($muscles as $m) {
            $groupId = $groupIds[$m['muscle_group_code']] ?? MuscleGroup::where('code', $m['muscle_group_code'])->value('id');
            Muscle::updateOrCreate(
                ['code' => $m['code']],
                [
                    'uuid' => Muscle::where('code', $m['code'])->value('uuid') ?: (string) Str::orderedUuid(),
                    'name' => $m['name'],
                    'muscle_group_id' => $groupId,
                    'description' => $m['description'] ?? null,
                    'order' => $m['order'] ?? null,
                    'status' => 'published',
                    'meta' => null,
                    'updated_at' => $now,
                    'created_at' => Muscle::where('code', $m['code'])->exists() ? Muscle::where('code', $m['code'])->value('created_at') : $now,
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 6) UPsert: Equipment
        // ---------------------------------------------------------------------
        $equipment = [
            ['name' => 'Peso corporal', 'code' => 'bodyweight', 'is_machine' => false, 'description' => 'Ejercicios sin implementos.', 'order' => 1],
            ['name' => 'Barra', 'code' => 'barbell', 'is_machine' => false, 'description' => 'Barra olímpica/estándar.', 'order' => 2],
            ['name' => 'Mancuerna', 'code' => 'dumbbell', 'is_machine' => false, 'description' => 'Par o mancuerna individual.', 'order' => 3],
            ['name' => 'Kettlebell', 'code' => 'kettlebell', 'is_machine' => false, 'description' => 'Pesa rusa.', 'order' => 4],
            ['name' => 'Barra EZ', 'code' => 'ez_bar', 'is_machine' => false, 'description' => 'Barra curvada para curl y extensiones.', 'order' => 5],
            ['name' => 'Barra hexagonal', 'code' => 'trap_bar', 'is_machine' => false, 'description' => 'Hex bar / trap bar.', 'order' => 6],

            // Máquinas
            ['name' => 'Máquina de poleas', 'code' => 'cable_machine', 'is_machine' => true, 'description' => 'Estación de poleas ajustables.', 'order' => 10],
            ['name' => 'Máquina guiada (Smith)', 'code' => 'smith_machine', 'is_machine' => true, 'description' => 'Barra guiada en rieles.', 'order' => 11],
            ['name' => 'Máquina de placas', 'code' => 'plate_loaded_machine', 'is_machine' => true, 'description' => 'Máquina cargada con discos.', 'order' => 12],
            ['name' => 'Prensa de piernas', 'code' => 'leg_press_machine', 'is_machine' => true, 'description' => 'Leg press horizontal/45°.', 'order' => 13],
            ['name' => 'Extensión de cuádriceps', 'code' => 'leg_extension_machine', 'is_machine' => true, 'description' => 'Máquina de extensión de rodilla.', 'order' => 14],
            ['name' => 'Curl femoral', 'code' => 'leg_curl_machine', 'is_machine' => true, 'description' => 'Máquina de flexión de rodilla.', 'order' => 15],
            ['name' => 'Jalón al pecho', 'code' => 'lat_pulldown_machine', 'is_machine' => true, 'description' => 'Máquina de jalón dorsales.', 'order' => 16],
            ['name' => 'Remo sentado', 'code' => 'seated_row_machine', 'is_machine' => true, 'description' => 'Máquina de remo con cable.', 'order' => 17],

            // Accesorios / libres
            ['name' => 'Banda elástica', 'code' => 'resistance_band', 'is_machine' => false, 'description' => 'Banda o miniband.', 'order' => 20],
            ['name' => 'TRX / Suspensión', 'code' => 'suspension_trainer', 'is_machine' => false, 'description' => 'Entrenador en suspensión.', 'order' => 21],
            ['name' => 'Banco', 'code' => 'bench', 'is_machine' => false, 'description' => 'Banco plano/inclinado/declinado.', 'order' => 22],
            ['name' => 'Cajón pliométrico', 'code' => 'plyo_box', 'is_machine' => false, 'description' => 'Box/step para saltos y apoyos.', 'order' => 23],
            ['name' => 'Balón medicinal', 'code' => 'medicine_ball', 'is_machine' => false, 'description' => 'Med ball / slam ball.', 'order' => 24],
            ['name' => 'Pelota suiza', 'code' => 'swiss_ball', 'is_machine' => false, 'description' => 'Fitball / estabilidad.', 'order' => 25],
            ['name' => 'Trineo', 'code' => 'sled', 'is_machine' => false, 'description' => 'Sled empuje/arrastre.', 'order' => 26],
            ['name' => 'Landmine', 'code' => 'landmine', 'is_machine' => false, 'description' => 'Pivote para barra.', 'order' => 27],

            // Cardio
            ['name' => 'Cinta de correr', 'code' => 'treadmill', 'is_machine' => true, 'description' => 'Caminadora / treadmill.', 'order' => 30],
            ['name' => 'Bicicleta', 'code' => 'exercise_bike', 'is_machine' => true, 'description' => 'Spinning/air bike.', 'order' => 31],
            ['name' => 'Rower', 'code' => 'rower', 'is_machine' => true, 'description' => 'Remo ergométrico.', 'order' => 32],
            ['name' => 'Elíptico', 'code' => 'elliptical', 'is_machine' => true, 'description' => 'Máquina elíptica.', 'order' => 33],

            // Otros
            ['name' => 'Discos', 'code' => 'weight_plate', 'is_machine' => false, 'description' => 'Placas/discos de peso.', 'order' => 40],
            ['name' => 'Saco de arena', 'code' => 'sandbag', 'is_machine' => false, 'description' => 'Sandbag de entrenamiento.', 'order' => 41],
        ];

        foreach ($equipment as $e) {
            Equipment::updateOrCreate(
                ['code' => $e['code']],
                [
                    'uuid' => Equipment::where('code', $e['code'])->value('uuid') ?: (string) Str::orderedUuid(),
                    'name' => $e['name'],
                    'is_machine' => (bool) $e['is_machine'],
                    'description' => $e['description'] ?? null,
                    'order' => $e['order'] ?? null,
                    'status' => 'published',
                    'meta' => null,
                    'updated_at' => $now,
                    'created_at' => Equipment::where('code', $e['code'])->exists() ? Equipment::where('code', $e['code'])->value('created_at') : $now,
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 7) UPsert: Exercises (con pivots)  — dataset amplio
        // ---------------------------------------------------------------------
        $levelId   = fn(string $code) => ExerciseLevel::where('code', $code)->value('id');
        $patternId = fn(string $code) => MovementPattern::where('code', $code)->value('id');
        $planeId   = fn(string $code) => ExercisePlane::where('code', $code)->value('id');
        $muscleId  = fn(string $code) => Muscle::where('code', $code)->value('id');
        $equipId   = fn(string $code) => Equipment::where('code', $code)->value('id');

        $PRI = 'primary'; $SEC = 'secondary'; $STB = 'stabilizer';
        $REPS = Exercise::MOD_REPS; $TIME = Exercise::MOD_TIME; $DIST = Exercise::MOD_DISTANCE;
        $CAL = Exercise::MOD_CALORIES; $RPE = Exercise::MOD_RPE; $LOAD_ONLY = Exercise::MOD_LOAD_ONLY; $TEMPO = Exercise::MOD_TEMPO_ONLY;
        $PUB = Exercise::STATUS_PUBLISHED;

        $items = [
            // (Mismo dataset que te armé antes – recortado aquí por espacio en este comentario)
            // Copiado íntegro: sentadillas, hinge, lunges, push/pull, core, carry, swing, extensiones, prensa, gemelos, etc.
            // === PEGADO ÍNTEGRO: INICIO ===
            [
                'name' => 'Sentadilla con peso corporal',
                'code' => 'bodyweight_squat',
                'level_code' => 'beginner',
                'pattern_code' => 'squat',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => false,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3x10-15reps'],
                'tempo_notation' => '3-1-1-0',
                'range_of_motion_notes' => 'Cadera bajo paralelo si movilidad lo permite',
                'equipment_notes' => '—',
                'setup_steps' => ['Pies a ancho de hombros', 'Brace de core', 'Descender con control'],
                'execution_cues' => ['Rodillas siguen la punta de pies', 'Mantener pecho arriba'],
                'common_mistakes' => ['Valgo de rodillas', 'Colapsar tronco'],
                'breathing' => 'Inhalar al bajar, exhalar al subir',
                'safety_notes' => 'Detener si hay dolor de rodilla',
                'status' => $PUB,
                'muscles' => [
                    ['quadriceps',   $PRI, 50],
                    ['gluteus_maximus', $PRI, 30],
                    ['erector_spinae',  $STB, 10],
                    ['hamstrings',      $SEC, 10],
                ],
                'equipment' => [
                    ['bodyweight', false],
                ],
            ],
            [
                'name' => 'Sentadilla goblet (kettlebell)',
                'code' => 'goblet_squat',
                'level_code' => 'beginner',
                'pattern_code' => 'squat',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3-4x8-12reps'],
                'tempo_notation' => '3-1-1-0',
                'equipment_notes' => 'Kettlebell al pecho',
                'status' => $PUB,
                'muscles' => [
                    ['quadriceps', $PRI, 45],
                    ['gluteus_maximus', $PRI, 35],
                    ['erector_spinae', $STB, 10],
                    ['gluteus_medius', $STB, 10],
                ],
                'equipment' => [
                    ['kettlebell', true],
                ],
            ],
            [
                'name' => 'Back squat (barra)',
                'code' => 'back_squat',
                'level_code' => 'intermediate',
                'pattern_code' => 'squat',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $LOAD_ONLY,
                'default_prescription' => ['notes' => '5x5 (novatos) o 3x3-5 (fuerza)'],
                'tempo_notation' => '3-0-1-0',
                'status' => $PUB,
                'muscles' => [
                    ['quadriceps', $PRI, 45],
                    ['gluteus_maximus', $PRI, 35],
                    ['erector_spinae', $STB, 15],
                    ['hamstrings', $SEC, 5],
                ],
                'equipment' => [
                    ['barbell', true],
                    ['bench',   false],
                ],
            ],
            [
                'name' => 'Front squat (barra)',
                'code' => 'front_squat',
                'level_code' => 'intermediate',
                'pattern_code' => 'squat',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $LOAD_ONLY,
                'default_prescription' => ['notes' => '4x4-6reps'],
                'tempo_notation' => '3-1-1-0',
                'status' => $PUB,
                'muscles' => [
                    ['quadriceps', $PRI, 50],
                    ['gluteus_maximus', $PRI, 30],
                    ['erector_spinae', $STB, 10],
                    ['core', $STB, 10],
                ],
                'equipment' => [
                    ['barbell', true],
                ],
            ],
            [
                'name' => 'Peso muerto convencional',
                'code' => 'deadlift',
                'level_code' => 'intermediate',
                'pattern_code' => 'hinge',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $LOAD_ONLY,
                'default_prescription' => ['notes' => '5x3-5 (fuerza)'],
                'status' => $PUB,
                'muscles' => [
                    ['gluteus_maximus', $PRI, 40],
                    ['hamstrings', $PRI, 35],
                    ['erector_spinae', $PRI, 15],
                    ['trapezius', $SEC, 10],
                ],
                'equipment' => [
                    ['barbell', true],
                ],
            ],
            [
                'name' => 'Romanian deadlift (RDL) con mancuernas',
                'code' => 'rdl_dumbbell',
                'level_code' => 'intermediate',
                'pattern_code' => 'hinge',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3-4x8-10'],
                'status' => $PUB,
                'muscles' => [
                    ['hamstrings', $PRI, 45],
                    ['gluteus_maximus', $PRI, 35],
                    ['erector_spinae', $SEC, 20],
                ],
                'equipment' => [
                    ['dumbbell', true],
                ],
            ],
            [
                'name' => 'Hip thrust (barra)',
                'code' => 'barbell_hip_thrust',
                'level_code' => 'intermediate',
                'pattern_code' => 'hinge',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '4x8-12'],
                'status' => $PUB,
                'muscles' => [
                    ['gluteus_maximus', $PRI, 60],
                    ['hamstrings', $SEC, 20],
                    ['quadriceps', $SEC, 10],
                    ['erector_spinae', $STB, 10],
                ],
                'equipment' => [
                    ['barbell', true],
                    ['bench',   true],
                    ['weight_plate', false],
                ],
            ],
            [
                'name' => 'Zancada caminando (mancuernas)',
                'code' => 'walking_lunge_db',
                'level_code' => 'intermediate',
                'pattern_code' => 'lunge',
                'plane_code' => 'multi',
                'unilateral' => true,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3x8-12 por pierna'],
                'status' => $PUB,
                'muscles' => [
                    ['quadriceps', $PRI, 40],
                    ['gluteus_maximus', $PRI, 30],
                    ['gluteus_medius', $STB, 15],
                    ['hamstrings', $SEC, 15],
                ],
                'equipment' => [
                    ['dumbbell', true],
                ],
            ],
            [
                'name' => 'Sentadilla búlgara (mancuernas)',
                'code' => 'bulgarian_split_squat',
                'level_code' => 'intermediate',
                'pattern_code' => 'lunge',
                'plane_code' => 'sagittal',
                'unilateral' => true,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3-4x8-10 por pierna'],
                'status' => $PUB,
                'muscles' => [
                    ['quadriceps', $PRI, 45],
                    ['gluteus_maximus', $PRI, 35],
                    ['gluteus_medius', $STB, 10],
                    ['hamstrings', $SEC, 10],
                ],
                'equipment' => [
                    ['dumbbell', false],
                    ['bench', true],
                ],
            ],
            [
                'name' => 'Flexiones de brazos (push-up)',
                'code' => 'push_up',
                'level_code' => 'beginner',
                'pattern_code' => 'push',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => false,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3xAMRAP o 3x10-15'],
                'status' => $PUB,
                'muscles' => [
                    ['pectoralis_major', $PRI, 50],
                    ['anterior_deltoid', $SEC, 20],
                    ['triceps_brachii',  $SEC, 30],
                ],
                'equipment' => [
                    ['bodyweight', false],
                    ['bench', false],
                ],
            ],
            [
                'name' => 'Press banca (barra)',
                'code' => 'barbell_bench_press',
                'level_code' => 'intermediate',
                'pattern_code' => 'push',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $LOAD_ONLY,
                'default_prescription' => ['notes' => '5x5 / 4x6-8'],
                'status' => $PUB,
                'muscles' => [
                    ['pectoralis_major', $PRI, 50],
                    ['anterior_deltoid', $SEC, 20],
                    ['triceps_brachii',  $SEC, 30],
                ],
                'equipment' => [
                    ['barbell', true],
                    ['bench', true],
                    ['weight_plate', true],
                ],
            ],
            [
                'name' => 'Press militar (mancuernas)',
                'code' => 'db_overhead_press',
                'level_code' => 'intermediate',
                'pattern_code' => 'push',
                'plane_code' => 'frontal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3-4x6-10'],
                'status' => $PUB,
                'muscles' => [
                    ['anterior_deltoid', $PRI, 40],
                    ['lateral_deltoid',  $PRI, 30],
                    ['triceps_brachii',  $SEC, 30],
                ],
                'equipment' => [
                    ['dumbbell', true],
                    ['bench', false],
                ],
            ],
            [
                'name' => 'Dominadas (pull-up)',
                'code' => 'pull_up',
                'level_code' => 'advanced',
                'pattern_code' => 'pull',
                'plane_code' => 'frontal',
                'unilateral' => false,
                'external_load' => false,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3-5xAMRAP'],
                'status' => $PUB,
                'muscles' => [
                    ['latissimus_dorsi', $PRI, 50],
                    ['rhomboids',        $SEC, 20],
                    ['biceps_brachii',   $SEC, 30],
                ],
                'equipment' => [
                    // si tenés code para barra fija, podés sumar aquí
                ],
            ],
            [
                'name' => 'Jalón al pecho (polea)',
                'code' => 'lat_pulldown',
                'level_code' => 'beginner',
                'pattern_code' => 'pull',
                'plane_code' => 'frontal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3-4x8-12'],
                'status' => $PUB,
                'muscles' => [
                    ['latissimus_dorsi', $PRI, 50],
                    ['rhomboids',        $SEC, 20],
                    ['biceps_brachii',   $SEC, 30],
                ],
                'equipment' => [
                    ['lat_pulldown_machine', true],
                ],
            ],
            [
                'name' => 'Remo sentado (polea)',
                'code' => 'seated_cable_row',
                'level_code' => 'beginner',
                'pattern_code' => 'pull',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3-4x8-12'],
                'status' => $PUB,
                'muscles' => [
                    ['rhomboids',        $PRI, 40],
                    ['latissimus_dorsi', $PRI, 40],
                    ['posterior_deltoid',$SEC, 20],
                ],
                'equipment' => [
                    ['seated_row_machine', true],
                ],
            ],
            [
                'name' => 'Remo con barra',
                'code' => 'barbell_row',
                'level_code' => 'intermediate',
                'pattern_code' => 'pull',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '4x6-10'],
                'status' => $PUB,
                'muscles' => [
                    ['rhomboids',        $PRI, 40],
                    ['latissimus_dorsi', $PRI, 40],
                    ['posterior_deltoid',$SEC, 20],
                ],
                'equipment' => [
                    ['barbell', true],
                ],
            ],
            [
                'name' => 'Pallof press (antirotación)',
                'code' => 'pallof_press',
                'level_code' => 'beginner',
                'pattern_code' => 'anti_rotation',
                'plane_code' => 'transverse',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $TIME,
                'default_prescription' => ['notes' => '3x20-30s por lado'],
                'status' => $PUB,
                'muscles' => [
                    ['core', $PRI, 70],
                    ['gluteus_medius', $STB, 15],
                    // si tenés obliques separados, puedes mapearlos
                ],
                'equipment' => [
                    ['cable_machine', true],
                ],
            ],
            [
                'name' => 'Plancha frontal',
                'code' => 'front_plank',
                'level_code' => 'beginner',
                'pattern_code' => 'anti_rotation',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => false,
                'default_modality' => $TIME,
                'default_prescription' => ['notes' => '3x30-60s'],
                'status' => $PUB,
                'muscles' => [
                    ['rectus_abdominis', $PRI, 40],
                    ['transversus_abdominis', $PRI, 30],
                    // oblicuos genéricos
                ],
                'equipment' => [
                    ['bodyweight', false],
                ],
            ],
            [
                'name' => 'Farmer carry (mancuernas)',
                'code' => 'farmer_carry_db',
                'level_code' => 'intermediate',
                'pattern_code' => 'carry',
                'plane_code' => 'multi',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $DIST,
                'default_prescription' => ['notes' => '4x20-30m'],
                'status' => $PUB,
                'muscles' => [
                    ['forearm_flexors', $PRI, 30],
                    ['trapezius', $PRI, 30],
                    ['core', $STB, 40],
                ],
                'equipment' => [
                    ['dumbbell', true],
                    ['trap_bar', false],
                ],
            ],
            [
                'name' => 'Kettlebell swing',
                'code' => 'kb_swing',
                'level_code' => 'intermediate',
                'pattern_code' => 'hinge',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '5x15-20'],
                'status' => $PUB,
                'muscles' => [
                    ['gluteus_maximus', $PRI, 45],
                    ['hamstrings', $PRI, 35],
                    ['erector_spinae', $SEC, 20],
                ],
                'equipment' => [
                    ['kettlebell', true],
                ],
            ],
            [
                'name' => 'Extensión de cuádriceps (máquina)',
                'code' => 'leg_extension',
                'level_code' => 'beginner',
                'pattern_code' => 'squat',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3-4x10-15'],
                'status' => $PUB,
                'muscles' => [
                    ['quadriceps', $PRI, 90],
                ],
                'equipment' => [
                    ['leg_extension_machine', true],
                ],
            ],
            [
                'name' => 'Curl femoral (máquina)',
                'code' => 'leg_curl',
                'level_code' => 'beginner',
                'pattern_code' => 'hinge',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3-4x10-15'],
                'status' => $PUB,
                'muscles' => [
                    ['hamstrings', $PRI, 90],
                ],
                'equipment' => [
                    ['leg_curl_machine', true],
                ],
            ],
            [
                'name' => 'Prensa de piernas',
                'code' => 'leg_press',
                'level_code' => 'beginner',
                'pattern_code' => 'squat',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => true,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '3-4x10-15'],
                'status' => $PUB,
                'muscles' => [
                    ['quadriceps', $PRI, 60],
                    ['gluteus_maximus', $SEC, 25],
                    ['hamstrings', $SEC, 15],
                ],
                'equipment' => [
                    ['leg_press_machine', true],
                ],
            ],
            [
                'name' => 'Elevación de talones de pie',
                'code' => 'standing_calf_raise',
                'level_code' => 'beginner',
                'pattern_code' => 'squat',
                'plane_code' => 'sagittal',
                'unilateral' => false,
                'external_load' => false,
                'default_modality' => $REPS,
                'default_prescription' => ['notes' => '4x12-20'],
                'status' => $PUB,
                'muscles' => [
                    ['gastrocnemius', $PRI, 70],
                    ['soleus', $SEC, 30],
                ],
                'equipment' => [
                    ['bodyweight', false],
                    ['weight_plate', false],
                ],
            ],
            // === PEGADO ÍNTEGRO: FIN ===
        ];

        foreach ($items as $data) {
            $exercise = Exercise::updateOrCreate(
                ['code' => $data['code']],
                [
                    'uuid'                  => Exercise::where('code', $data['code'])->value('uuid') ?: (string) Str::orderedUuid(),
                    'name'                  => $data['name'],
                    'status'                => $data['status'] ?? $PUB,
                    'exercise_level_id'     => isset($data['level_code'])   ? $levelId($data['level_code'])   : null,
                    'movement_pattern_id'   => isset($data['pattern_code']) ? $patternId($data['pattern_code']) : null,
                    'exercise_plane_id'     => isset($data['plane_code'])   ? $planeId($data['plane_code'])   : null,
                    'unilateral'            => (bool) ($data['unilateral'] ?? false),
                    'external_load'         => (bool) ($data['external_load'] ?? false),
                    'default_modality'      => $data['default_modality'] ?? $REPS,
                    'default_prescription'  => $data['default_prescription'] ?? [],
                    'tempo_notation'        => $data['tempo_notation']       ?? null,
                    'range_of_motion_notes' => $data['range_of_motion_notes']?? null,
                    'equipment_notes'       => $data['equipment_notes']      ?? null,
                    'setup_steps'           => $data['setup_steps']          ?? [],
                    'execution_cues'        => $data['execution_cues']       ?? [],
                    'common_mistakes'       => $data['common_mistakes']      ?? [],
                    'breathing'             => $data['breathing']            ?? null,
                    'safety_notes'          => $data['safety_notes']         ?? null,
                ]
            );

            // Pivots: músculos
            $syncMuscles = [];
            foreach (($data['muscles'] ?? []) as $row) {
                [$mCode, $role, $pct] = $row;
                $mid = $muscleId($mCode);
                if ($mid) {
                    $syncMuscles[$mid] = ['role' => $role, 'involvement_pct' => $pct];
                }
            }
            $exercise->muscles()->sync($syncMuscles);

            // Pivots: equipamiento
            $syncEquip = [];
            foreach (($data['equipment'] ?? []) as $row) {
                [$eCode, $req] = $row;
                $eid = $equipId($eCode);
                if ($eid) {
                    $syncEquip[$eid] = ['is_required' => (bool) $req];
                }
            }
            $exercise->equipment()->sync($syncEquip);
        }
    }
}
