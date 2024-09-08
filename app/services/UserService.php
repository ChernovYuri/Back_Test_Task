<?php

namespace App\Services;

use App\Models\History;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Логирование действий с пользователями
     */
    protected function logHistory(string $modelId, string $action, ?array $before = null, ?array $after = null): void
    {
        History::create([
            'model_id' => $modelId,
            'model_name' => User::class,
            'before' => json_encode($before),
            'after' => json_encode($after),
            'action' => $action,
        ]);
    }

    /**
     * Просмотр списка пользователей
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsers(array $filters = [], array $sort = [])
    {
        $query = User::query();

        // Применение фильтров
        foreach ($filters as $field => $value) {
            if (in_array($field, ['id', 'last_name', 'name', 'middle_name', 'email', 'phone'])) {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        // Применение сортировки
        if (! empty($sort['sort_by'])) {
            $direction = $sort['sort_order'] ?? 'asc';
            $query->orderBy($sort['sort_by'], $direction);
        }

        return $query->get();
    }

    /**
     * Просмотр пользователя
     */
    public function getUserById(string $id): User
    {
        return Cache::remember("user:{$id}", 60, function () use ($id) {
            return User::findOrFail($id);
        });
    }

    /**
     * Редактирование пользователя
     */
    public function updateUser(string $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::findOrFail($id);
            $before = $user->toArray(); // Состояние до обновления
            $user->update($data);
            $after = $user->toArray();  // Состояние после обновления
            $this->logHistory($id, 'updated', $before, $after);
            Cache::forget("user:{$id}");

            return $user;
        });
    }

    /**
     * Удаление пользователя в корзину
     */
    public function softDeleteUser(string $id): void
    {
        DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);
            $before = $user->toArray();
            $user->delete(); // Soft delete
            $this->logHistory($id, 'deleted soft', $before);
            Cache::forget("user:{$id}");
        });
    }

    /**
     * Просмотр списка пользователей в корзине
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDeletedUsers()
    {
        return User::onlyTrashed()->get();
    }

    /**
     * Восстановление пользователя из корзины
     */
    public function restoreUser(string $id): void
    {
        DB::transaction(function () use ($id) {
            $user = User::onlyTrashed()->findOrFail($id);
            $before = $user->toArray(); // Состояние до восстановления
            $user->restore(); // Восстановление из корзины
            $after = User::findOrFail($id)->toArray(); // Состояние после восстановления
            $this->logHistory($id, 'restored', $before, $after);
            Cache::forget("user:{$id}");
        });
    }

    /**
     * Восстановление пользователя из таблицы histories
     */
    public function restoreUserFromHistories(string $id): void
    {
        $history = History::findOrFail($id);
        $userModel = $history->model_name::findOrFail($history->model_id);
        $userModel->fill($history->before);
        $userModel->save();
    }

    /**
     * Полное удаление пользователя из БД
     */
    public function hardDeleteUser(string $id): void
    {
        DB::transaction(function () use ($id) {
            $user = User::onlyTrashed()->findOrFail($id);
            $before = $user->toArray();
            $user->forceDelete(); // hard delete
            $this->logHistory($id, 'deleted hard', $before);
            Cache::forget("user:{$id}");
        });
    }

    /**
     * Групповое удаление пользователей в корзину
     */
    public function softDeleteUsers(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $users = User::whereIn('id', $ids)->get();
            foreach ($users as $user) {
                $before = $user->toArray();
                $user->delete(); // Soft delete
                Cache::forget("user:{$user->id}");
                $this->logHistory($user->id, 'deleted soft', $before);
            }
        });
    }

    /**
     * Групповое полное удаление пользователей из БД
     */
    public function hardDeleteUsers(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $users = User::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($users as $user) {
                $before = $user->toArray();
                $user->forceDelete(); // hard delete
                Cache::forget("user:{$user->id}");
                $this->logHistory($user->id, 'deleted hard', $before);
            }
        });
    }

    /**
     * Групповое восстановление пользователей из корзины
     */
    public function restoreUsers(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $users = User::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($users as $user) {
                $before = $user->toArray(); // Состояние до восстановления
                $user->restore(); // Восстановление из корзины
                $after = User::findOrFail($user->id)->toArray(); // Состояние после восстановления
                $this->logHistory($user->id, 'restored', $before, $after);
                Cache::forget("user:{$user->id}");
            }
        });
    }
}
