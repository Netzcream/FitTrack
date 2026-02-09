<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        Log::info('Login attempt', ['email' => $request->email]);

        // Validación básica
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        Log::info('Validation passed', $validated);

        /*
        |--------------------------------------------------------------------------
        | 1) Detectar automáticamente a qué tenant pertenece el usuario
        |--------------------------------------------------------------------------
        |
        | NO usamos data->users.
        | Recorremos todos los tenants y buscamos el usuario dentro de cada uno.
        | Cuando lo encontramos, listo: ya sabemos el tenant real.
        |
        */

        $tenant = null;
        $user   = null;

        foreach (Tenant::all() as $t) {
            Log::info('Checking tenant', ['tenant_id' => $t->id]);

            // Cambiar contexto al tenant
            tenancy()->initialize($t);

            // Buscar el usuario en la DB del tenant actual
            $u = \App\Models\User::where('email', $request->email)->first();
            Log::info('User search result', ['tenant_id' => $t->id, 'found' => $u ? true : false]);

            if ($u) {
                $tenant = $t;
                $user   = $u;
                Log::info('User found', ['tenant_id' => $t->id, 'user_id' => $u->id]);
                break;
            }

            tenancy()->end();
        }

        if (!$tenant || !$user) {
            tenancy()->end();
            Log::error('User not found in any tenant', ['email' => $request->email]);
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | 2) Validar contraseña dentro del tenant correcto
        |--------------------------------------------------------------------------
        */

        if (!Hash::check($request->password, $user->password)) {
            Log::error('Password mismatch', ['user_id' => $user->id]);
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 3) Crear token Sanctum dentro del tenant
        |--------------------------------------------------------------------------
        */

        $token = $user->createToken('mobile-app')->plainTextToken;

        /*
        |--------------------------------------------------------------------------
        | 4) Obtener datos del estudiante asociado al usuario
        |--------------------------------------------------------------------------
        */

        $student = \App\Models\Tenant\Student::where('email', $user->email)->first();

        /*
        |--------------------------------------------------------------------------
        | 5) Responder a la app móvil con datos completos
        |--------------------------------------------------------------------------
        */

        $scheme = $request->getScheme();
        if (!in_array($scheme, ['http', 'https'], true)) {
            $appUrlScheme = parse_url((string) config('app.url'), PHP_URL_SCHEME);
            if (is_string($appUrlScheme) && in_array($appUrlScheme, ['http', 'https'], true)) {
                $scheme = $appUrlScheme;
            } else {
                $scheme = app()->environment('local') ? 'http' : 'https';
            }
        }

        $response = [
            'tenant' => [
                'id'     => $tenant->id,
                'name'   => $tenant->name ?? $tenant->id,
                'domain' => "{$scheme}://{$tenant->mainDomain()}",
            ],
            'user' => [
                'id'    => $user->id,
                'email' => $user->email,
                'name'  => $user->name,
            ],
            'token' => $token,
        ];

        // Agregar datos del estudiante si existe
        if ($student) {
            $response['student'] = [
                'id'                  => $student->id,
                'uuid'                => $student->uuid,
                'email'               => $student->email,
                'first_name'          => $student->first_name,
                'last_name'           => $student->last_name,
                'full_name'           => $student->full_name,
                'phone'               => $student->phone,
                'goal'                => $student->goal,
                'status'              => $student->status,
                'timezone'            => $student->timezone,
                'current_level'       => $student->current_level,

                // Datos personales
                'birth_date'          => $student->birth_date,
                'gender'              => $student->gender,
                'height_cm'           => $student->height_cm,
                'weight_kg'           => $student->weight_kg,
                'imc'                 => $student->imc,

                // Datos de comunicación
                'language'            => $student->language,
                'notifications'       => $student->notifications,

                // Datos de entrenamiento (si existen en training_data)
                'training_experience' => $student->training_data['experience'] ?? null,
                'days_per_week'       => $student->training_data['days_per_week'] ?? null,
            ];
        }

        return response()->json($response);
    }

    public function logout(Request $request)
    {
        // El usuario ya debe estar autenticado (middleware auth:sanctum)
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }
}
