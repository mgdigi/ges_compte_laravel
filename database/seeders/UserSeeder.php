<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\Compte;
use App\Models\Transaction;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'id' => Str::uuid(),
            'nom' => 'Gueye',
            'prenom' => 'Mohamed',
            'email' => 'admin@gmail.com',
            'telephone' => '780118223',
            'adresse' => 'Dakar, Sénégal',
            'nci' => '1767200700455',
            'password' => Hash::make('admin123'),
        ]);

        User::factory()
        ->has(Compte::factory())->has(Transaction::factory()->count(5))
        ->count(10)
        ->create();
    }
}
