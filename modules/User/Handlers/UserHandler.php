<?php
namespace Modules\User\Handlers;

class UserHandler
{
    public function ping()
    {
        $data = [
            "ping" => "pong"
        ];

        return $data;
    }
    public function profile()
    {
        $data = [
            "username" => "cacing69"
        ];

        return $data;
    }

    public function check($id)
    {
        // Logika untuk mengambil pengguna berdasarkan ID
        return ['id' => $id];
    }
}
