<?php

/**
 * FlinkISO hybrid-integration config.
 * Bridges the new Laravel API service with the legacy CakePHP app.
 */
return [
    // CakePHP Security.salt — required to verify legacy passwords (md5(salt.password)).
    'security_salt' => env('FLINKISO_SECURITY_SALT', ''),

    // JWT settings for the auth bridge.
    'jwt_secret' => env('JWT_SECRET', ''),
    'jwt_ttl_minutes' => (int) env('JWT_TTL_MINUTES', 120),

    // When set, ALL notification emails go to this address instead of the assignee.
    // Use for staging/testing; leave empty in production so assignees get their own emails.
    'notify_override_email' => env('QMS_NOTIFY_OVERRIDE_EMAIL', ''),
];
