<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Публичные маршруты (доступны всем не авторизованным пользователям)
Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']); // Регистрация пользователя
    Route::post('login', [AuthController::class, 'login']); // Авторизация пользователя
    Route::post('password/reset', [AuthController::class, 'resetPassword']); // Сброс пароля пользователя
});

// Приватные маршруты (доступны только после авторизации пользователя)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::middleware('auth:sanctum')->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']); // Просмотр списка пользователей
        Route::get('/{id}', [UserController::class, 'show']); // Просмотр пользователя
        Route::put('/{id}', [UserController::class, 'update']); // Редактирование пользователя

        // Приватные маршруты для корзины и удалений
        Route::delete('/{id}/soft-delete', [UserController::class, 'softDelete']); // Удаление пользователя в корзину
        Route::get('/soft-deleted', [UserController::class, 'deleted']); // Просмотр списка пользователей в корзине
        Route::post('/{id}/restore', [UserController::class, 'restore']); // Восстановление пользователя из корзины
        Route::delete('/{id}/hard-delete', [UserController::class, 'hardDelete']); // Полное удаление пользователя из БД
        // Групповые
        Route::delete('/soft-delete-group', [UserController::class, 'softDeleteGroup']); // Групповое удаление пользователей в корзину
        Route::delete('/hard-delete-group', [UserController::class, 'hardDeleteGroup']); // Групповое удаление пользователей из БД
        Route::post('/restore-group', [UserController::class, 'restoreGroup']); // Групповое восстановление пользователей из корзины
    });
});
