<?php
namespace Modules\User\Handlers;

class UserHandler
{
    public function profile()
    {
        $data = [
            "name" => "cacing69"
        ];

        return $data;
    }

    public function check($id)
    {
        // Logika untuk mengambil pengguna berdasarkan ID
        return ['id' => $id];
    }
}
