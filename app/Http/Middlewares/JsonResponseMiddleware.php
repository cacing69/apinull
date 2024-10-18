<?php

namespace App\Http\Middlewares;

class JsonResponseMiddleware
{
    public function handle($response)
    {
        // Cek apakah respons berupa array atau object, dan ubah ke JSON jika ya
        if (is_array($response) || is_object($response)) {
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            echo $response;
        }

        exit;
    }
}
