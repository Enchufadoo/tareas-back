<?php

namespace Feature;

use App\Models\User;
use App\Tests\Utils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GuestControllerTest extends TestCase
{
    use RefreshDatabase;
    use Utils;

    public function test_check_username_is_available()
    {
        User::factory()->create(['username' => 'testtest1']);
        User::factory()->create(['username' => 'testtest2']);
        User::factory()->create(['username' => 'testtest3']);
        User::factory()->create(['username' => 'testtest5']);

        $usernameToCheck = 'testUserName';

        $response = $this->json('GET', '/api/guest/username/available', ['username' => $usernameToCheck]);
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($content['data']['available'], true);
    }

    public function test_check_username_is_not_available()
    {
        $user = User::factory()->create(['username' => 'testtest4']);

        $usernameToCheck = $user->username;

        $response = $this->json(
            'GET',
            '/api/guest/username/available',
            ['username' => $usernameToCheck]
        );
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($content['data']['available'], false);
    }

    public function test_creating_a_new_user_with_email()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'username' => 'johndoe',
        ];

        $response = $this->json('POST', '/api/guest/registration/email', $userData);
        $response->assertStatus(Response::HTTP_CREATED);
        $responseData = $this->convertToJson($response);

        $this->assertNotEmpty($responseData['data']['token']);
    }

    /**
     * @return \array[][]
     */
    public static function new_user_with_missing_fields_provider(): array
    {
        return [
            [
                [
                    'email' => 'johndoe@example.com',
                    'password' => 'password123',
                    'username' => 'ariel1234'
                ]
            ],
            [
                [
                    'name' => 'John Doe',
                    'email' => null,
                    'password' => 'password123',
                    'username' => 'johndoe',
                ]
            ],
            [
                [
                    'name' => 'John Doe',
                    'email' => 'johndoe@example.com',
                    'username' => 'johndoe',
                ]
            ],
            [
                [
                    'email' => 'johndoe@example.com',
                    'password' => 'password123',
                    'username' => 'johndoe',
                ]
            ],
            [
                [
                    'name' => 'John Doe',
                    'email' => 'zzjzjzjzjz',
                    'password' => 'password123',
                    'username' => 'johndoe',
                ]
            ],
            [
                [
                    'name' => 'J',
                    'email' => 'johndoe@example.com',
                    'password' => 'password123',
                    'username' => 'johndoe',
                ]
            ],
            [
                [
                    'name' => 'John Doe',
                    'email' => 'johndoe@example.com',
                    'password' => 'password123',
                    'username' => 'j',
                ]
            ],

        ];
    }

    /**
     * @dataProvider new_user_with_missing_fields_provider
     * @return void
     */
    public function test_creating_a_new_user_with_missing_fields($userData)
    {
        $response = $this->json('POST', '/api/guest/registration/email', $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
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
        User::factory()->create(['email' => $userEmail]);

        $response = $this->json('GET', '/api/guest/email/available', ['email' => $sentEmail]);
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
        $response = $this->json('GET', '/api/guest/email/available', ['email' => $email]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

}
