<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'login' => [
        'success' => ':attribute logged in successfully!',
        'password_incorrect' => 'The password entered is incorrect.',
        'not_found' => 'No :attribute found with this email address.',
        'error' => 'Failed to login the :attribute. Please try again later.',
    ],
    'logout' => [
        'success' => ':attribute logged out successfully.',
        'error' => 'Failed to log out the :attribute. Please try again later.',
    ],

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

];
