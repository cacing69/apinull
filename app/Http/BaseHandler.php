<?php

namespace App\Http;

use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory as ValidatorFactory;

/**
 * Class BaseHandler
 *
 * The BaseHandler class serves as a foundational class for handling validation
 * and logging functionalities within the application. It uses the Laravel
 * translation and validation components to handle validation rules and error messages.
 * This class is typically extended or instantiated to provide validation
 * capabilities in the application.
 *
 * It initializes a custom validation loader, translator, and validator,
 * and includes methods for setting up a logger and validating request data.
 *
 * @package App\Http
 */
class BaseHandler
{
    /**
     * @var ValidatorFactory The validator instance used for validating data.
     */
    protected $validator;

    /**
     * @var Logger The logger instance for logging errors and other data (currently not implemented).
     */
    protected $logger;

    /**
     * BaseHandler constructor.
     *
     * The constructor initializes the logger, sets up the translation loader
     * for validation messages, and creates a validator factory instance.
     * It loads custom validation error messages into the loader, which are
     * used for validating data based on a set of rules.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setupLogger();

        // Initialize the loader and translator
        $loader = new ArrayLoader();

        // Add custom validation messages into the array loader
        $loader->addMessages('en', 'validation', [
            'accepted' => ':attribute must be accepted.',
            'active_url' => ':attribute is not a valid URL.',
            'after' => ':attribute must be a date after :date.',
            'after_or_equal' => ':attribute must be a date after or equal to :date.',
            'alpha' => ':attribute may only contain letters.',
            'alpha_dash' => ':attribute may only contain letters, numbers, dashes and underscores.',
            'alpha_num' => ':attribute may only contain letters and numbers.',
            'array' => ':attribute must be an array.',
            'before' => ':attribute must be a date before :date.',
            'before_or_equal' => ':attribute must be a date before or equal to :date.',
            'between' => [
                'numeric' => ':attribute must be between :min and :max.',
                'file' => ':attribute must be between :min and :max kilobytes.',
                'string' => ':attribute must be between :min and :max characters.',
                'array' => ':attribute must have between :min and :max items.',
            ],
            'boolean' => ':attribute must be true or false.',
            'confirmed' => ':attribute confirmation does not match.',
            'date' => ':attribute is not a valid date.',
            'date_equals' => ':attribute must be a date equal to :date.',
            'date_format' => ':attribute does not match the format :format.',
            'different' => ':attribute and :other must be different.',
            'digits' => ':attribute must be :digits digits.',
            'digits_between' => ':attribute must be between :min and :max digits.',
            'dimensions' => ':attribute has invalid image dimensions.',
            'distinct' => ':attribute has a duplicate value.',
            'email' => ':attribute must be a valid email address.',
            'ends_with' => ':attribute must end with one of the following: :values',
            'exists' => 'The selected :attribute is invalid.',
            'file' => ':attribute must be a file.',
            'filled' => ':attribute must have a value.',
            'gt' => [
                'numeric' => ':attribute must be greater than :value.',
                'file' => ':attribute must be greater than :value kilobytes.',
                'string' => ':attribute must be greater than :value characters.',
                'array' => ':attribute must have more than :value items.',
            ],
            'gte' => [
                'numeric' => ':attribute must be greater than or equal :value.',
                'file' => ':attribute must be greater than or equal :value kilobytes.',
                'string' => ':attribute must be greater than or equal :value characters.',
                'array' => ':attribute must have :value items or more.',
            ],
            'image' => ':attribute must be an image.',
            'in' => 'The selected :attribute is invalid.',
            'in_array' => ':attribute does not exist in :other.',
            'integer' => ':attribute must be an integer.',
            'ip' => ':attribute must be a valid IP address.',
            'ipv4' => ':attribute must be a valid IPv4 address.',
            'ipv6' => ':attribute must be a valid IPv6 address.',
            'json' => ':attribute must be a valid JSON string.',
            'lt' => [
                'numeric' => ':attribute must be less than :value.',
                'file' => ':attribute must be less than :value kilobytes.',
                'string' => ':attribute must be less than :value characters.',
                'array' => ':attribute must have less than :value items.',
            ],
            'lte' => [
                'numeric' => ':attribute must be less than or equal :value.',
                'file' => ':attribute must be less than or equal :value kilobytes.',
                'string' => ':attribute must be less than or equal :value characters.',
                'array' => ':attribute must not have more than :value items.',
            ],
            'max' => [
                'numeric' => ':attribute may not be greater than :max.',
                'file' => ':attribute may not be greater than :max kilobytes.',
                'string' => ':attribute may not be greater than :max characters.',
                'array' => ':attribute may not have more than :max items.',
            ],
            'mimes' => ':attribute must be a file of type: :values.',
            'mimetypes' => ':attribute must be a file of type: :values.',
            'min' => [
                'numeric' => ':attribute must be at least :min.',
                'file' => ':attribute must be at least :min kilobytes.',
                'string' => ':attribute must be at least :min characters.',
                'array' => ':attribute must have at least :min items.',
            ],
            'not_in' => 'The selected :attribute is invalid.',
            'not_regex' => ':attribute format is invalid.',
            'numeric' => ':attribute must be a number.',
            'present' => ':attribute must be present.',
            'regex' => ':attribute format is invalid.',
            'required' => ':attribute is required.',
            'required_if' => ':attribute is required when :other is :value.',
            'required_unless' => ':attribute is required unless :other is in :values.',
            'required_with' => ':attribute is required when :values is present.',
            'required_with_all' => ':attribute is required when :values are present.',
            'required_without' => ':attribute is required when :values is not present.',
            'required_without_all' => ':attribute is required when none of :values are present.',
            'same' => ':attribute and :other must match.',
            'size' => [
                'numeric' => ':attribute must be :size.',
                'file' => ':attribute must be :size kilobytes.',
                'string' => ':attribute must be :size characters.',
                'array' => ':attribute must contain :size items.',
            ],
            'starts_with' => ':attribute must start with one of the following: :values',
            'string' => ':attribute must be a string.',
            'timezone' => ':attribute must be a valid zone.',
            'unique' => ':attribute has already been taken.',
            'uploaded' => ':attribute failed to upload.',
            'url' => ':attribute format is invalid.',
            'uuid' => ':attribute must be a valid UUID.',
            'custom' => [
                'attribute-name' => [
                    'rule-name' => 'custom-message',
                ],
            ],
            'attributes' => [],
        ]);

        $translator = new Translator($loader, 'en');

        $this->validator = new ValidatorFactory($translator);
    }

    /**
     * Sets up the logger instance.
     *
     * This function initializes the logger for the application, which is used
     * to log errors and other relevant information (currently commented out
     * in this example).
     *
     * @return void
     */
    protected function setupLogger()
    {
        // Initialize logger
    }

    /**
     * Validates the given data against a set of rules.
     *
     * This method uses the validator instance to validate the provided data
     * and return an error response if the validation fails.
     *
     * @param array $data The data to be validated.
     * @param array $rules The validation rules.
     * @return \Illuminate\Http\JsonResponse|null The response in case of validation failure or null.
     */
    protected function validate(array $data, array $rules)
    {
        // Create validator instance
        $validator = $this->validator->make($data, $rules);

        if ($validator->fails()) {
            // Return validation errors in a JSON response
            return response()->json([
                "data" => null,
                "meta" => null,
                "error" => [
                    "message" => "Error unprocessable entity",
                    "stacks" => $validator->errors()->all()
                ]
            ], 422);
        }
    }
}
