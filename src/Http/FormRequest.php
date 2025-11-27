<?php

namespace NeoPhp\Http;

use NeoPhp\Validation\Validator;
use NeoPhp\Auth\Access\AuthorizationException;

/**
 * Form Request
 * 
 * Base class for form request validation and authorization
 */
abstract class FormRequest
{
    protected Request $request;
    protected array $validated = [];
    protected ?Validator $validator = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     */
    abstract public function rules(): array;

    /**
     * Get custom messages for validator errors
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Validate the request
     * 
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function validate(): array
    {
        // Check authorization first
        if (!$this->authorize()) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        // Get all input data
        $data = $this->all();

        // Create validator
        $this->validator = Validator::make($data, $this->rules(), $this->messages());

        // Run validation
        if (!$this->validator->passes()) {
            $this->failedValidation();
        }

        // Store validated data
        $this->validated = $this->validator->validated();

        return $this->validated;
    }

    /**
     * Handle a failed validation attempt
     * 
     * @throws \Exception
     */
    protected function failedValidation(): void
    {
        $errors = $this->validator->errors();
        
        throw new ValidationException('The given data was invalid.', $errors);
    }

    /**
     * Get the validated data from the request
     */
    public function validated(): array
    {
        if (empty($this->validated)) {
            $this->validate();
        }

        return $this->validated;
    }

    /**
     * Get the validator instance
     */
    public function getValidator(): ?Validator
    {
        return $this->validator;
    }

    /**
     * Get all input data
     */
    public function all(): array
    {
        return $this->request->all();
    }

    /**
     * Get input data by key
     */
    public function input(string $key, $default = null)
    {
        return $this->request->input($key, $default);
    }

    /**
     * Check if input exists
     */
    public function has(string $key): bool
    {
        return $this->request->has($key);
    }

    /**
     * Get only specific input fields
     */
    public function only(array $keys): array
    {
        return $this->request->only($keys);
    }

    /**
     * Get all input except specific fields
     */
    public function except(array $keys): array
    {
        return $this->request->except($keys);
    }

    /**
     * Get the user making the request
     */
    public function user(): ?object
    {
        return auth()->user();
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        //
    }

    /**
     * Configure the validator instance
     */
    protected function withValidator(Validator $validator): void
    {
        //
    }

    /**
     * Handle a passed validation attempt
     */
    protected function passedValidation(): void
    {
        //
    }

    /**
     * Get a specific validated value
     */
    public function validated(string $key, $default = null)
    {
        $data = $this->validated();
        return $data[$key] ?? $default;
    }

    /**
     * Merge additional data into validated data
     */
    public function merge(array $data): self
    {
        $this->validated = array_merge($this->validated(), $data);
        return $this;
    }

    /**
     * Get the request instance
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Magic method to proxy to request
     */
    public function __get(string $key)
    {
        return $this->request->$key;
    }

    /**
     * Magic method to check if property exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->request->$key);
    }
}
