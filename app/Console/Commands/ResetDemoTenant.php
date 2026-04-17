<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Models\Tenant;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationParticipant;
use App\Models\Tenant\Exercise;
use App\Models\Tenant\ExerciseCompletionLog;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Message;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentGamificationProfile;
use App\Models\Tenant\StudentPlanAssignment;
use App\Models\Tenant\StudentWeightEntry;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Workout;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ResetDemoTenant extends Command
{
    protected $signature = 'demo:reset-tenant {tenant? : Tenant ID a resetear}';

    protected $description = 'Resetea solo datos demo del tenant: ejercicios, alumnos, contactos web y chats con alumnos.';

    public function handle(): int
    {
        if (! config('demo.enabled')) {
            $this->warn('DEMO_MODE está deshabilitado. No se ejecuta el reset.');
            return self::SUCCESS;
        }

        $tenantId = (string) ($this->argument('tenant') ?: config('demo.tenant_id', 'demo'));
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            $this->error("No se encontró el tenant demo [{$tenantId}].");
            return self::FAILURE;
        }

        $this->info("Reseteando datos demo del tenant [{$tenantId}]...");

        $exitCode = $tenant->run(function () use ($tenantId) {
            DB::beginTransaction();

            try {
                $this->resetContacts();
                $this->resetStudentChats();
                $this->resetStudentData();
                $this->resetExercisesAndPlans();

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }

            foreach ([
                \Database\Seeders\Tenant\RoleAndPermissionSeeder::class,
                \Database\Seeders\Tenant\UserSeeder::class,
                \Database\Seeders\Tenant\StudentSeeder::class,
                \Database\Seeders\Tenant\ExerciseAndPlanSeeder::class,
            ] as $seederClass) {
                $code = Artisan::call('db:seed', [
                    '--database' => 'tenant',
                    '--class' => $seederClass,
                    '--force' => true,
                ]);
                $this->output->write(Artisan::output());

                if ($code !== 0) {
                    return $code;
                }
            }

            $this->info("Tenant demo [{$tenantId}] reseteado correctamente.");

            return self::SUCCESS;
        });

        return (int) $exitCode;
    }

    private function resetContacts(): void
    {
        $this->line('Limpiando contactos web...');
        Contact::query()->withTrashed()->forceDelete();
    }

    private function resetStudentChats(): void
    {
        $this->line('Limpiando chats con alumnos...');

        Message::query()->withTrashed()->forceDelete();
        ConversationParticipant::query()->delete();
        Conversation::query()->withTrashed()->forceDelete();
    }

    private function resetStudentData(): void
    {
        $this->line('Limpiando alumnos y datos asociados...');

        $studentUserIds = Student::query()
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->all();

        Invoice::query()->withTrashed()->forceDelete();
        ExerciseCompletionLog::query()->delete();
        StudentGamificationProfile::query()->delete();
        StudentWeightEntry::query()->delete();
        Workout::query()->withTrashed()->forceDelete();
        StudentPlanAssignment::query()->delete();
        Student::query()->withTrashed()->forceDelete();

        DB::table(config('permission.table_names.model_has_roles'))
            ->where('model_type', User::class)
            ->whereIn('model_id', $studentUserIds)
            ->delete();

        DB::table(config('permission.table_names.model_has_permissions'))
            ->where('model_type', User::class)
            ->whereIn('model_id', $studentUserIds)
            ->delete();

        if (! empty($studentUserIds)) {
            User::query()->whereIn('id', $studentUserIds)->delete();
        }

        $demoUserEmails = [
            'admin@fittrack.com.ar',
            'asistente@fittrack.com.ar',
            'demo_admin@fittrack.com.ar',
            'demo_student@fittrack.com.ar',
        ];

        $demoUserIds = User::query()
            ->whereIn('email', $demoUserEmails)
            ->pluck('id')
            ->all();

        DB::table(config('permission.table_names.model_has_roles'))
            ->where('model_type', User::class)
            ->whereIn('model_id', $demoUserIds)
            ->delete();

        DB::table(config('permission.table_names.model_has_permissions'))
            ->where('model_type', User::class)
            ->whereIn('model_id', $demoUserIds)
            ->delete();

        User::query()->whereIn('email', $demoUserEmails)->delete();
    }

    private function resetExercisesAndPlans(): void
    {
        $this->line('Limpiando ejercicios y planes...');

        TrainingPlan::query()->withTrashed()->forceDelete();
        Exercise::query()->withTrashed()->forceDelete();
    }
}
