<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Contact;

class User extends Model implements Authenticatable
{
    protected $table = "users";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        "username",
        "password",
        "name"
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, "user_id", "id");
    }

    public function getAuthIdentifierName()
    {
        /**
         * Get the name of the unique identifier for the user.
         *
         * @return string
         */
        return 'username';
    }

    public function getAuthIdentifier()
    {
        /**
         * Get the unique identifier for the user.
         *
         * @return mixed
         */

        return $this->username;
    }

    public function getAuthPassword()
    {
        /**
         * Get the password for the user.
         *
         * @return string
         */

        return $this->password;
    }

    public function getRememberToken()
    {
        /**
         * Get the token value for the "remember me" session.
         *
         * @return string
         */
        return $this->token;
    }

    public function setRememberToken($value)
    {
        /**
         * Set the token value for the "remember me" session.
         *
         * @param  string  $value
         * @return void
         */
        $this->token = $value;
    }

    public function getRememberTokenName()
    {
        /**
         * Get the column name for the "remember me" token.
         *
         * @return string
         */
        return "token";
    }
}
