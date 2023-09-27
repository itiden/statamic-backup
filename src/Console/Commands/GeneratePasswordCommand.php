<?php

declare(strict_types=1);

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;

class GeneratePasswordCommand extends Command
{

    protected $signature = 'statamic:backup:generate-password {--show}';

    protected $description = 'Generate a new random password';

    public function handle()
    {
        $password = $this->generateRandomPassword();

        if ($this->option('show')) {
            return $this->line('<comment>' . $password . '</comment>');
        }

        // Next, we will replace the application key in the environment file so it is
        // automatically setup for this developer. This key gets generated using a
        // secure random byte generator and is later base64 encoded for storage.
        if (!$this->writeNewEnvironmentFileWith($password)) {
            return;
        }

        $this->laravel['config']['backup.password'] = $password;

        $this->components->info('Backup password set successfully.');
    }

    protected function generateRandomPassword(): string
    {
        return 'base64:' . base64_encode(
            Encrypter::generateKey($this->laravel['config']['app.cipher'])
        );
    }

    protected function writeNewEnvironmentFileWith($key): bool
    {
        $escaped = preg_quote('=' . $this->laravel['config']['backup.password'], '/');

        $replaced = preg_replace(
            "/^BACKUP_PASSWORD{$escaped}/m",
            'BACKUP_PASSWORD=' . $key,
            $input = file_get_contents($this->laravel->environmentFilePath())
        );

        if ($replaced === $input || $replaced === null) {
            $this->error('Unable to set backup password. No BACKUP_PASSWORD variable was found in the .env file.');

            return false;
        }

        file_put_contents($this->laravel->environmentFilePath(), $replaced);

        return true;
    }
}
