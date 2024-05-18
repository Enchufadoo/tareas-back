<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFriend extends Model
{
    use HasFactory;

    protected $table = 'users_friends';

    protected $fillable = [
        'user_id',
        'friend_id'
    ];

    public static function areUsersFriends($user_id, $friend_id): bool
    {
        return self::where('user_id', $user_id)
            ->where('friend_id', $friend_id)->exists();
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function friend()
    {
        return $this->hasOne(User::class);
    }
}
