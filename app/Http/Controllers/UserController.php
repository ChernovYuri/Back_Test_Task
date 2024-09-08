<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // v1
    use ResponseTrait;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Просмотр списка пользователей с фильтрацией и сортировкой
     */
    public function index(Request $request)
    {
        $filters = $request->only(['id', 'last_name', 'name', 'middle_name', 'email', 'phone']);
        $sort = $request->only(['sort_by', 'sort_order']);

        $users = $this->userService->getUsers($filters, $sort);

        return $this->respond($users);
    }

    /**
     * Получение пользователя
     */
    public function show(Request $request, $id)
    {
        $user = $this->userService->getUserById($id);

        return $this->respond($user);
    }

    /**
     * Редактирование пользователя
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:40',
            'name' => 'required|string|max:40',
            'middle_name' => 'required|string|max:40|nullable',
            'email' => 'required|email|max:80|unique:users,email,'.$id,
            'phone' => 'required|string|max:20|nullable',
        ]);

        $user = $this->userService->updateUser($id, $validated);

        return $this->respond($user);
    }

    /**
     * Удаление пользователя в корзину
     */
    public function softDelete($id)
    {
        $this->userService->softDeleteUser($id);

        return $this->respond(['message' => 'User soft deleted successfully.']);
    }

    /**
     * Просмотр списка пользователей в корзине
     */
    public function deleted()
    {
        $deletedUsers = $this->userService->getDeletedUsers();

        return $this->respond($deletedUsers);
    }

    /**
     * Восстановление пользователя из корзины
     */
    public function restore($id)
    {
        $this->userService->restoreUser($id);

        return $this->respond(['message' => 'User restored successfully.']);
    }

    /**
     * Восстановление пользователя таблицы histories
     */
    public function restoreFromHistories($id)
    {
        $history = History::findOrFail($id);
        $userModel = $history->model_name::findOrFail($history->model_id);
        $userModel->fill($history->before);
        $userModel->save();

        $this->userService->restoreUserFromHistories($id);

        return $this->respond(['message' => 'User restored successfully from history.']);
    }

    /**
     * Полное удаление пользователя из БД
     */
    public function hardDelete($id)
    {
        $this->userService->hardDeleteUser($id);

        return $this->respond(['message' => 'User permanently deleted successfully.']);
    }

    /**
     * Групповое удаление пользователей в корзину
     */
    public function softDeleteGroup(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->userService->softDeleteUsers($ids);

        return $this->respond(['message' => 'Users soft deleted successfully.']);
    }

    /**
     * Групповое полное удаление пользователей из БД
     */
    public function hardDeleteGroup(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->userService->hardDeleteUsers($ids);

        return $this->respond(['message' => 'Users permanently deleted successfully.']);
    }

    /**
     * Групповое восстановление пользователей из корзины
     */
    public function restoreGroup(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->userService->restoreUsers($ids);

        return $this->respond(['message' => 'Users restored successfully.']);
    }
}
