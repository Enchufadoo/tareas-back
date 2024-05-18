<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FriendRequest extends Model
{
    use HasFactory;

    const STATUS_ACCEPTED = 'accepted';
    const STATUS_PENDING = 'pending';
    const STATUS_REJECTED = 'rejected';

    protected $table = 'friend_requests';

    protected $fillable = [
        'user_id',
        'receiver_id',
        'status'
    ];

    protected $hidden = [
        'user_id',
        'receiver_id',
        
    ];

//    protected $visible = [
//        'status',
//        'created_at'
//    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function receiver()
    {
        return $this->hasOne(User::class, 'id', 'receiver_id');
    }

    public static function findFriendRequest(int $userId, int $receiverId)
    {
        return FriendRequest::where([['user_id', '=', $userId], ['receiver_id', '=', $receiverId]])->first();
    }
}
