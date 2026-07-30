<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestFirebase extends Command
{
    protected $signature = 'app:test-firebase';
    protected $description = 'Test Firebase connection';

    public function handle()
    {
        try {
            $this->info('Testing Firebase connection...');

            // Kalau pakai kreait/laravel-firebase (recommended)
            $auth = app('firebase.auth');

            // Test ambil 1 user
            $users = $auth->listUsers(1);

            foreach ($users as $user) {
                $this->info('✅ Connected! User UID: ' . $user->uid);
                return Command::SUCCESS;
            }

            $this->line('Credential path: ' . config('firebase.projects.app.credentials'));

            $this->info('✅ Connected! Tidak ada user di Firebase.');
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('❌ Firebase connection failed!');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}