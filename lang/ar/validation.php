<?php

return [
    // Custom validation messages
    'required'        => 'The :attribute field is required.',
    'email'           => 'The :attribute must be a valid email address.',
    'min'             => [
        'string' => 'The :attribute must be at least :min characters.',
        'numeric' => 'The :attribute must be at least :min.',
    ],
    'max'             => [
        'string' => 'The :attribute may not be greater than :max characters.',
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
    ],
    'confirmed'       => 'The :attribute confirmation does not match.',
    'unique'          => 'The :attribute has already been taken.',
    'numeric'         => 'The :attribute must be a number.',
    'integer'         => 'The :attribute must be an integer.',
    'in'              => 'The selected :attribute is invalid.',
    'url'             => 'The :attribute must be a valid URL.',
    'image'           => 'The :attribute must be an image.',
    'mimes'           => 'The :attribute must be a file of type: :values.',
    'size'            => [
        'file' => 'The :attribute must be :size kilobytes.',
    ],
    'between'         => [
        'numeric' => 'The :attribute must be between :min and :max.',
    ],
    'password'        => [
        'min' => 'Password must be at least :min characters.',
    ],

    // Custom attribute names
    'attributes'      => [
        'name'             => 'name',
        'email'            => 'email address',
        'password'         => 'password',
        'password_confirmation' => 'password confirmation',
        'title'            => 'title',
        'description'      => 'description',
        'price'            => 'price',
        'category_id'      => 'category',
        'pass_percentage'  => 'pass percentage',
        'duration_minutes' => 'duration',
        'max_attempts'     => 'max attempts',
    ],

    // Auth-specific
    'login_failed'    => 'These credentials do not match our records.',
    'account_inactive' => 'Your account has been deactivated. Please contact support.',
    'throttle'        => 'Too many login attempts. Please try again in :seconds seconds.',
];
