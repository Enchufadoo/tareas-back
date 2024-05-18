<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnterResetCodeRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\UpdatePasswordForResetRequest;
use App\Managers\PasswordReset;
use App\Models\PasswordResetCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PasswordResetController extends Controller
{
    /**
     * Resets the password for a user and sends a password recovery email.
     *
     * @param PasswordResetRequest $request
     *
     * @param PasswordReset $passwordReset
     * @return JsonResponse
     */
    public function passwordReset(PasswordResetRequest $request, PasswordReset $passwordReset): JsonResponse
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($user) {
            $passwordResetCode = new PasswordResetCode();

            $passwordResetCode->user_id = $user->id;
            $passwordResetCode->code = $passwordReset->createCode();
            $passwordResetCode->expiry_date = Carbon::now()->addMinutes(30);
            $passwordResetCode->save();
        } else {
            Log::warning('Password requested for a non existent user', ['email' => $email]);
        }

        return $this->json([
        ], 'Password recovery received');
    }

    /**
     * Enters the reset code provided by the user for password recovery.
     *
     * @param EnterResetCodeRequest $request
     * @param PasswordReset $passwordReset
     *
     * @return JsonResponse
     *
     * @throws NotFoundHttpException
     */
    public function enterResetCode(EnterResetCodeRequest $request, PasswordReset $passwordReset)
    {
        $email = $request->email;
        $code = $request->code;

        $user = User::where('email', $email)->first();

        /**
         * The reason why this message gets returned is to hide the user / code creation data from the user
         */
        $defaultErrorResponse = [
            [],
            'Incorrect recovery code or too many attempts',
            Response::HTTP_BAD_REQUEST,
            [
                'code' => 'Incorrect recovery code or too many attempts'
            ]
        ];

        if (!$user) {
            Log::warning('Attempt to recover password for a non existing user');
            return $this->json(
                ...$defaultErrorResponse
            );
        }

        $passwordResetCode = PasswordResetCode::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$passwordResetCode) {
            Log::warning('Attempt to reset password but not recovery created');
            return $this->json(
                ...$defaultErrorResponse
            );
        }

        if ($passwordResetCode->renewal_token) {
            Log::warning('The recovery code has already been entered');
            return $this->json([], 'Code already entered', Response::HTTP_GONE, [
                'code' => 'The recovery code has already been entered'
            ]);
        }

        if ($passwordResetCode->redeemed) {
            Log::warning('The recovery code has already been redeemed');
            return $this->json([], 'Code already redeemed', Response::HTTP_GONE, [
                'code' => 'The recovery code has already been redeemed'
            ]);
        }

        if (Carbon::parse($passwordResetCode->expiry_date)->isPast()) {
            Log::info('The recovery code expired');
            return $this->json([], 'Code expired', Response::HTTP_GONE, [
                'code' => 'The recovery code has expired'
            ]);
        }

        if ($passwordResetCode->attempts >= PasswordReset::MAX_NUMBER_OF_ATTEMPTS) {
            Log::info('Max number of attempts to redeem the code reached');
            return $this->json(
                ...$defaultErrorResponse
            );
        }

        $passwordResetCode->attempts = $passwordResetCode->attempts + 1;
        $passwordResetCode->save();

        if ($code !== $passwordResetCode->code) {
            Log::info('Incorrect recovery code');
            return $this->json(
                ...$defaultErrorResponse
            );
        }

        $renewalToken = $passwordReset->createRenewalToken();

        $passwordResetCode->renewal_token = $renewalToken;
        $passwordResetCode->save();

        return $this->json([
            'renewal_token' => $renewalToken
        ], 'Correct recovery code');
    }

    public function updatePasswordForReset(UpdatePasswordForResetRequest $request)
    {
        $code = $request->code;
        $token = $request->renewal_token;
        $password = $request->password;

        $passwordResetCode = PasswordResetCode::where([['code', $code], ['renewal_token', $token]])->first();

        if (!$passwordResetCode) {
            Log::info('Invalid code or renewal token');
            return $this->json([], 'Invalid request', Response::HTTP_BAD_REQUEST);
        }

        $user = $passwordResetCode->user;

        $passwordResetCode->redeemed = true;
        $passwordResetCode->save();

        $user->password = Hash::make($password);
        $user->save();

        return $this->json([], 'Password saved succesfully');
    }
}
