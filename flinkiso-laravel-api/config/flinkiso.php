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
];
