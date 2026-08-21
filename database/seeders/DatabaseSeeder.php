<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * ATTENTION SÉCURITÉ : la version précédente créait les comptes admin
     * avec le même mot de passe fixe 'Ajecb2024!', committé en clair dans le
     * dépôt Git. Si ce seeder tourne en production sans qu'on pense à
     * changer les mots de passe derrière, c'est une porte d'entrée triviale
     * pour n'importe qui ayant accès au code source.
     *
     * On génère désormais un mot de passe aléatoire par utilisateur à
     * chaque exécution, affiché une seule fois dans la sortie de la
     * commande. Il est à communiquer au titulaire du compte puis à changer
     * dès la première connexion (endpoint /auth/change-password).
     */
    public function run(): void
    {
        $membresBureau = [
            [
                'nom' => 'TOKANOU',
                'prenom' => 'Seyla Reynold',
                'email' => 'president@ajdcb.org',
                'role' => 'super_admin',
                'telephone' => '+22961234567',
            ],
            [
                'nom' => 'BASSADILA',
                'prenom' => 'Pergely Clesh Alverain',
                'email' => 'secretaire.general@ajdcb.org',
                'role' => 'admin',
                'telephone' => '+22961234568',
            ],
            [
                'nom' => 'GANGA',
                'prenom' => 'Adam Brel Guydalrich',
                'email' => 'tresorier@ajdcb.org',
                'role' => 'admin',
                'telephone' => '+22961234569',
            ],
        ];

        foreach ($membresBureau as $data) {
            $password = Str::password(16);

            User::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'password' => Hash::make($password),
                'role' => $data['role'],
                'telephone' => $data['telephone'],
                'est_actif' => true,
            ]);

            if ($this->command) {
                $this->command->warn(
                    "Compte créé : {$data['email']} — mot de passe temporaire : {$password} (à changer immédiatement)"
                );
            }
        }
    }
}