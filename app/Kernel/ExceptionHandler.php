<?php

namespace App\Kernel;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

class ExceptionHandler
{
    private $logger; // Logger instance (not used in this code, could be used for logging exceptions)
    protected $whoops; // Instance of Whoops for detailed error reporting

    /**
     * ExceptionHandler constructor.
     * Initializes the Whoops error handler instance.
     */
    public function __construct()
    {
        $this->whoops = new Run(); // Instantiate Whoops Run object
    }

    /**
     * Handles the exception.
     * Displays detailed error information in development or production based on configuration.
     *
     * @param \Throwable $exception The exception that was thrown.
     * @return \Symfony\Component\HttpFoundation\Response The JSON response with error details.
     */
    public function handle(\Throwable $exception)
    {
        // Check if 'render_error' is set to true in the request to display detailed error messages (useful for development)
        if (filter_var(@$_REQUEST["render_error"], FILTER_VALIDATE_BOOL)) {
            $this->whoops->pushHandler(new PrettyPageHandler()); // Add PrettyPageHandler to format the error output nicely
            $this->whoops->handleException($exception); // Handle the exception with Whoops
        } else {
            // If 'render_error' is false or not present, return a custom JSON error response
            $errorDetail = $this->getErrorMessage($exception); // Get the structured error message
            return response()->json([
                "data" => null, // No data
                "meta" => null, // No meta data
                "error" => [
                    "message" => $errorDetail["message"], // Error message to show to the user
                    "stacks" => @$_ENV["APP_ENV"] === "local" ? [ // Show detailed stack trace if environment is 'local'
                        [
                            "type" => get_class($exception), // Exception type (class name)
                            "trace" => $exception->getMessage(), // Exception message
                            "file" => $exception->getFile(), // File where the exception occurred
                            "line" => $exception->getLine(), // Line number where the exception occurred
                        ]
                    ] : null // Hide stack trace in production environments
                ]
            ], $errorDetail["code"]); // Send the response with the appropriate HTTP status code
        }
    }

    /**
     * Extracts a structured error message from a Throwable.
     * Determines error message and HTTP status code based on the exception message.
     *
     * @param \Throwable $throwable The exception or error that was thrown.
     * @return array An associative array with the error message and the corresponding HTTP status code.
     */
    private function getErrorMessage(\Throwable $throwable)
    {
        // Handle specific error patterns like SQL errors
        if (preg_match('/SQLSTATE\[.*\]\:(.*)\:.*\sERROR/', $throwable->getMessage(), $extractMessage)) {
            return [
                "message" => trim($extractMessage[1]), // Extracted SQL error message
                "code" => 500, // Internal Server Error code
            ];
        }
        // Handle case for "No query results" errors
        elseif (preg_match('/(No query results).*\[.*\]/', $throwable->getMessage(), $extractMessage)) {
            return [
                "message" => trim($extractMessage[1]), // Extracted 'No query results' message
                "code" => 404, // Not Found error code
            ];
        }
        else {
            // General error handling: use the exception's message and code, defaulting to 500 if the code is not a valid HTTP status code
            return [
                "message" => $throwable->getMessage(), // Exception message
                "code" => preg_match('/^(?:4|5)\d{2}$/', $throwable->getCode()) ? $throwable->getCode() : 500, // Valid HTTP code or default to 500
            ];
        }
    }
}
