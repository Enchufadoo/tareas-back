<?php

namespace Feature;

use App\Managers\PasswordReset;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Tests\Utils;
use Carbon\Carbon;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PassswordResetControllerTest extends TestCase
{
    use RefreshDatabase;
    use Utils;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::first();

        
        $this->actingAs($this->user);
    }

    public function test_reset_password_non_existent_user()
    {
        $response = $this->json('POST', '/api/user/password/reset', ['email' => 'something@something.com']);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertNull(PasswordResetCode::first());
    }

    public function test_reset_password_user()
    {
        $email = 'something@email.com';
        $user = User::factory()->create(['email' => $email]);

        $response = $this->json('POST', '/api/user/password/reset', ['email' => $email]);
        $response->assertStatus(Response::HTTP_OK);

        $passwordResetData = PasswordResetCode::first();

        $this->assertEquals($user->id, $passwordResetData->user_id);
        $this->assertEquals(PasswordReset::RECOVERY_CODE_LENGTH, strlen($passwordResetData->code));

        $this->assertTrue(ctype_digit($passwordResetData->code));
    }

    public function test_reset_password_for_user_another_reset_pending()
    {
        $email = 'something@email.com';
        User::factory()->create(['email' => $email]);

        $response = $this->json('POST', '/api/user/password/reset', ['email' => $email]);
        $response->assertStatus(Response::HTTP_OK);

        $response = $this->json('POST', '/api/user/password/reset', ['email' => $email]);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount(2, PasswordResetCode::all());
    }

    public function test_entering_correct_reset_code()
    {
        $email = 'something@email.com';
        User::factory()->create(['email' => $email]);

        $response = $this->json('POST', '/api/user/password/reset', ['email' => $email]);
        $response->assertStatus(Response::HTTP_OK);

        $passwordResetData = PasswordResetCode::first();
        $this->assertNull($passwordResetData->renewal_token);

        $response = $this->json(
            'POST',
            '/api/user/password/code',
            ['email' => $email, 'code' => $passwordResetData->code]
        );
        $responseData = $this->convertToJson($response);

        $passwordResetData->refresh();

        $this->assertEquals($responseData['data']['renewal_token'], $passwordResetData->renewal_token);

        $this->assertEquals(
            PasswordReset::RENEWAL_TOKEN_LENGTH,
            strlen($passwordResetData->renewal_token)
        );

        $this->assertEquals(0, $passwordResetData->redeemed);
        $this->assertEquals(1, $passwordResetData->attempts);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_entering_incorrect_reset_code()
    {
        $email = 'something@email.com';
        User::factory()->create(['email' => $email]);

        $response = $this->json('POST', '/api/user/password/reset', ['email' => $email]);
        $response->assertStatus(Response::HTTP_OK);

        $response = $this->json(
            'POST',
            '/api/user/password/code',
            ['email' => $email, 'code' => 'ZAZA']
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_entering_a_reset_code_without_a_recovery_attempt()
    {
        $email = 'something@email.com';
        User::factory()->create(['email' => $email]);

        $response = $this->json(
            'POST',
            '/api/user/password/code',
            ['email' => $email, 'code' => 'ZAZA']
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_entering_a_reset_code_that_has_already_been_entered_fails()
    {
        $email = 'something@email.com';
        User::factory()->create(['email' => $email]);

        $response = $this->json('POST', '/api/user/password/reset', ['email' => $email]);
        $response->assertStatus(Response::HTTP_OK);

        $passwordResetData = PasswordResetCode::first();
        $this->assertNull($passwordResetData->renewal_token);

        $response = $this->json(
            'POST',
            '/api/user/password/code',
            ['email' => $email, 'code' => $passwordResetData->code]
        );

        $response->assertStatus(Response::HTTP_OK);

        $response = $this->json(
            'POST',
            '/api/user/password/code',
            ['email' => $email, 'code' => $passwordResetData->code]
        );

        $response->assertStatus(Response::HTTP_GONE);
    }

    public function test_entering_a_reset_code_that_has_expired_fails()
    {
        $email = 'something@email.com';
        User::factory()->create(['email' => $email]);

        $response = $this->json('POST', '/api/user/password/reset', ['email' => $email]);
        $response->assertStatus(Response::HTTP_OK);

        $passwordResetData = PasswordResetCode::first();
        $passwordResetData->expiry_date = Carbon::now()->addMinutes(-30);
        $passwordResetData->save();
        $response = $this->json(
            'POST',
            '/api/user/password/code',
            ['email' => $email, 'code' => $passwordResetData->code]
        );

        $response->assertStatus(Response::HTTP_GONE);
    }

    public function test_entering_a_reset_code_after_max_number_of_attempts_fails()
    {
        $email = 'something@email.com';
        User::factory()->create(['email' => $email]);

        $response = $this->json('POST', '/api/user/password/reset', ['email' => $email]);
        $response->assertStatus(Response::HTTP_OK);
        $responseData = $this->convertToJson($response);

        $passwordResetData = PasswordResetCode::first();
        $passwordResetData->attempts = PasswordReset::MAX_NUMBER_OF_ATTEMPTS;
        $passwordResetData->save();
        $response = $this->json(
            'POST',
            '/api/user/password/code',
            ['email' => $email, 'code' => $passwordResetData->code]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_updating_the_password_works()
    {
        $email = 'something@email.com';
        $user = User::factory()->create(['email' => $email]);

        $response = $this->json('POST', '/api/user/password/reset', ['email' => $email]);
        $response->assertStatus(Response::HTTP_OK);

        $passwordResetData = PasswordResetCode::first();

        $response = $this->json(
            'POST',
            '/api/user/password/code',
            ['email' => $email, 'code' => $passwordResetData->code]
        );
        $responseData = $this->convertToJson($response);
        $renewalToken = $responseData['data']['renewal_token'];

        $response->assertStatus(Response::HTTP_OK);
        $newPassword = 'zazaza123';

        $response = $this->json(
            'POST',
            '/api/user/password/reset/update',
            ['password' => $newPassword, 'code' => $passwordResetData->code, 'renewal_token' => $renewalToken]
        );

        $user->refresh();

        $response->assertStatus(Response::HTTP_OK);

        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    public function test_updating_the_password_with_a_wrong_renewal_token_fails()
    {
        $email = 'something@email.com';
        $user = User::factory()->create(['email' => $email]);
        $passwordresetManager = new PasswordReset();

        $response = $this->json('POST', '/api/user/password/reset', ['email' => $email]);
        $response->assertStatus(Response::HTTP_OK);

        $passwordResetData = PasswordResetCode::first();

        $response = $this->json(
            'POST',
            '/api/user/password/code',
            ['email' => $email, 'code' => $passwordResetData->code]
        );

        $renewalToken = $passwordresetManager->createRenewalToken();

        $response->assertStatus(Response::HTTP_OK);
        $newPassword = 'zazaza123';

        $response = $this->json(
            'POST',
            '/api/user/password/reset/update',
            ['password' => $newPassword, 'code' => $passwordResetData->code, 'renewal_token' => $renewalToken]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        $user->refresh();
        $this->assertFalse(Hash::check($newPassword, $user->password));
    }
}
