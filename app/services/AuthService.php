<?php

namespace App\Services;

class AuthService
{
    public function registerUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    public function resetUserPassword(array $data)
    {
        return Password::reset($data, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        });
    }
}
