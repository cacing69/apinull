<?php
namespace Repository;

use Models\User;
use PhpParser\Node\Stmt\TryCatch;
class UserRepository {
    public function save($data)
    {
        $user = new User();

        // try {
            $user->email = $data["email"];
            // $user->name = $data["name"];
            $user->password = $data["password"];
            $user->save();
        // } catch (\Throwable $th) {
        //     throw new \Exception($th->getMessage(), 400);

        // }

        return $user;
    }
}
