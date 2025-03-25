<?php

return [
    'index' => [
        'success' => ':attribute retrieved successfully.',
        'error' => 'Failed to retrieve :attribute. Please try again later.',
    ],
    'store' => [
        'success' => ':attribute created successfully!',
        'error' => 'Failed to create :attribute. Please try again later.',
    ],
    'update' => [
        'success' => ':attribute updated successfully!',
        'error' => 'Failed to update :attribute. Please try again later.',
    ],
    'destroy' => [
        'success' => ':attribute deleted successfully!',
        'error' => 'Failed to delete :attribute. Please try again later.',
    ],
    'assign' => [
        'success' => ':assigned assigned to :attribute successfully!',
        'error' => 'Failed to assign :assigned to :attribute. Please try again later.',
        'not_allowed' => ':assigned assignment to the super user is not allowed.',
    ],
    'revoke' => [
        'success' => ':revoked revoked from :attribute successfully!',
        'error' => 'Failed to revoke :revoked from :attribute. Please try again later.',
        'not_allowed' => ':revoked revokement from the super user is not allowed.',
    ],
    'getMetrics' => [
        'not_allowed' => 'You have no access to :attribute metrics.',
    ]
];
