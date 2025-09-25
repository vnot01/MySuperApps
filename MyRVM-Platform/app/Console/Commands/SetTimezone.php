<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class SetTimezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-timezone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the application timezone based on the server\'s IP address';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fetching timezone from ip-api.com...');

        try {
            $response = Http::get('http://ip-api.com/json');

            if ($response->successful() && $response->json('status') === 'success') {
                $timezone = $response->json('timezone');
                $this->info("Detected timezone: {$timezone}");

                if ($this->updateEnvFile('TIMEZONE', $timezone)) {
                    $this->info('Application timezone has been set successfully.');
                    $this->comment('Please clear the config cache by running: php artisan config:cache');
                } else {
                    $this->error('Failed to update the .env file.');
                }

            } else {
                $this->error('Failed to fetch timezone from ip-api.com.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Update the .env file with the new timezone.
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    protected function updateEnvFile(string $key, string $value): bool
    {
        $envFilePath = $this->laravel->basePath('.env');

        if (!File::exists($envFilePath)) {
            return false;
        }

        $content = File::get($envFilePath);
        $newContent = '';

        $keyExists = false;
        foreach (explode(PHP_EOL, $content) as $line) {
            if (str_starts_with($line, $key . '=')) {
                $newContent .= $key . '=' . $value . PHP_EOL;
                $keyExists = true;
            } else {
                $newContent .= $line . PHP_EOL;
            }
        }

        if (!$keyExists) {
            $newContent .= PHP_EOL . $key . '=' . $value . PHP_EOL;
        }
        
        // Trim trailing newlines before writing
        $newContent = rtrim($newContent);

        return File::put($envFilePath, $newContent) !== false;
    }
}