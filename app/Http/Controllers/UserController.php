<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\EmailRegistrationRequest;
use App\Http\Requests\IsEmailAvailableRequest;
use App\Http\Requests\IsUsernameAvailableRequest;
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
}
