<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $jumlahKaryawan = 5;
        
        for ($i = 0; $i < $jumlahKaryawan; $i++) {
            $userId = DB::table('users')->insertGetId([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'is_admin' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::table('user_details')->insert([
                'user_id' => $userId,
                'kontak' => $faker->numberBetween(800000000, 899999999),
                'alamat' => $faker->address,
                'tgl_aktif' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
