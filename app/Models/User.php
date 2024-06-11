<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const USER_NAME_VALIDATION_RULES = ['string', 'min:8', 'max:50'];
    const USER_USERNAME_VALIDATION_RULES = ['alpha_num', 'min:8', 'max:20'];

    const USER_PASSWORD_VALIDATION_RULES = ['string', 'min:8', 'max:20'];

    const USER_EMAIL_VALIDATION_RULES = ['string', 'email', 'max:255', 'unique:users'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'avatar'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'id'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function findUsernameAnotherUser(string $username)
    {
        return User::where('username', '=', $username)
            ->where('id', '!=', auth()->id())
            ->first();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function taskProgress()
    {
        return $this->hasMany(TaskProgress::class);
    }

    public function userSetting()
    {
        return $this->hasMany(UserSetting::class);
    }
}
