<?php
use Symfony\Component\VarDumper\VarDumper;


if (!function_exists('dd')) {
    function dd(...$vars)
    {
       foreach ($vars as $var) {
            VarDumper::dump($var); // Gunakan VarDumper untuk menampilkan variable
        }
        die(); // Hentikan eksekusi
    }
}

/**
 * Membuat respons JSON.
 *
 * @param mixed $data
 * @param int $statusCode
 */
function response() {
    return new class {
        /**
         * Membuat respons JSON dengan data, status code, dan header custom
         *
         * @param array|object $data Data yang akan dikonversi ke JSON
         * @param int $statusCode Status HTTP Code (default 200)
         * @param array $headers Headers tambahan
         * @return void
         */
            public function json($data, $statusCode = 200, array $headers = [])
        {
            // Set Content-Type ke JSON
            header('Content-Type: application/json');

            // Set status code HTTP
            http_response_code($statusCode);

            // Set headers tambahan
            foreach ($headers as $key => $value) {
                header("{$key}: {$value}");
            }

            // Tampilkan JSON
            echo json_encode($data);
            exit; // Pastikan untuk menghentikan eksekusi setelah respons
        }
    };
}
