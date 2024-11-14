<?php
namespace Repository;

use Models\User;
class AuthRepository
{
    public function check($email, $password)
    {
        $user = User::where("email", $email)->first();

        if (!password_verify($password, $user->password)) {
            throw new \Exception("wrong user credentials", 400);
        }

        return $user;
    }
}
