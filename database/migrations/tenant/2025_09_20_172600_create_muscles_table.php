<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_muscles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->foreignId('muscle_group_id')->nullable()->constrained('exercise_muscle_groups')->nullOnDelete();
            $table->string('name')->unique();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
/*
        // Seed: exercise_muscles (después de crear exercise_muscles)
        // Obtenemos IDs de grupos por code para asignar FK
        $groupIds = DB::table('exercise_muscle_groups')->pluck('id', 'code')->toArray();
        $now = now();

        DB::table('exercise_muscles')->insert([
            // Pecho
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Pectoral mayor',
                'code' => 'pectoralis_major',
                'muscle_group_id' => $groupIds['chest'] ?? null,
                'description' => null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Pectoral menor',
                'code' => 'pectoralis_minor',
                'muscle_group_id' => $groupIds['chest'] ?? null,
                'description' => null,
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Espalda
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Dorsal ancho',
                'code' => 'latissimus_dorsi',
                'muscle_group_id' => $groupIds['back'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Romboides',
                'code' => 'rhomboids',
                'muscle_group_id' => $groupIds['back'] ?? null,
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Erectores espinales',
                'code' => 'erector_spinae',
                'muscle_group_id' => $groupIds['back'] ?? null,
                'order' => 3,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Hombros
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Deltoides anterior',
                'code' => 'anterior_deltoid',
                'muscle_group_id' => $groupIds['shoulders'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Deltoides medio',
                'code' => 'lateral_deltoid',
                'muscle_group_id' => $groupIds['shoulders'] ?? null,
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Deltoides posterior',
                'code' => 'posterior_deltoid',
                'muscle_group_id' => $groupIds['shoulders'] ?? null,
                'order' => 3,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Manguito rotador',
                'code' => 'rotator_cuff',
                'muscle_group_id' => $groupIds['shoulders'] ?? null,
                'order' => 4,
                'status' => 'published',
                'meta' => null,
                'description' => 'Supraespinoso, infraespinoso, redondo menor, subescapular.',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Brazos
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Bíceps braquial',
                'code' => 'biceps_brachii',
                'muscle_group_id' => $groupIds['arms'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Braquial anterior',
                'code' => 'brachialis',
                'muscle_group_id' => $groupIds['arms'] ?? null,
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Tríceps braquial',
                'code' => 'triceps_brachii',
                'muscle_group_id' => $groupIds['arms'] ?? null,
                'order' => 3,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Core
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Recto abdominal',
                'code' => 'rectus_abdominis',
                'muscle_group_id' => $groupIds['core'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Oblicuos externos',
                'code' => 'external_obliques',
                'muscle_group_id' => $groupIds['core'] ?? null,
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Oblicuos internos',
                'code' => 'internal_obliques',
                'muscle_group_id' => $groupIds['core'] ?? null,
                'order' => 3,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Transverso del abdomen',
                'code' => 'transversus_abdominis',
                'muscle_group_id' => $groupIds['core'] ?? null,
                'order' => 4,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Glúteos
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Glúteo mayor',
                'code' => 'gluteus_maximus',
                'muscle_group_id' => $groupIds['glutes'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Glúteo medio',
                'code' => 'gluteus_medius',
                'muscle_group_id' => $groupIds['glutes'] ?? null,
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Glúteo menor',
                'code' => 'gluteus_minimus',
                'muscle_group_id' => $groupIds['glutes'] ?? null,
                'order' => 3,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Cuádriceps
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Recto femoral',
                'code' => 'rectus_femoris',
                'muscle_group_id' => $groupIds['quadriceps'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Vasto lateral',
                'code' => 'vastus_lateralis',
                'muscle_group_id' => $groupIds['quadriceps'] ?? null,
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Vasto medial',
                'code' => 'vastus_medialis',
                'muscle_group_id' => $groupIds['quadriceps'] ?? null,
                'order' => 3,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Vasto intermedio',
                'code' => 'vastus_intermedius',
                'muscle_group_id' => $groupIds['quadriceps'] ?? null,
                'order' => 4,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Isquios
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Bíceps femoral',
                'code' => 'biceps_femoris',
                'muscle_group_id' => $groupIds['hamstrings'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Semitendinoso',
                'code' => 'semitendinosus',
                'muscle_group_id' => $groupIds['hamstrings'] ?? null,
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Semimembranoso',
                'code' => 'semimembranosus',
                'muscle_group_id' => $groupIds['hamstrings'] ?? null,
                'order' => 3,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Pantorrillas
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Gastrocnemio',
                'code' => 'gastrocnemius',
                'muscle_group_id' => $groupIds['calves'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Sóleo',
                'code' => 'soleus',
                'muscle_group_id' => $groupIds['calves'] ?? null,
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Aductores
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Aductor mayor',
                'code' => 'adductor_magnus',
                'muscle_group_id' => $groupIds['adductors'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Aductor largo',
                'code' => 'adductor_longus',
                'muscle_group_id' => $groupIds['adductors'] ?? null,
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Abductores (cadera lateral)
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Tensor de la fascia lata',
                'code' => 'tensor_fasciae_latae',
                'muscle_group_id' => $groupIds['abductors'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Trapecios / cuello
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Trapecio',
                'code' => 'trapezius',
                'muscle_group_id' => $groupIds['traps'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => 'Porción superior, media e inferior.',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Antebrazos
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Flexores del antebrazo',
                'code' => 'forearm_flexors',
                'muscle_group_id' => $groupIds['forearms'] ?? null,
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Extensores del antebrazo',
                'code' => 'forearm_extensors',
                'muscle_group_id' => $groupIds['forearms'] ?? null,
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Extra útiles
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Psoas-ilíaco (flexor de cadera)',
                'code' => 'iliopsoas',
                'muscle_group_id' => $groupIds['abductors'] ?? $groupIds['core'] ?? null,
                'order' => 99,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Tibial anterior',
                'code' => 'tibialis_anterior',
                'muscle_group_id' => $groupIds['calves'] ?? null,
                'order' => 99,
                'status' => 'published',
                'meta' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);*/
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_muscles');
    }
};
