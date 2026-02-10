<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class AdminUsuariosControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_index_filters_by_buscar(): void
    {
        $admin = $this->createUser('admin');

        $target = User::factory()->create([
            'username' => 'johnny',
            'name' => 'John Doe',
            'email' => 'johnny@example.com',
            'role' => 'medico',
        ]);
        User::factory()->create([
            'username' => 'other',
            'name' => 'Other User',
            'email' => 'other@example.com',
            'role' => 'enfermero',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/usuarios?buscar=johnny');

        $response->assertOk();
        $response->assertViewHas('usuarios', function ($usuarios) use ($target) {
            $ids = $usuarios->getCollection()->pluck('id')->all();
            return in_array($target->id, $ids, true) && count($ids) === 1;
        });
    }

    public function test_store_creates_user_and_hashes_password(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->post('/admin/usuarios', [
            'username' => 'newuser',
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'medico',
            'password' => 'secret12',
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/usuarios');
        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'role' => 'medico',
            'is_active' => 1,
        ]);

        $user = User::where('username', 'newuser')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('secret12', $user->password));
    }

    public function test_update_prevents_self_deactivation(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->put("/admin/usuarios/{$admin->id}", [
            'username' => $admin->username,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response->assertSessionHas('error');
        $admin->refresh();
        $this->assertTrue((bool) $admin->is_active);
    }

    public function test_update_prevents_removing_last_admin_role(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->put("/admin/usuarios/{$admin->id}", [
            'username' => $admin->username,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'medico',
            'is_active' => true,
        ]);

        $response->assertSessionHas('error');
        $admin->refresh();
        $this->assertSame('admin', $admin->role);
    }

    public function test_destroy_prevents_deleting_self_and_last_admin(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->delete("/admin/usuarios/{$admin->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_toggle_and_reset_password_for_other_user(): void
    {
        $admin = $this->createUser('admin');
        $user = $this->createUser('medico');
        $oldHash = $user->password;

        $toggle = $this->actingAs($admin)->patch("/admin/usuarios/{$user->id}/toggle");
        $toggle->assertSessionHas('success');
        $user->refresh();
        $this->assertFalse((bool) $user->is_active);

        $reset = $this->actingAs($admin)->post("/admin/usuarios/{$user->id}/reset-password");
        $reset->assertSessionHas('success');
        $user->refresh();
        $this->assertNotSame($oldHash, $user->password);
    }
}
