<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $userService;

    protected function setUp(): void
    {
        parent::setUp();

        // Инициализируем UserService перед каждым тестом
        $this->userService = $this->app->make(UserService::class);

        // Мокаем вызовы Cache::remember() и Cache::forget()
        Cache::shouldReceive('remember')
            ->andReturnUsing(function ($key, $minutes, $callback) {
                return $callback();
            });

        Cache::shouldReceive('forget')
            ->andReturn(true);
    }

    public function testGetUserById()
    {
        // Создаем тестового пользователя
        $user = User::factory()->create();

        // Используем сервис для получения пользователя по ID
        $foundUser = $this->userService->getUserById($user->id);

        // Проверяем, что найденный пользователь совпадает с созданным
        $this->assertEquals($user->id, $foundUser->id);
    }

    public function testUpdateUser()
    {
        $user = User::factory()->create();

        $updatedData = [
            'name' => 'UpdatedName',
            'last_name' => 'UpdatedLastName',
            'email' => 'updated@example.com',
        ];

        $updatedUser = $this->userService->updateUser($user->id, $updatedData);

        $this->assertEquals('UpdatedName', $updatedUser->name);
        $this->assertEquals('UpdatedLastName', $updatedUser->last_name);
        $this->assertEquals('updated@example.com', $updatedUser->email);
    }

    public function testSoftDeleteUser()
    {
        $user = User::factory()->create();

        $this->userService->softDeleteUser($user->id);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function testHardDeleteUser()
    {
        $user = User::factory()->create();
        $this->userService->softDeleteUser($user->id);

        $this->userService->hardDeleteUser($user->id);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function testRestoreUser()
    {
        $user = User::factory()->create();
        $this->userService->softDeleteUser($user->id);

        $this->userService->restoreUser($user->id);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function testSoftDeleteUsers()
    {
        $users = User::factory()->count(3)->create();
        $ids = $users->pluck('id')->toArray();

        $this->userService->softDeleteUsers($ids);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('users', ['id' => $id]);
        }
    }

    public function testHardDeleteUsers()
    {
        $users = User::factory()->count(3)->create();
        $ids = $users->pluck('id')->toArray();

        $this->userService->softDeleteUsers($ids);
        $this->userService->hardDeleteUsers($ids);

        foreach ($ids as $id) {
            $this->assertDatabaseMissing('users', ['id' => $id]);
        }
    }

    public function testRestoreUsers()
    {
        $users = User::factory()->count(3)->create();
        $ids = $users->pluck('id')->toArray();

        $this->userService->softDeleteUsers($ids);
        $this->userService->restoreUsers($ids);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('users', ['id' => $id]);
        }
    }
}
