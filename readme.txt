php artisan tinker



use App\Models\User;

User::create([
    'username' => 'admin',
    'name' => 'Administrador General',
    'password' => bcrypt('admin123'),
    'role' => 'admin'
]);

User::create([
    'username' => 'drnavia',
    'name' => 'Dr. Josué Navia',
    'password' => bcrypt('medico123'),
    'role' => 'medico'
]);

User::create([
    'username' => 'enfermera1',
    'name' => 'Enfermera María',
    'password' => bcrypt('enfermera123'),
    'role' => 'enfermero'
]);
