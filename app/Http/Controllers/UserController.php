<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoggedIsEmailAvailableRequest;
use App\Http\Requests\LoggedIsUsernameAvailableRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SetUsernameRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Responses\LoginResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

/**
 * Class UserController
 *
 * This class is responsible for managing user related functionalities.
 */
class UserController extends Controller
{
    /**
     * Authenticates a user using OAuth login.
     *
     * @return JsonResponse
     */
    public function oauthLogin()
    {
        $accessToken = request()->input('accessToken');

        try {
            $googleData = Socialite::driver('google')->userFromToken($accessToken);
        } catch (InvalidStateException) {
            return $this->json([], 'Could not login', Response::HTTP_UNAUTHORIZED);
        }

        $user = User::where('email', $googleData->getEmail())->first();
        $avatar = file_get_contents($googleData->getAvatar());

        if (!$user) {
            $user = User::create([
                'avatar' => base64_encode($avatar),
                'email' => $googleData->getEmail(),
                'name' => $googleData->getName(),
            ]);
        }

        $token = $user->createToken('google')->plainTextToken;
        return (new LoginResponse())->success($token, LoginResponse::METHOD_GOOGLE);
    }

    /**
     * Retrieves the user data for the authenticated user.
     *
     * @return JsonResponse
     */
    public function userData()
    {
        return $this->json(
            [
                'user' => Auth::user()->only('id', 'name', 'username', 'email')
            ]
        );
    }

    /**
     * Sets the username for the authenticated user.
     *
     * @param SetUsernameRequest $request
     * @return JsonResponse
     */
    public function setUsername(SetUsernameRequest $request): JsonResponse
    {
        $newUsername = $request->get('username');
        $user = auth()->user();

        if ($newUsername === $user->username) {
            return $this->json([
                'username' => $newUsername
            ], 'No changes');
        }

        $user->username = $newUsername;
        $user->save();

        return $this->json([
            'username' => $newUsername
        ], 'Username saved');
    }

    /**
     * Logs in a user using email and password authentication.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function emailLogin(LoginRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return $this->json(
                ['Invalid username or password'],
                'Invalid username or password',
                Response::HTTP_UNAUTHORIZED
            );
        }

        $token = $user->createToken('token')->plainTextToken;

        return (new LoginResponse())->success($token, LoginResponse::METHOD_EMAIL);
    }

    /**
     * Updates the user information.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request)
    {
        $user = auth()->user();

        $user->update($request->only(['name', 'username']));

        if ($request->avatar) {
            $path = $request->file('avatar')->store('profile_pictures');
            $user->avatar = $path;
            $user->save();
        }

        return $this->json([], 'User successfully updated');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $oldPassword = $request->old_password;

        $password = $request->password;
        $user = auth()->user();
        $user->password = Hash::make($password);
        $user->save();

        return $this->json([], 'Password successfully changed');
    }

    /**
     * Checks if the given username is available.
     *
     */
    public function isUsernameAvailable(LoggedIsUsernameAvailableRequest $request): JsonResponse
    {
        $requestedUsername = $request->username;

        if ($requestedUsername === auth()->user()->username) {
            return $this->json(['available' => false]);
        }

        $existsAlready = User::where('username', $requestedUsername)->exists();
        if ($existsAlready) {
            return $this->json(['available' => false], 'Username not available');
        }
        return $this->json(['available' => true], 'Username available');
    }

    /**
     * Check if an email is available or already taken.
     *
     */
    public function isEmailAvailable(LoggedIsEmailAvailableRequest $request)
    {
        $requestedEmail = $request->email;

        if ($requestedEmail === auth()->user()->email) {
            return $this->json(['available' => false]);
        }

        $existsAlready = User::where('email', $requestedEmail)->exists();
        if ($existsAlready) {
            return $this->json(['available' => false], 'Email not available');
        }
        return $this->json(['available' => true], 'Email available');
    }
}
