<?php

return [
    'info' => [
        'title' => 'FitTrack Mobile API',
        'version' => '1.0.0',
        'description' => 'Contrato API para app mobile. Incluye request/response detallado por endpoint.',
    ],

    'tags' => [
        ['name' => 'Documentation', 'description' => 'Endpoints de documentacion'],
        ['name' => 'Auth', 'description' => 'Autenticacion'],
        ['name' => 'Profile', 'description' => 'Perfil del alumno'],
        ['name' => 'Plans', 'description' => 'Planes de entrenamiento'],
        ['name' => 'Workouts', 'description' => 'Sesiones de entrenamiento'],
        ['name' => 'Weight', 'description' => 'Registros de peso'],
        ['name' => 'Progress', 'description' => 'Home, dashboard y progreso'],
        ['name' => 'Messaging', 'description' => 'Mensajeria alumno-entrenador'],
    ],

    'components' => [
        'schemas' => [
            'ApiError' => [
                'type' => 'object',
                'properties' => [
                    'error' => ['type' => 'string'],
                    'message' => ['type' => ['string', 'null']],
                    'details' => ['type' => 'object', 'additionalProperties' => true],
                ],
                'required' => ['error'],
            ],
            'ValidationError' => [
                'type' => 'object',
                'properties' => [
                    'error' => ['type' => 'string'],
                    'details' => ['type' => 'object', 'additionalProperties' => true],
                ],
                'required' => ['error', 'details'],
            ],
            'StudentProfile' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'id' => ['type' => ['integer', 'string']],
                    'uuid' => ['type' => 'string'],
                    'email' => ['type' => 'string'],
                    'first_name' => ['type' => ['string', 'null']],
                    'last_name' => ['type' => ['string', 'null']],
                    'full_name' => ['type' => ['string', 'null']],
                    'phone' => ['type' => ['string', 'null']],
                    'goal' => ['type' => ['string', 'null']],
                    'timezone' => ['type' => ['string', 'null']],
                    'birth_date' => ['type' => ['string', 'null']],
                    'gender' => ['type' => ['string', 'null']],
                    'height_cm' => ['type' => ['number', 'null']],
                    'weight_kg' => [
                        'type' => ['number', 'null'],
                        'description' => 'Ultimo peso registrado (tabla student_weight_entries), con fallback a data.weight_kg.',
                    ],
                    'imc' => [
                        'type' => ['number', 'null'],
                        'description' => 'IMC calculado con height_cm y weight_kg efectivo.',
                    ],
                    'language' => ['type' => ['string', 'null']],
                    'notifications' => [
                        'type' => ['object', 'null'],
                        'additionalProperties' => ['type' => 'boolean'],
                    ],
                    'training_experience' => ['type' => ['string', 'null']],
                    'days_per_week' => ['type' => ['integer', 'null']],
                ],
            ],
            'WorkoutExerciseImage' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'url' => ['type' => 'string'],
                    'thumb' => ['type' => ['string', 'null']],
                ],
            ],
            'WorkoutExercise' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'id' => ['type' => ['integer', 'string', 'null']],
                    'exercise_id' => ['type' => ['integer', 'string', 'null']],
                    'name' => ['type' => ['string', 'null']],
                    'description' => [
                        'type' => ['string', 'null'],
                        'description' => 'Si viene null en exercises_data, la API intenta hidratarla desde la entidad Exercise.',
                    ],
                    'category' => [
                        'type' => ['string', 'null'],
                        'description' => 'Si viene null/vacia en exercises_data, la API intenta hidratarla desde la entidad Exercise.',
                    ],
                    'level' => [
                        'type' => ['string', 'null'],
                        'description' => 'Si viene null/vacio en exercises_data, la API intenta hidratarlo desde la entidad Exercise.',
                    ],
                    'equipment' => [
                        'type' => ['string', 'null'],
                        'description' => 'Si viene null/vacio en exercises_data, la API intenta hidratarlo desde la entidad Exercise.',
                    ],
                    'image_url' => ['type' => ['string', 'null']],
                    'images' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/WorkoutExerciseImage'],
                        'description' => 'Coleccion completa de imagenes del ejercicio.',
                    ],
                    'pdf_url' => ['type' => ['string', 'null']],
                    'completed' => ['type' => ['boolean', 'null']],
                    'sets' => ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => true]],
                ],
            ],
            'Workout' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'uuid' => ['type' => 'string'],
                    'session_instance_id' => ['type' => ['string', 'null']],
                    'status' => ['type' => ['string', 'null']],
                    'plan_day' => ['type' => ['integer', 'null']],
                    'cycle_index' => ['type' => ['integer', 'null']],
                    'started_at' => ['type' => ['string', 'null']],
                    'completed_at' => ['type' => ['string', 'null']],
                    'duration_minutes' => ['type' => 'integer'],
                    'rating' => ['type' => ['integer', 'null']],
                    'notes' => ['type' => ['string', 'null']],
                    'exercises_data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkoutExercise']],
                    'exercises' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkoutExercise']],
                    'progress' => ['type' => 'object', 'additionalProperties' => true],
                    'live' => ['type' => 'object', 'additionalProperties' => true],
                ],
            ],
            'WeightEntry' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'uuid' => ['type' => 'string'],
                    'weight_kg' => ['type' => 'number'],
                    'recorded_at' => ['type' => 'string'],
                    'source' => ['type' => ['string', 'null']],
                    'notes' => ['type' => ['string', 'null']],
                    'meta' => ['type' => 'object', 'additionalProperties' => true],
                    'created_at' => ['type' => 'string'],
                ],
            ],
            'PlanSummary' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'uuid' => ['type' => 'string'],
                    'name' => ['type' => ['string', 'null']],
                    'description' => ['type' => ['string', 'null']],
                    'goal' => ['type' => ['string', 'null']],
                    'duration' => ['type' => ['string', 'integer', 'null']],
                    'is_active' => ['type' => 'boolean'],
                    'assigned_from' => ['type' => ['string', 'null']],
                    'assigned_until' => ['type' => ['string', 'null']],
                    'exercises_count' => ['type' => 'integer'],
                ],
            ],
            'PlanDetail' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'uuid' => ['type' => 'string'],
                    'name' => ['type' => ['string', 'null']],
                    'description' => ['type' => ['string', 'null']],
                    'goal' => ['type' => ['string', 'null']],
                    'duration' => ['type' => ['string', 'integer', 'null']],
                    'is_active' => ['type' => 'boolean'],
                    'assigned_from' => ['type' => ['string', 'null']],
                    'assigned_until' => ['type' => ['string', 'null']],
                    'meta' => ['type' => ['object', 'array', 'null'], 'additionalProperties' => true],
                    'exercises' => ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => true]],
                ],
            ],
            'Conversation' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'uuid' => ['type' => 'string'],
                    'type' => ['type' => ['string', 'null']],
                    'student_id' => ['type' => ['integer', 'null']],
                    'subject' => ['type' => ['string', 'null']],
                    'last_message_at' => ['type' => ['string', 'null']],
                    'participants' => ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => true]],
                    'last_message' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                ],
            ],
            'Message' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'conversation_id' => ['type' => 'integer'],
                    'sender_type' => ['type' => ['string', 'null']],
                    'sender_id' => ['type' => ['string', 'integer', 'null']],
                    'body' => ['type' => 'string'],
                    'attachments' => ['type' => ['array', 'null'], 'items' => ['type' => 'object', 'additionalProperties' => true]],
                    'status' => ['type' => ['string', 'null']],
                    'created_at' => ['type' => ['string', 'null']],
                    'updated_at' => ['type' => ['string', 'null']],
                ],
            ],
            'PaginatedMessages' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'current_page' => ['type' => 'integer'],
                    'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Message']],
                    'per_page' => ['type' => ['integer', 'string']],
                    'total' => ['type' => 'integer'],
                    'last_page' => ['type' => 'integer'],
                ],
            ],
            'WorkoutContext' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'student' => ['type' => 'object', 'additionalProperties' => true],
                    'gamification' => ['type' => 'object', 'additionalProperties' => true],
                    'active_plan' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                    'active_workout' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                    'requested_workout_id' => ['type' => ['integer', 'null']],
                    'requested_workout_uuid' => ['type' => ['string', 'null']],
                ],
            ],
            'GamificationPatchProfile' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'has_profile' => ['type' => 'boolean'],
                    'current_xp' => ['type' => 'integer', 'description' => 'XP actual dentro del nivel (alias de xp_inside_level).'],
                    'total_xp' => ['type' => 'integer'],
                    'current_level' => ['type' => 'integer'],
                    'current_tier' => ['type' => 'integer'],
                    'tier_name' => ['type' => ['string', 'null']],
                    'active_badge' => ['type' => ['string', 'null']],
                    'level_progress_percent' => ['type' => 'integer'],
                    'xp_for_current_level' => ['type' => 'integer'],
                    'xp_for_next_level' => ['type' => 'integer'],
                    'xp_inside_level' => ['type' => 'integer'],
                    'xp_required_inside_level' => ['type' => 'integer'],
                    'total_exercises_completed' => ['type' => 'integer'],
                    'last_exercise_completed_at' => ['type' => ['string', 'null']],
                ],
                'required' => ['has_profile', 'current_xp', 'total_xp'],
            ],
            'GamificationEvent' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'exercise_id' => ['type' => ['integer', 'null']],
                    'exercise_name' => ['type' => ['string', 'null']],
                    'exercise_level' => ['type' => ['string', 'null']],
                    'awarded' => ['type' => 'boolean'],
                    'reason' => [
                        'type' => 'string',
                        'enum' => [
                            'awarded',
                            'already_awarded_in_session',
                            'exercise_identifier_missing',
                            'exercise_not_found',
                        ],
                    ],
                    'awarded_xp' => ['type' => 'integer'],
                    'xp' => ['type' => 'integer'],
                    'xp_gained' => ['type' => 'integer'],
                    'session_instance_id' => ['type' => ['string', 'null']],
                    'current_xp' => ['type' => ['integer', 'null'], 'description' => 'XP actual del alumno dentro del nivel tras procesar el evento.'],
                    'total_xp' => ['type' => ['integer', 'null'], 'description' => 'XP total acumulado del alumno tras procesar el evento.'],
                    'level_before' => ['type' => ['integer', 'null']],
                    'level_after' => ['type' => ['integer', 'null']],
                    'tier_before' => ['type' => ['integer', 'null']],
                    'tier_after' => ['type' => ['integer', 'null']],
                    'leveled_up' => ['type' => ['boolean', 'null']],
                    'tier_changed' => ['type' => ['boolean', 'null']],
                ],
                'required' => ['awarded', 'reason', 'awarded_xp', 'xp', 'xp_gained'],
            ],
            'WorkoutPatchGamification' => [
                'type' => 'object',
                'additionalProperties' => true,
                'properties' => [
                    'profile' => ['$ref' => '#/components/schemas/GamificationPatchProfile'],
                    'events' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/GamificationEvent']],
                ],
                'required' => ['profile', 'events'],
            ],
            'WorkoutPatchSync' => [
                'type' => 'object',
                'properties' => [
                    'exercises_updated' => ['type' => 'boolean'],
                    'live_progress_updated' => ['type' => 'boolean'],
                ],
                'required' => ['exercises_updated', 'live_progress_updated'],
                'additionalProperties' => true,
            ],
        ],
    ],

    'operations' => [
        '/docs' => [
            'get' => [
                'summary' => 'Obtener la documentacion OpenAPI',
                'description' => 'Devuelve la especificacion en JSON. Endpoint publico, sin token.',
                'responses' => [
                    '200' => [
                        'description' => 'OpenAPI spec',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'openapi' => ['type' => 'string'],
                                        'info' => ['type' => 'object', 'additionalProperties' => true],
                                        'paths' => ['type' => 'object', 'additionalProperties' => true],
                                        'components' => ['type' => 'object', 'additionalProperties' => true],
                                    ],
                                    'required' => ['openapi', 'info', 'paths'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/auth/login' => [
            'post' => [
                'summary' => 'Login mobile',
                'description' => 'Autentica por email/password, detecta tenant y retorna token Sanctum.',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['email', 'password'],
                                'properties' => [
                                    'email' => ['type' => 'string', 'format' => 'email'],
                                    'password' => ['type' => 'string', 'format' => 'password'],
                                ],
                            ],
                            'example' => [
                                'email' => 'alumno@fittrack.test',
                                'password' => 'secret123',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Login exitoso',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'tenant' => ['type' => 'object', 'additionalProperties' => true],
                                        'user' => ['type' => 'object', 'additionalProperties' => true],
                                        'student' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                                        'token' => ['type' => 'string'],
                                    ],
                                    'required' => ['tenant', 'user', 'token'],
                                ],
                            ],
                        ],
                    ],
                    '401' => [
                        'description' => 'Credenciales invalidas',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApiError']]],
                    ],
                    '404' => [
                        'description' => 'Usuario no encontrado en ningun tenant',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApiError']]],
                    ],
                    '422' => [
                        'description' => 'Error de validacion',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]],
                    ],
                ],
            ],
        ],

        '/auth/logout' => [
            'post' => [
                'summary' => 'Logout',
                'description' => 'Revoca el token actual.',
                'requestBody' => null,
                'responses' => [
                    '200' => [
                        'description' => 'Sesion cerrada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                    ],
                                    'required' => ['message'],
                                ],
                                'example' => ['message' => 'Sesion cerrada correctamente'],
                            ],
                        ],
                    ],
                    '401' => [
                        'description' => 'No autenticado',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApiError']]],
                    ],
                ],
            ],
        ],

        '/user' => [
            'get' => [
                'summary' => 'Usuario autenticado',
                'description' => 'Devuelve el usuario asociado al token actual.',
                'responses' => [
                    '200' => [
                        'description' => 'Usuario',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'additionalProperties' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/profile' => [
            'get' => [
                'summary' => 'Perfil del alumno',
                'responses' => [
                    '200' => [
                        'description' => 'Perfil',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/StudentProfile'],
                                    ],
                                    'required' => ['data'],
                                ],
                            ],
                        ],
                    ],
                    '404' => [
                        'description' => 'Perfil no encontrado',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApiError']]],
                    ],
                ],
            ],
            'patch' => [
                'summary' => 'Actualizar perfil del alumno',
                'requestBody' => [
                    'required' => false,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'first_name' => ['type' => 'string', 'maxLength' => 255],
                                    'last_name' => ['type' => 'string', 'maxLength' => 255],
                                    'phone' => ['type' => 'string', 'maxLength' => 50],
                                    'goal' => ['type' => 'string', 'maxLength' => 100],
                                    'timezone' => ['type' => 'string', 'maxLength' => 50],
                                    'birth_date' => ['type' => 'string', 'format' => 'date'],
                                    'gender' => ['type' => 'string', 'enum' => ['male', 'female', 'other']],
                                    'height_cm' => ['type' => 'number', 'minimum' => 50, 'maximum' => 300],
                                    'weight_kg' => ['type' => 'number', 'minimum' => 20, 'maximum' => 500],
                                    'language' => ['type' => 'string', 'maxLength' => 10],
                                    'notifications' => ['type' => 'object', 'additionalProperties' => ['type' => 'boolean']],
                                    'training_experience' => ['type' => 'string', 'maxLength' => 100],
                                    'days_per_week' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 7],
                                    'contact' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'phone' => ['type' => ['string', 'null'], 'maxLength' => 50],
                                            'timezone' => ['type' => ['string', 'null'], 'maxLength' => 50],
                                            'language' => ['type' => ['string', 'null'], 'maxLength' => 10],
                                        ],
                                        'additionalProperties' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Perfil actualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                        'data' => ['$ref' => '#/components/schemas/StudentProfile'],
                                    ],
                                    'required' => ['message', 'data'],
                                ],
                            ],
                        ],
                    ],
                    '422' => [
                        'description' => 'Datos invalidos',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]],
                    ],
                ],
            ],
        ],

        '/profile/preferences' => [
            'patch' => [
                'summary' => 'Actualizar contacto y notificaciones',
                'description' => 'Endpoint dedicado para preferencias de contacto (telefono, timezone, idioma) y notificaciones.',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'contact' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'phone' => ['type' => ['string', 'null'], 'maxLength' => 50],
                                            'timezone' => ['type' => ['string', 'null'], 'maxLength' => 50],
                                            'language' => ['type' => ['string', 'null'], 'maxLength' => 10],
                                        ],
                                        'additionalProperties' => false,
                                    ],
                                    'phone' => ['type' => ['string', 'null'], 'maxLength' => 50],
                                    'timezone' => ['type' => ['string', 'null'], 'maxLength' => 50],
                                    'language' => ['type' => ['string', 'null'], 'maxLength' => 10],
                                    'notifications' => [
                                        'type' => 'object',
                                        'additionalProperties' => ['type' => 'boolean'],
                                        'description' => 'Preferencias por canal/evento. Ejemplo: new_plan, session_reminder.',
                                    ],
                                ],
                                'anyOf' => [
                                    ['required' => ['contact']],
                                    ['required' => ['phone']],
                                    ['required' => ['timezone']],
                                    ['required' => ['language']],
                                    ['required' => ['notifications']],
                                ],
                            ],
                            'example' => [
                                'contact' => [
                                    'phone' => '+54 11 5555 5555',
                                    'timezone' => 'America/Argentina/Buenos_Aires',
                                    'language' => 'es',
                                ],
                                'notifications' => [
                                    'new_plan' => true,
                                    'session_reminder' => true,
                                    'weekly_summary' => false,
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Preferencias actualizadas',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                        'data' => ['$ref' => '#/components/schemas/StudentProfile'],
                                    ],
                                    'required' => ['message', 'data'],
                                ],
                            ],
                        ],
                    ],
                    '422' => [
                        'description' => 'Datos invalidos',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]],
                    ],
                ],
            ],
        ],

        '/plans' => [
            'get' => [
                'summary' => 'Listar planes del alumno',
                'responses' => [
                    '200' => [
                        'description' => 'Lista de planes',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/PlanSummary'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/plans/current' => [
            'get' => [
                'summary' => 'Plan activo actual',
                'responses' => [
                    '200' => [
                        'description' => 'Plan activo o null',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => ['object', 'null'],
                                            'additionalProperties' => true,
                                        ],
                                        'message' => ['type' => ['string', 'null']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/plans/{id}' => [
            'get' => [
                'summary' => 'Detalle de un plan',
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'description' => 'ID numerico del plan',
                        'schema' => ['type' => 'integer', 'minimum' => 1],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Detalle del plan',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/PlanDetail'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/workouts' => [
            'get' => [
                'summary' => 'Listar workouts',
                'parameters' => [
                    [
                        'name' => 'status',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filtra por estado',
                        'schema' => [
                            'type' => 'string',
                            'enum' => ['pending', 'in_progress', 'completed', 'skipped'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workouts del alumno',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Workout']],
                                        'context' => ['$ref' => '#/components/schemas/WorkoutContext'],
                                    ],
                                    'required' => ['data', 'context'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/workouts/today' => [
            'get' => [
                'summary' => 'Workout de hoy',
                'description' => 'Obtiene o crea el workout de hoy segun plan activo.',
                'responses' => [
                    '200' => [
                        'description' => 'Workout de hoy o mensaje sin plan',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'oneOf' => [
                                                ['$ref' => '#/components/schemas/Workout'],
                                                ['type' => 'null'],
                                            ],
                                        ],
                                        'message' => ['type' => ['string', 'null']],
                                        'context' => ['$ref' => '#/components/schemas/WorkoutContext'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/workouts/stats' => [
            'get' => [
                'summary' => 'Estadisticas de workouts',
                'responses' => [
                    '200' => [
                        'description' => 'Estadisticas',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'completed_workouts' => ['type' => 'integer'],
                                                'pending_workouts' => ['type' => 'integer'],
                                                'skipped_workouts' => ['type' => 'integer'],
                                                'average_duration_minutes' => ['type' => ['number', 'null']],
                                                'average_rating' => ['type' => ['number', 'null']],
                                                'total_duration_minutes' => ['type' => 'integer'],
                                            ],
                                        ],
                                        'context' => ['$ref' => '#/components/schemas/WorkoutContext'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/workouts/{id}' => [
            'get' => [
                'summary' => 'Detalle de workout',
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'description' => 'ID numerico o UUID del workout',
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workout',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/Workout'],
                                        'context' => ['$ref' => '#/components/schemas/WorkoutContext'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'patch' => [
                'summary' => 'Actualizar progreso de workout',
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'description' => 'ID numerico o UUID del workout',
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'description' => 'Debe incluir al menos uno: exercises, elapsed_minutes, elapsed_seconds, effort.',
                                'anyOf' => [
                                    ['required' => ['exercises']],
                                    ['required' => ['elapsed_minutes']],
                                    ['required' => ['elapsed_seconds']],
                                    ['required' => ['effort']],
                                ],
                                'properties' => [
                                    'exercises' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'oneOf' => [
                                                ['required' => ['id']],
                                                ['required' => ['exercise_id']],
                                            ],
                                            'properties' => [
                                                'id' => ['type' => ['integer', 'string', 'null']],
                                                'exercise_id' => ['type' => ['integer', 'null'], 'minimum' => 1],
                                                'name' => ['type' => ['string', 'null']],
                                                'completed' => ['type' => ['boolean', 'null']],
                                                'sets' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'reps' => ['type' => ['integer', 'null'], 'minimum' => 0],
                                                            'weight' => ['type' => ['number', 'null'], 'minimum' => 0],
                                                            'duration_seconds' => ['type' => ['integer', 'null'], 'minimum' => 0],
                                                            'completed' => ['type' => ['boolean', 'null']],
                                                        ],
                                                        'additionalProperties' => true,
                                                    ],
                                                ],
                                            ],
                                            'additionalProperties' => true,
                                        ],
                                    ],
                                    'elapsed_minutes' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 1440],
                                    'elapsed_seconds' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 86400],
                                    'effort' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10],
                                ],
                            ],
                            'examples' => [
                                'exercises_sync' => [
                                    'summary' => 'Sincronizacion de ejercicios',
                                    'value' => [
                                        'exercises' => [
                                            [
                                                'exercise_id' => 101,
                                                'completed' => true,
                                                'sets' => [
                                                    ['reps' => 12, 'weight' => 40, 'duration_seconds' => 0, 'completed' => true],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'live_sync' => [
                                    'summary' => 'Sincronizacion de timer/esfuerzo',
                                    'value' => [
                                        'elapsed_seconds' => 1580,
                                        'effort' => 7,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workout actualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                        'data' => ['$ref' => '#/components/schemas/Workout'],
                                        'gamification' => ['$ref' => '#/components/schemas/WorkoutPatchGamification'],
                                        'sync' => ['$ref' => '#/components/schemas/WorkoutPatchSync'],
                                        'context' => ['$ref' => '#/components/schemas/WorkoutContext'],
                                    ],
                                    'required' => ['message', 'data', 'gamification', 'sync', 'context'],
                                ],
                                'examples' => [
                                    'awarded_xp' => [
                                        'summary' => 'Evento con XP otorgado',
                                        'value' => [
                                            'message' => 'Workout updated',
                                            'data' => [
                                                'id' => 321,
                                                'uuid' => 'f6be7186-8d32-4f79-93e8-cbe9110a9582',
                                                'status' => 'in_progress',
                                            ],
                                            'gamification' => [
                                                'profile' => [
                                                    'has_profile' => true,
                                                    'current_xp' => 47,
                                                    'total_xp' => 1247,
                                                    'current_level' => 12,
                                                    'current_tier' => 2,
                                                ],
                                                'events' => [
                                                    [
                                                        'exercise_id' => 101,
                                                        'exercise_name' => 'Sentadilla',
                                                        'exercise_level' => 'intermediate',
                                                        'awarded' => true,
                                                        'reason' => 'awarded',
                                                        'awarded_xp' => 15,
                                                        'xp' => 15,
                                                        'xp_gained' => 15,
                                                        'session_instance_id' => '5ecf320f-53f5-4262-894d-512f679318d6',
                                                        'current_xp' => 47,
                                                        'total_xp' => 1247,
                                                        'level_before' => 12,
                                                        'level_after' => 12,
                                                        'tier_before' => 2,
                                                        'tier_after' => 2,
                                                        'leveled_up' => false,
                                                        'tier_changed' => false,
                                                    ],
                                                ],
                                            ],
                                            'sync' => [
                                                'exercises_updated' => true,
                                                'live_progress_updated' => false,
                                            ],
                                            'context' => [
                                                'requested_workout_id' => 321,
                                            ],
                                        ],
                                    ],
                                    'already_awarded_in_session' => [
                                        'summary' => 'Evento ya premiado en la sesion (XP=0)',
                                        'value' => [
                                            'message' => 'Workout updated',
                                            'data' => [
                                                'id' => 321,
                                                'uuid' => 'f6be7186-8d32-4f79-93e8-cbe9110a9582',
                                                'status' => 'in_progress',
                                            ],
                                            'gamification' => [
                                                'profile' => [
                                                    'has_profile' => true,
                                                    'current_xp' => 47,
                                                    'total_xp' => 1247,
                                                    'current_level' => 12,
                                                    'current_tier' => 2,
                                                ],
                                                'events' => [
                                                    [
                                                        'exercise_id' => 101,
                                                        'exercise_name' => 'Sentadilla',
                                                        'awarded' => false,
                                                        'reason' => 'already_awarded_in_session',
                                                        'awarded_xp' => 0,
                                                        'xp' => 0,
                                                        'xp_gained' => 0,
                                                        'session_instance_id' => '5ecf320f-53f5-4262-894d-512f679318d6',
                                                        'current_xp' => 47,
                                                        'total_xp' => 1247,
                                                    ],
                                                ],
                                            ],
                                            'sync' => [
                                                'exercises_updated' => true,
                                                'live_progress_updated' => false,
                                            ],
                                            'context' => [
                                                'requested_workout_id' => 321,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '422' => [
                        'description' => 'Payload invalido',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]],
                    ],
                ],
            ],
        ],

        '/workouts/{id}/start' => [
            'post' => [
                'summary' => 'Iniciar workout',
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'description' => 'ID numerico o UUID del workout',
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'requestBody' => null,
                'responses' => [
                    '200' => [
                        'description' => 'Workout iniciado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                        'data' => ['$ref' => '#/components/schemas/Workout'],
                                        'context' => ['$ref' => '#/components/schemas/WorkoutContext'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/workouts/{id}/complete' => [
            'post' => [
                'summary' => 'Completar workout',
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'description' => 'ID numerico o UUID del workout',
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['duration_minutes'],
                                'properties' => [
                                    'duration_minutes' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1440],
                                    'rating' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 5],
                                    'notes' => ['type' => 'string', 'maxLength' => 1000],
                                    'survey' => ['type' => 'object', 'additionalProperties' => true],
                                    'current_weight' => ['type' => 'number', 'minimum' => 20, 'maximum' => 300],
                                    'current_weight_kg' => ['type' => 'number', 'minimum' => 20, 'maximum' => 300],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workout completado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                        'data' => ['$ref' => '#/components/schemas/Workout'],
                                        'weight_entry' => [
                                            'oneOf' => [
                                                ['$ref' => '#/components/schemas/WeightEntry'],
                                                ['type' => 'null'],
                                            ],
                                        ],
                                        'context' => ['$ref' => '#/components/schemas/WorkoutContext'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/workouts/{id}/skip' => [
            'post' => [
                'summary' => 'Saltar workout',
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'description' => 'ID numerico o UUID del workout',
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'requestBody' => [
                    'required' => false,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'reason' => ['type' => 'string', 'maxLength' => 500],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workout salteado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                        'data' => ['$ref' => '#/components/schemas/Workout'],
                                        'context' => ['$ref' => '#/components/schemas/WorkoutContext'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/weight' => [
            'get' => [
                'summary' => 'Historial de peso',
                'parameters' => [
                    [
                        'name' => 'limit',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 365],
                        'description' => 'Cantidad de registros (default 30)',
                    ],
                    [
                        'name' => 'days',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer', 'minimum' => 1],
                        'description' => 'Filtrar ultimos N dias',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Registros de peso',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WeightEntry']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'post' => [
                'summary' => 'Registrar peso',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['weight_kg'],
                                'properties' => [
                                    'weight_kg' => ['type' => 'number', 'minimum' => 20, 'maximum' => 500],
                                    'recorded_at' => ['type' => 'string', 'format' => 'date-time'],
                                    'notes' => ['type' => 'string', 'maxLength' => 500],
                                    'source' => ['type' => 'string', 'enum' => ['manual', 'scale_device', 'api']],
                                    'meta' => ['type' => 'object', 'additionalProperties' => true],
                                ],
                            ],
                            'example' => [
                                'weight_kg' => 74.3,
                                'source' => 'manual',
                                'notes' => 'Peso matutino',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Peso registrado (compatibilidad)',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                        'data' => ['$ref' => '#/components/schemas/WeightEntry'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '201' => [
                        'description' => 'Peso registrado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                        'data' => ['$ref' => '#/components/schemas/WeightEntry'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '422' => [
                        'description' => 'Datos invalidos',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]],
                    ],
                ],
            ],
        ],

        '/weight/latest' => [
            'get' => [
                'summary' => 'Ultimo peso registrado',
                'responses' => [
                    '200' => [
                        'description' => 'Ultimo peso o null',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'oneOf' => [
                                                ['$ref' => '#/components/schemas/WeightEntry'],
                                                ['type' => 'null'],
                                            ],
                                        ],
                                        'message' => ['type' => ['string', 'null']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/weight/change' => [
            'get' => [
                'summary' => 'Cambio de peso',
                'parameters' => [
                    [
                        'name' => 'days',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer', 'minimum' => 1],
                        'description' => 'Periodo en dias (default 7)',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Cambio de peso o null',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'oneOf' => [
                                                [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'period_days' => ['type' => 'integer'],
                                                        'initial_weight_kg' => ['type' => 'number'],
                                                        'current_weight_kg' => ['type' => 'number'],
                                                        'change_kg' => ['type' => 'number'],
                                                        'change_percentage' => ['type' => 'number'],
                                                        'direction' => ['type' => 'string', 'enum' => ['up', 'down', 'stable']],
                                                    ],
                                                ],
                                                ['type' => 'null'],
                                            ],
                                        ],
                                        'message' => ['type' => ['string', 'null']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/weight/average' => [
            'get' => [
                'summary' => 'Peso promedio del periodo',
                'parameters' => [
                    [
                        'name' => 'days',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer', 'minimum' => 1],
                        'description' => 'Periodo en dias (default 30)',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Promedio o null',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'oneOf' => [
                                                [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'period_days' => ['type' => 'integer'],
                                                        'average_weight_kg' => ['type' => 'number'],
                                                    ],
                                                ],
                                                ['type' => 'null'],
                                            ],
                                        ],
                                        'message' => ['type' => ['string', 'null']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/home' => [
            'get' => [
                'summary' => 'Home completo del alumno',
                'responses' => [
                    '200' => [
                        'description' => 'Dashboard home',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'student' => ['type' => 'object', 'additionalProperties' => true],
                                                'gamification' => ['type' => 'object', 'additionalProperties' => true],
                                                'active_plan' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                                                'today_workout' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                                                'active_workout' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                                                'progress_data' => ['type' => 'object', 'additionalProperties' => true],
                                                'trainings_this_month' => ['type' => 'integer'],
                                                'goal_this_month' => ['type' => 'integer'],
                                                'has_pending_payment' => ['type' => 'boolean'],
                                                'pending_payment' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                                                'no_active_plan_message' => ['type' => ['string', 'null']],
                                                'plan_history' => ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => true]],
                                                'home_state' => ['type' => 'object', 'additionalProperties' => true],
                                            ],
                                            'additionalProperties' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/progress/dashboard' => [
            'get' => [
                'summary' => 'Dashboard de progreso',
                'responses' => [
                    '200' => [
                        'description' => 'Metricas de progreso',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'student' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'current_weight_kg' => ['type' => ['number', 'null']],
                                                        'height_cm' => ['type' => ['number', 'null']],
                                                        'imc' => ['type' => ['number', 'null']],
                                                    ],
                                                ],
                                                'workouts' => ['type' => 'object', 'additionalProperties' => true],
                                                'weight' => ['type' => 'object', 'additionalProperties' => true],
                                                'progress' => ['type' => 'object', 'additionalProperties' => true],
                                                'recent_workouts' => ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => true]],
                                            ],
                                            'additionalProperties' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/progress' => [
            'get' => [
                'summary' => 'Resumen de progreso',
                'responses' => [
                    '200' => [
                        'description' => 'Resumen del plan activo',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'has_active_plan' => ['type' => 'boolean'],
                                                'message' => ['type' => ['string', 'null']],
                                                'plan_name' => ['type' => ['string', 'null']],
                                                'plan_starts_at' => ['type' => ['string', 'null']],
                                                'plan_ends_at' => ['type' => ['string', 'null']],
                                                'total_plan_days' => ['type' => ['integer', 'null']],
                                                'current_cycle' => ['type' => ['integer', 'null']],
                                                'next_plan_day' => ['type' => ['integer', 'null']],
                                                'progress' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                                                'current_cycle_complete' => ['type' => ['boolean', 'null']],
                                            ],
                                            'additionalProperties' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/progress/recent' => [
            'get' => [
                'summary' => 'Ultimos workouts completados',
                'parameters' => [
                    [
                        'name' => 'limit',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
                        'description' => 'Cantidad de resultados (default 10)',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de workouts completados',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Workout']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/payments' => [
            'get' => [
                'summary' => 'Dashboard de pagos',
                'responses' => [
                    '200' => [
                        'description' => 'Datos de pagos',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'student' => ['type' => 'object', 'additionalProperties' => true],
                                                'pending_invoice' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                                                'payment_methods' => ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => true]],
                                                'payment_info' => ['type' => 'object', 'additionalProperties' => true],
                                                'invoices_history' => ['type' => 'object', 'additionalProperties' => true],
                                                'commercial_plan' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                                            ],
                                            'additionalProperties' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/messages/conversation' => [
            'get' => [
                'summary' => 'Conversacion alumno-entrenador',
                'parameters' => [
                    [
                        'name' => 'per_page',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Mensajes por pagina (default 50)',
                        'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 200],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Conversacion y mensajes paginados',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'conversation' => ['$ref' => '#/components/schemas/Conversation'],
                                        'messages' => ['$ref' => '#/components/schemas/PaginatedMessages'],
                                    ],
                                    'required' => ['conversation', 'messages'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/messages/send' => [
            'post' => [
                'summary' => 'Enviar mensaje',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['body'],
                                'properties' => [
                                    'body' => ['type' => 'string'],
                                    'attachments' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'required' => ['path', 'name', 'mime', 'size'],
                                            'properties' => [
                                                'path' => ['type' => 'string'],
                                                'name' => ['type' => 'string'],
                                                'mime' => ['type' => 'string'],
                                                'size' => ['type' => 'integer', 'minimum' => 1],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Mensaje creado (compatibilidad)',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/Message'],
                            ],
                        ],
                    ],
                    '201' => [
                        'description' => 'Mensaje creado',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/Message'],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/messages/read' => [
            'post' => [
                'summary' => 'Marcar conversacion como leida',
                'requestBody' => null,
                'responses' => [
                    '200' => [
                        'description' => 'OK',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                    ],
                                    'required' => ['message'],
                                ],
                                'example' => ['message' => 'Marked as read'],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/messages/mute' => [
            'post' => [
                'summary' => 'Silenciar o activar notificaciones de la conversacion',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['mute'],
                                'properties' => [
                                    'mute' => ['type' => 'boolean'],
                                ],
                            ],
                            'example' => ['mute' => true],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Estado actualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                    ],
                                    'required' => ['message'],
                                ],
                                'example' => ['message' => 'Mute status updated'],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        '/messages/unread-count' => [
            'get' => [
                'summary' => 'Cantidad de mensajes no leidos',
                'responses' => [
                    '200' => [
                        'description' => 'Conteo',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'count' => ['type' => 'integer', 'minimum' => 0],
                                    ],
                                    'required' => ['count'],
                                ],
                                'example' => ['count' => 3],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
