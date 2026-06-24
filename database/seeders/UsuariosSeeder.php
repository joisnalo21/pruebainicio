<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    /**
     * Usuarios reales del sistema. Idempotente: updateOrCreate por 'username'
     * no duplica y restaura el usuario si algo lo borró.
     * 'password' va en texto plano: el cast 'hashed' del modelo lo cifra.
     */
    public function run(): void
    {
        $password = 'Hospital2025*'; // cámbiala (y actualízala en los tests Dusk)

        $usuarios = [
            ['username' => 'admin',      'name' => 'Administrador', 'role' => 'admin'],
            ['username' => 'drnavia',    'name' => 'Dr. Navia',     'role' => 'medico'],
            ['username' => 'enfermera1', 'name' => 'Enfermera Uno',  'role' => 'enfermero'],
        ];

        foreach ($usuarios as $u) {
            User::updateOrCreate(
                ['username' => $u['username']],
                [
                    'name'     => $u['name'],
                    'email'    => $u['username'].'@hospital.test',
                    'role'     => $u['role'],
                    'password' => $password,
                ]
            );
        }
    }
}