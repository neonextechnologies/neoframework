<?php

namespace NeoPhp\Http;

use Exception;

/**
 * Validation Exception
 * 
 * Thrown when form validation fails
 */
class ValidationException extends Exception
{
    protected array $errors = [];

    public function __construct(string $message, array $errors = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    /**
     * Get the validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get the first error message
     */
    public function first(): ?string
    {
        $errors = $this->errors;
        
        if (empty($errors)) {
            return null;
        }

        $firstField = array_key_first($errors);
        $messages = $errors[$firstField];

        return is_array($messages) ? $messages[0] : $messages;
    }

    /**
     * Convert to JSON response
     */
    public function toResponse(): Response
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => $this->errors
        ], $this->getCode());
    }
}
