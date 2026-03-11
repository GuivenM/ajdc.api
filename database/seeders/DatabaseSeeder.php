<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Supprimer d'abord l'appel au factory qui cause l'erreur
        // \App\Models\User::factory(10)->create(); // COMMENTEZ OU SUPPRIMEZ CETTE LIGNE

        // Créer vos utilisateurs manuellement
        User::create([
            'nom' => 'TOKANOU',
            'prenom' => 'Reynold',
            'email' => 'president@ajecb.org',
            'password' => Hash::make('Ajecb2024!'),
            'role' => 'super_admin',
            'telephone' => '+22961234567',
            'est_actif' => true
        ]);

        User::create([
            'nom' => 'BASSADILA',
            'prenom' => 'Clesh',
            'email' => 'admin@ajecb.org',
            'password' => Hash::make('Ajecb2024!'),
            'role' => 'admin',
            'telephone' => '+22961234568',
            'est_actif' => true
        ]);

        User::create([
            'nom' => 'ONGANDZA',
            'prenom' => 'Dieuveil',
            'email' => 'moderateur@ajecb.org',
            'password' => Hash::make('Ajecb2024!'),
            'role' => 'moderateur',
            'telephone' => '+22961234569',
            'est_actif' => true
        ]);
    }
}