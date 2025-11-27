<?php

return [
    'welcome' => 'Welcome to NeoFramework',
    'hello' => 'Hello, :name!',
    'goodbye' => 'Goodbye, :name. See you soon!',
    
    'auth' => [
        'failed' => 'These credentials do not match our records.',
        'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
        'password_reset' => 'Your password has been reset!',
        'password_sent' => 'We have emailed your password reset link!',
    ],
    
    'validation' => [
        'required' => 'The :attribute field is required.',
        'email' => 'The :attribute must be a valid email address.',
        'min' => [
            'string' => 'The :attribute must be at least :min characters.',
        ],
        'max' => [
            'string' => 'The :attribute must not be greater than :max characters.',
        ],
    ],
    
    'errors' => [
        '404' => 'Page Not Found',
        '403' => 'Forbidden',
        '500' => 'Internal Server Error',
    ],
];
