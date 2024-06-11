<?php

namespace Tests\Feature;

use App\Models\User;
use App\Tests\Utils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    use Utils;

    public function test_user_can_update_username()
    {
        $user = User::factory()->create(['username' => 'test']);

        $newUsername = 'newUserName';

        $this->actingAs($user);

        $response = $this->json('PATCH', '/api/user', [
            'username' => $newUsername
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => $newUsername
        ]);

        $newUserData = auth()->user();
        $this->assertEquals($newUserData->username, $newUsername);
    }

    public function test_user_can_update_name()
    {
        $user = User::factory()->create(['name' => 'test ariel']);

        $newName = 'newUserName';

        $this->actingAs($user);

        $response = $this->json('PATCH', '/api/user', [
            'name' => $newName
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $newName
        ]);

        $newUserData = auth()->user();
        $this->assertEquals($newUserData->name, $newName);
    }

    public function test_updating_user_fails_with_no_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->json('PATCH', '/api/user', [

        ]);

        $response->assertStatus(422);
    }

    public function test_user_data_is_fetching_data_correctly()
    {
        $user = User::factory()->create(['username' => 'testtest1',
            'name' => 'zongo', 'email' => 'test@test.com']);

        $this->actingAs($user);

        $response = $this->json('GET', '/api/user');
        $response->assertStatus(Response::HTTP_OK);
        $data = $this->convertToJson($response);

        $userResponse = $data['data']['user'];
        $this->assertEquals($userResponse['username'], $user->username);
        $this->assertEquals($userResponse['name'], $user->name);
        $this->assertEquals($userResponse['email'], $user->email);
    }

    public function test_setting_user_username_when_its_available()
    {
        $user = User::factory()->create(['username' => 'testtest4']);
        $this->actingAs($user);

        $newUsername = $user->username;

        $response = $this->json('PATCH', '/api/user/username', ['username' => $newUsername]);
        $response->assertStatus(Response::HTTP_OK);

        $user = auth()->user();

        $this->assertEquals($user->username, $newUsername);
    }

    public function test_setting_username_when_its_not_available()
    {
        $user = User::factory()->create(['username' => 'testtest4']);
        $anotherUser = User::factory()->create(['username' => 'zongo123']);

        $this->actingAs($user);

        $response = $this->json('PATCH', '/api/user/username', ['name' => $anotherUser->username]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_login_valid_user_with_email()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('holamundo')
        ]);

        // Act: Attempt to login via 'api/login/email' endpoint
        $response = $this->post('/api/user/login/email', [
            'email' => 'testuser@example.com',
            'password' => 'holamundo',
        ]);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_email_login_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('holamundo')
        ]);

        // Act: Attempt to login via 'api/login/email' endpoint
        $response = $this->post('/api/user/login/email', [
            'email' => 'testuser@example.com',
            'password' => 'zazaza',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_email_login_invalid_email()
    {
        $password = Hash::make('holamundo');

        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => $password
        ]);

        // Act: Attempt to login via 'api/login/email' endpoint
        $response = $this->post('/api/user/login/email', [
            'email' => 'invalid@email.com',
            'password' => $password,
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_uploading_avatar()
    {
        $user = User::factory()->create(['username' => 'testtest1',
            'name' => 'zongo', 'email' => 'test@test.com']);

        $this->assertEmpty($user->avatar);

        $this->actingAs($user);

        Storage::fake('public');

        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->json('PATCH', '/api/user', [
            'avatar' => $file
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $path = 'profile_pictures/' . $file->hashName();
        Storage::assertExists($path);

        $this->assertEquals($user->avatar, $path);
    }

    public function test_uploading_avatar_with_wrong_extension_fails()
    {
        $user = User::factory()->create(['username' => 'testtest1',
            'name' => 'zongo', 'email' => 'test@test.com']);

        $this->assertEmpty($user->avatar);

        $this->actingAs($user);

        Storage::fake('public');

        $file = UploadedFile::fake()->image('profile.ttt');

        $response = $this->json('PATCH', '/api/user', [
            'avatar' => $file
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $path = 'profile_pictures/' . $file->hashName();
        Storage::assertMissing($path);
    }

    public function test_uploading_avatar_that_exceeds_the_file_size_fails()
    {
        $user = User::factory()->create(['username' => 'testtest1',
            'name' => 'zongo', 'email' => 'test@test.com']);

        $this->assertEmpty($user->avatar);

        $this->actingAs($user);

        Storage::fake('public');

        $file = UploadedFile::fake()->create('profile.jpg', 3014);

        $response = $this->json('PATCH', '/api/user', [
            'avatar' => $file
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $path = 'profile_pictures/' . $file->hashName();
        Storage::assertMissing($path);
    }

    public function test_requesting_a_resource_with_no_user_fails()
    {
        $response = $this->json('GET', '/api/task', ['finished' => 0]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_changing_the_password_works()
    {
        $oldPassword = 'oldpassword123';
        $user = User::factory()->create(['username' => 'test', 'password' => Hash::make($oldPassword)]);

        $newPassword = 'zazaza123';
        $this->actingAs($user);

        $response = $this->json('PATCH', '/api/user/password',
            ['password' => $newPassword, 'old_password' => $oldPassword]);

        $response->assertStatus(Response::HTTP_OK);

        $user->refresh();

        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    public function test_changing_the_password_with_incorrect_old_password_fails()
    {
        $oldPassword = 'oldpassword123';
        $user = User::factory()->create(['username' => 'test', 'password' => Hash::make($oldPassword)]);

        $newPassword = 'zazaza123';
        $this->actingAs($user);

        $response = $this->json('PATCH', '/api/user/password',
            ['password' => $newPassword, 'old_password' => 'zetapeta12']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $user->refresh();

        $this->assertTrue(Hash::check($oldPassword, $user->password));
    }

    public static function invalid_password_provider(): array
    {
        return [
            ['newPassword' => ''],
            ['newPassword' => '123'],
            ['newPassword' => 'fffffffffffffffffffffffffffffffffffffffffffff'],
        ];
    }

    /**
     * @dataProvider invalid_password_provider
     */
    public function test_changing_the_password_with_invalid_data_fails($newPassword)
    {
        $originalPassword = 'aajkajkak123';

        $user = User::factory()->create(['username' => 'test', 'password' => Hash::make($originalPassword)]);
        $this->actingAs($user);

        $response = $this->json('PATCH', '/api/user/password', ['old_password' => $originalPassword, 'password' => $newPassword]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $user->refresh();

        $this->assertTrue(Hash::check($originalPassword, $user->password));
    }

    public function test_check_username_is_available()
    {
        $user = User::factory()->create(['username' => 'test']);
        $this->actingAs($user);

        User::factory()->create(['username' => 'testtest1']);
        User::factory()->create(['username' => 'testtest2']);
        User::factory()->create(['username' => 'testtest3']);
        User::factory()->create(['username' => 'testtest5']);

        $usernameToCheck = 'testUserName';

        $response = $this->json('GET', '/api/user/username/available', ['username' => $usernameToCheck]);
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($content['data']['available'], true);
    }

    public function test_check_username_is_not_available()
    {
        $user = User::factory()->create(['username' => 'test']);
        $this->actingAs($user);

        $user = User::factory()->create(['username' => 'testtest4']);

        $usernameToCheck = $user->username;

        $response = $this->json(
            'GET',
            '/api/user/username/available',
            ['username' => $usernameToCheck]
        );
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($content['data']['available'], false);
    }

    public function test_cant_change_to_same_username_as_current_username()
    {
        $user = User::factory()->create(['username' => 'test']);
        $this->actingAs($user);

        $usernameToCheck = $user->username;

        $response = $this->json(
            'GET',
            '/api/user/username/available',
            ['username' => $usernameToCheck]
        );

        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($content['data']['available'], false);
    }

    /**
     * @return array[]
     */
    public static function email_available_provider(): array
    {
        return [
            [
                'holamundo@example.com',
                'chaumundo@example.com',
                true
            ],
            [
                'holamundo@example.com',
                'holamundo@example.com',
                false
            ],
        ];
    }

    /**
     * @dataProvider email_available_provider
     */
    public function test_checking_if_email_is_available(
        $userEmail,
        $sentEmail,
        $available
    )
    {
        $user = User::factory()->create(['username' => 'test']);
        $this->actingAs($user);
        User::factory()->create(['email' => $userEmail]);

        $response = $this->json('GET', '/api/user/email/available', ['email' => $sentEmail]);
        $response->assertStatus(Response::HTTP_OK);
        $data = $this->convertToJson($response);

        $this->assertEquals($available, $data['data']['available']);
    }

    public static function invalid_email_provider(): array
    {
        return [
            ['invalidemail'],
            ['invalid.email'],
            ['invalid@'],
            ['invalid']
        ];
    }

    /**
     * @dataProvider invalid_email_provider
     */
    public function test_checking_if_email_is_available_with_invalid_email($email)
    {
        $user = User::factory()->create([]);
        $this->actingAs($user);

        $response = $this->json('GET', '/api/user/email/available', ['email' => $email]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_cant_change_to_same_email_as_current_email()
    {
        $emailToCheck = "ariel@ariel.com";

        $user = User::factory()->create(['email' => $emailToCheck]);
        $this->actingAs($user);

        $response = $this->json(
            'GET',
            '/api/user/email/available',
            ['email' => $emailToCheck]
        );

        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($content['data']['available'], false);
    }

}
