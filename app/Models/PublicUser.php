<?php
// app/Models/PublicUser.php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PublicUser extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'subscribed_to_updates'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'subscribed_to_updates' => 'boolean',
        ];
    }

    public function deadlines(): HasMany
    {
        return $this->hasMany(UserDeadline::class);
    }
}
