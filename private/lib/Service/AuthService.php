<?php

namespace App\Service;

use App\Database\Exception\NotFoundException;
use App\Database\Repository\UserRepository;
use App\Utilities\Session;

class AuthService
{
    private const PASSWORD_HASH = '';

    public function register(): void
    {
    }

    public function login(string $username, string $password): void
    {
        if ($username === '' || $password === '') {
            throw new \RuntimeException('Username or Password was not provided');
        }

        $repository = new UserRepository();
        try {
            $user = $repository->getByUsername($username);
        } catch (NotFoundException $e) {
            throw new \RuntimeException('Username or Password is incorrect');
        }

        // also check if passwords match

        $encryptedPassword = encryptMyPassword($password);

        if ($user->getPassword() === $encryptedPassword) {
            Session::put('user', $user->getId());

            /// extra magic here for time (save id Db)
        }
    }
}