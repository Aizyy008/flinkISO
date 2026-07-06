<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only Eloquent model mapping to the legacy CakePHP `users` table.
 *
 * OWNERSHIP: this table is OWNED BY the CakePHP app. Laravel only READS it
 * (for authentication). Do not write to it from Laravel.
 */
class FlinkUser extends Model
{
    protected $table = 'users';
    public $timestamps = false;      // CakePHP manages created/modified itself
    protected $keyType = 'string';   // UUID varchar(36) primary key
    public $incrementing = false;

    protected $hidden = ['password', 'password_token'];

    /**
     * Verify a plaintext password against the CakePHP hash: md5(Security.salt . password).
     */
    public function verifyPassword(string $plain): bool
    {
        $salt = config('flinkiso.security_salt');
        return hash_equals($this->password, md5($salt . $plain));
    }
}
