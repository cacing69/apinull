<?php
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\Yaml\Yaml;

if (!function_exists('app_path')) {
    function app_path($path = "")
    {
        if(strlen($path) === 0) {
            return APINULL_PATH;
        } else {
            return preg_replace('/\\'.DIRECTORY_SEPARATOR.'+/', DIRECTORY_SEPARATOR, APINULL_PATH.DIRECTORY_SEPARATOR.$path);
        }
    }
}

if (!function_exists('dd')) {
    function dd(...$vars)
    {
       foreach ($vars as $var) {
            VarDumper::dump($var); // Gunakan VarDumper untuk menampilkan variable
        }
        die(); // Hentikan eksekusi
    }
}

if (!function_exists('yaml_validator')) {
    function yaml_validator($module, $rule)
    {
        $lowerName = strtolower($module);
        $rulesYaml = Yaml::parseFile(app_path("src/Modules/{$module}/Http/Validate/{$lowerName}.{$rule}.yaml")) ?? [];

        $rulesConvert = convert_yaml_to_laravel_rules($rulesYaml);

        return $rulesConvert;
    }
}

if (!function_exists('convert_yaml_to_laravel_rules')) {
    function convert_yaml_to_laravel_rules(array $rules)
    {
        $laravelRules = [];
        foreach ($rules as $field => $fieldRules) {
            $laravelRules[$field] = [];

            foreach ($fieldRules as $rule => $value) {
                if ($value === true) {
                    $laravelRules[$field][] = $rule;
                } elseif (is_numeric($value)) {
                    $laravelRules[$field][] = $rule . ':' . $value;
                }
            }

            $laravelRules[$field] = implode('|', $laravelRules[$field]);
        }

        return $laravelRules;
    }
}

if (!function_exists('response_error')) {
    function response_error($message, $httpCode = 400)
    {
        return response()->json([
            "data" => null,
            "meta" => null,
            "error" => [
                "message" => $message,
                "stacks" => null
            ]
        ], $httpCode);
    }
}

if (!function_exists('response_success')) {
    function response_success($data, $meta = null, $httpCode = 200)
    {
        return response()->json([
            "data" => $data,
            "meta" => null,
            "error" => null
        ], $httpCode);
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
