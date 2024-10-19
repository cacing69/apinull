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
