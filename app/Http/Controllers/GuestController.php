<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailRegistrationRequest;
use App\Http\Requests\IsEmailAvailableRequest;
use App\Http\Requests\IsUsernameAvailableRequest;
use App\Http\Responses\LoginResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

/**
 * Class for guest user related operations
 */
class GuestController extends Controller
{
    /**
     * Registers a new user with email registration.
     *
     * @param EmailRegistrationRequest $request
     * @return JsonResponse
     */
    public function emailRegistration(EmailRegistrationRequest $request): JsonResponse
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->save();

        $token = $user->createToken('token')->plainTextToken;

        return (new LoginResponse())->success($token, LoginResponse::METHOD_EMAIL, Response::HTTP_CREATED);
    }

    /**
     * Checks if the given username is available.
     *
     * @param IsUsernameAvailableRequest $request
     *
     * @return JsonResponse
     */
    public function isUsernameAvailable(IsUsernameAvailableRequest $request): JsonResponse
    {
        $requestedUsername = $request->username;
        $existsAlready = User::where('username', $requestedUsername)->exists();
        if ($existsAlready) {
            return $this->json(['available' => false], 'Username not available');
        }
        return $this->json(['available' => true], 'Username available');
    }

    /**
     * Check if an email is available or already taken.
     *
     * @param IsEmailAvailableRequest $request
     *
     * @return JsonResponse
     */
    public function isEmailAvailable(IsEmailAvailableRequest $request)
    {
        $requestedEmail = $request->email;
        $existsAlready = User::where('email', $requestedEmail)->exists();
        if ($existsAlready) {
            return $this->json(['available' => false], 'Email not available');
        }
        return $this->json(['available' => true], 'Email available');
    }
}
