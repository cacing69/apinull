<?php
namespace Repository;

use Models\User;
class UserRepository {
    public function save($data)
    {
        $user = new User();

        // try {
        $user->email = $data["email"];
        $user->name = $data["name"];
        $user->password = password_hash($data["password"], PASSWORD_BCRYPT);
        $user->save();
        return $user;
    }

    public function countUserByEmail($email)
    {
        return User::where("email", $email)->count();
    }
}
