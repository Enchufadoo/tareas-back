<?php

namespace App\Http\Controllers;

use App\Http\Requests\FriendUsernameExistsRequest;
use App\Http\Requests\ResolveFriendRequest;
use App\Http\Requests\SendFriendRequestRequest;
use App\Models\FriendRequest;
use App\Models\SettingSettingValue;
use App\Models\User;
use Illuminate\Http\Response;

class FriendController extends Controller
{
    public function friendUsernameExists(FriendUsernameExistsRequest $request)
    {
        $username = $request->get('username');
        $userId = auth()->user()->id;

        $receiver = User::findUsernameAnotherUser($username);

        $sentRequest = null;
        $receivedRequest = null;

        if ((bool)$receiver) {
            $receiverId = $receiver->id;

            $sentRequest = FriendRequest::findFriendRequest($userId, $receiverId);
            $receivedRequest = FriendRequest::findFriendRequest($receiverId, $userId);
        }

        return $this->json(
            [
                'exists' => (bool)$receiver,
                'sent_request' => $sentRequest,
                'received_request' => $receivedRequest,
            ]
        );
    }

    public function sendFriendRequest(SendFriendRequestRequest $request)
    {
        $receiver = User::findUsernameAnotherUser($request->username);

        $receiverId = $receiver->id;
        $userId = auth()->user()->id;

        $sentRequest = FriendRequest::findFriendRequest($userId, $receiverId);
        if ($sentRequest) {
            return $this->json([
                'request' => $sentRequest
            ], 'Request has already been sent', Response::HTTP_CONFLICT);
        }

        $receivedRequest = FriendRequest::findFriendRequest($receiverId, $userId);
        if ($receivedRequest) {
            return $this->json([
                'request' => $receivedRequest
            ], 'Request has already been received', Response::HTTP_CONFLICT);
        }

        $newFriendRequest = new FriendRequest([
            'user_id' => $userId,
            'receiver_id' => $receiverId,
            'status' => FriendRequest::STATUS_PENDING
        ]);

        $newFriendRequest->save();

        return $this->json([
            'request' => $newFriendRequest->setVisible(['created_at', 'status'])
        ], 'Request created', Response::HTTP_CREATED);
    }

    public function list()
    {
        $friendRequests = FriendRequest::with('user', 'receiver')->where(['user_id' => auth()->user()->id])
            ->limit(50)->get();

        $serializedRequests = [];

        $friendRequests->each(function ($friendRequest) use (&$serializedRequests) {
            $data = [
                'id' => $friendRequest->id,
                'from' => $friendRequest->user->username,
                'to' => $friendRequest->receiver->username,
                'status' => $friendRequest->status,
                'created_at' => $friendRequest->created_at,
            ];

            $serializedRequests[] = $data;
        });

        return $this->json(['requests' => $serializedRequests], 'List of requests');
    }

    public function resolveFriendRequest(ResolveFriendRequest $request)
    {
        $friendRequestId = $request->get('friend_request_id');
        $friendRequest = FriendRequest::findOrFail($friendRequestId);

        if ($friendRequest->status !== FriendRequest::STATUS_PENDING) {
            return $this->json([], 'Friend request cannot be accepted', Response::HTTP_BAD_REQUEST);
        }

        $friendRequest->status = FriendRequest::STATUS_ACCEPTED;
        $friendRequest->save();

        return $this->json([], 'Friend request accepted');
    }

}