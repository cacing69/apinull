<?php
use Models\User;
use ParagonIE\Paseto\Keys\Version4\AsymmetricPublicKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Purpose;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\Yaml\Yaml;

/**
 * Retrieves the application path by concatenating the provided relative path with the base application path.
 *
 * @param string $path The relative path to be appended to the base application path.
 * @return string The full path to the application.
 */
if (!function_exists('app_path')) {
    function app_path($path = "")
    {
        if(strlen($path) === 0) {
            return APINULL_PATH; // Return the base application path if no path is provided.
        } else {
            return preg_replace('/\\'.DIRECTORY_SEPARATOR.'+/', DIRECTORY_SEPARATOR, APINULL_PATH.DIRECTORY_SEPARATOR.$path);
        }
    }
}

/**
 * Dumps variables for debugging and halts script execution.
 *
 * @param mixed ...$vars Variables to be dumped.
 */
if (!function_exists('dd')) {
    function dd(...$vars)
    {
       foreach ($vars as $var) {
            VarDumper::dump($var); // Display variables using VarDumper
        }
        die(); // Stop script execution after displaying the variables
    }
}

/**
 * Validates request data using YAML-based rules for a specific module and rule.
 *
 * @param string $module The name of the module for which validation rules are needed.
 * @param string $rule The validation rule (e.g., 'create', 'update').
 * @return array The validation rules converted to Laravel's validation format.
 */
if (!function_exists('yaml_request_validator')) {
    function yaml_request_validator($module, $rule)
    {
        $lowerName = strtolower($module);
        $rulesYaml = Yaml::parseFile(app_path("src/Modules/{$module}/Http/Validate/{$lowerName}.{$rule}.yaml")) ?? [];

        // Convert YAML rules to Laravel validation format
        $rulesConvert = convert_yaml_to_validate_laravel_rules($rulesYaml);

        return $rulesConvert;
    }
}

/**
 * Converts YAML validation rules to Laravel validation rules format.
 *
 * @param array $rules The validation rules in array format.
 * @return array The converted validation rules in Laravel format (using pipe '|' as separator).
 */
if (!function_exists('convert_yaml_to_validate_laravel_rules')) {
    function convert_yaml_to_validate_laravel_rules(array $rules)
    {
        $laravelRules = [];
        foreach ($rules as $field => $fieldRules) {
            $laravelRules[$field] = [];

            foreach ($fieldRules as $rule => $value) {
                if ($value === true) {
                    $laravelRules[$field][] = $rule; // If value is true, just add the rule name
                } elseif (is_numeric($value)) {
                    $laravelRules[$field][] = $rule . ':' . $value; // If there's a numeric value, add it to the rule
                }
            }

            // Join the rules with a pipe '|' separator
            $laravelRules[$field] = implode('|', $laravelRules[$field]);
        }

        return $laravelRules;
    }
}

/**
 * Generates a JSON response for errors.
 *
 * @param string $message The error message to be included in the response.
 * @param int $httpCode The HTTP status code (default: 400).
 * @return JsonResponse The JSON response with the error message.
 */
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

/**
 * Generates a JSON response for success.
 *
 * @param mixed $data The data to be included in the success response.
 * @param mixed $meta Optional metadata to be included in the response.
 * @param int $httpCode The HTTP status code (default: 200).
 * @return JsonResponse The JSON response with the success data.
 */
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
 * Retrieves the authenticated user's ID.
 *
 * @return mixed The ID of the currently authenticated user.
 */
if (!function_exists('auth_id')) {
    function auth_id()
    {
        return auth()->id;
    }
}

/**
 * Retrieves the authenticated user object based on the claims in the token from the Authorization header.
 *
 * @return User The authenticated user object.
 */
if (!function_exists('auth')) {
    function auth()
    {
        $claims = token_check(); // Validate and verify the token

        // Fetch the user based on the email claim in the token
        return User::where("email", $claims["email"])->firstOrFail();
    }
}

/**
 * Checks the token provided in the Authorization header.
 *
 * @return array|JsonResponse The token claims if valid, or an error response if the token is invalid.
 */
if (!function_exists('token_check')) {
    function token_check()
    {
        $header = getallheaders(); // Retrieve all headers

        if (!array_key_exists("Authorization", $header)) {
           return response_error("Authorization header missing", 401); // If the Authorization header is missing
        }

        $authHeader = $header["Authorization"];

        list($type, $token) = explode(" ", $authHeader, 2);

        if ($type !== 'Bearer') {
            return response_error("Invalid token type", 401); // If the token type is not 'Bearer'
        }

        $publicKey = new AsymmetricPublicKey(base64_decode($_ENV["PASETO_PUBLIC_KEY"])); // Retrieve the public key from environment

        try {
            // Verify the token using the public key
            $parser =  (new Parser())
            ->setKey($publicKey)
            ->setPurpose(Purpose::public())
            ->parse($token);

            $claims = $parser->getClaims(); // Retrieve the claims from the token

            return $claims;
        } catch (\Exception $e) {
            return response_error("Invalid token: " . $e->getMessage()); // Handle any errors if the token is invalid
        }
    }
}

/**
 * Creates a JSON response.
 *
 * @return object The response object with a json() method for flexible JSON responses.
 */
function response() {
    return new class {
        /**
         * Creates a JSON response with data, HTTP status code, and optional custom headers.
         *
         * @param array|object $data The data to be converted to JSON.
         * @param int $statusCode The HTTP status code (default 200).
         * @param array $headers Optional custom headers.
         * @return void The response is sent and execution stops.
         */
            public function json($data, $statusCode = 200, array $headers = [])
        {
            // Set the Content-Type header to application/json
            header('Content-Type: application/json');

            // Set the HTTP status code
            http_response_code($statusCode);

            // Add any custom headers if provided
            foreach ($headers as $key => $value) {
                header("{$key}: {$value}");
            }

            // Output the JSON data
            echo json_encode($data);
            exit; // Ensure that execution stops after sending the response
        }
    };
}
