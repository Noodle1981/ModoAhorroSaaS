<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_settings')->insert([
            // Le da una preferencia al usuario con ID 1
            'user_id' => 1, 
            'key' => 'notification_channel',
            'value' => 'email'
        ]);
    }
}