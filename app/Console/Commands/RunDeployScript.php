<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeployLog;
use Symfony\Component\Process\Process;

class RunDeployScript extends Command
{
    protected $signature = 'app:run-deploy';
    protected $description = 'Ejecuta el script de deploy-lnq.sh';

    public function handle(): int
    {
        $allowedEnvs = ['production', 'staging'];

        if (!in_array(app()->environment(), $allowedEnvs)) {
            $this->error("Este comando solo puede ejecutarse en: " . implode(', ', $allowedEnvs));
            return Command::FAILURE;
        }

        DeployLog::truncate();

        $startTime = now();

        $process = Process::fromShellCommandline('bash /var/repository/scripts/deploy-lnq.sh');
        $process->setTimeout(600);

        $partialBuffer = '';

        $process->run(function ($type, $buffer) use (&$partialBuffer) {
            $partialBuffer .= $buffer;

            while (str_contains($partialBuffer, "\n")) {
                [$line, $partialBuffer] = explode("\n", $partialBuffer, 2);

                $clean = preg_replace("/\e\[[0-9;]*[mK]/", '', $line);
                $clean = trim($clean);

                if ($clean !== '' && !preg_match('/^[\.\- ]+$/', $clean)) {
                    DeployLog::create(['message' => $clean]);
                }
            }
        });

        if (trim($partialBuffer) !== '') {
            $clean = preg_replace("/\e\[[0-9;]*[mK]/", '', $partialBuffer);
            $clean = trim($clean);

            if ($clean !== '' && !preg_match('/^[\.\- ]+$/', $clean)) {
                DeployLog::create(['message' => $clean]);
            }
        }

        $endTime = now();
        $duration = $startTime->diff($endTime);

        $formatted = $endTime->format('d-m-Y H:i:s');
        $timeMessage = sprintf(
            "🚀 Deploy finalizado — %s (Duración: %02d:%02d:%02d)",
            $formatted,
            $duration->h,
            $duration->i,
            $duration->s
        );

        DeployLog::create(['message' => $timeMessage]);

        return $process->isSuccessful() ? 0 : 1;
    }
}
