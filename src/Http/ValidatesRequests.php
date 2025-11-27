<?php

namespace NeoPhp\Http;

/**
 * Validates Requests Trait
 * 
 * Add this trait to controllers to enable validation methods
 */
trait ValidatesRequests
{
    /**
     * Validate the request data
     * 
     * @throws ValidationException
     */
    protected function validate(Request $request, array $rules, array $messages = []): array
    {
        $validator = validator($request->all(), $rules, $messages);

        if (!$validator->passes()) {
            throw new ValidationException(
                'The given data was invalid.',
                $validator->errors()
            );
        }

        return $validator->validated();
    }

    /**
     * Validate the request or return validation result
     */
    protected function validator(Request $request, array $rules, array $messages = []): \NeoPhp\Validation\Validator
    {
        return validator($request->all(), $rules, $messages);
    }

    /**
     * Create and validate a form request
     */
    protected function validateWith(string $formRequestClass, Request $request): array
    {
        $formRequest = new $formRequestClass($request);
        return $formRequest->validate();
    }
}
