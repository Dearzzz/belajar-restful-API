<?php

namespace Tests\Feature;

use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post("/api/users", [
            "username" => "Richard",
            "password" => "rahasia123@gmail.com",
            "name" => "Richard Kurnia"
        ])->assertStatus(201)->assertJson([
            "data" => [
                "username" => "Richard",
                "name" => "Richard Kurnia"
            ]
        ]);
    }

    public function testRegisterFailed()
    {
        $this->post("/api/users", [
            "username" => "",
            "password" => "",
            "name" => ""
        ])->assertStatus(400)->assertJson([
            "errors" => [
                "username" => [
                    "The username field is required."
                ],
                "password" => [
                    "The password field is required."
                ],
                "name" => [
                    "The name field is required."
                ]
            ]
        ]);
    }

    public function testRegisterUsernameAlreadyExist()
    {
        $this->testRegisterSuccess();

        $this->post("/api/users", [
            "username" => "Richard",
            "password" => "rahasia123@gmail.com",
            "name" => "Richard Kurnia"
        ])->assertStatus(400)->assertJson([
            "errors" => [
                "message" => [
                    "Username has already registered"
                ]
            ]
        ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->post("/api/users/login", [
            "username" => "test",
            "password" => "test",
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    "username" => "test",
                    "name" => "test"
                ]
            ]);

        $user = User::where("username", "=", "test")->first();
        assertNotNull($user->token);
    }

    public function testLoginFailedUsernameNotFound()
    {
        $this->post("/api/users/login", [
            "username" => "test",
            "password" => "test",
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "Username or Password is wrong"
                    ]
                ]
            ]);
    }
    public function testLoginFailedPasswordWrong()
    {
        $this->seed([UserSeeder::class]);
        $this->post("/api/users/login", [
            "username" => "test",
            "password" => "salah",
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "Username or Password is wrong"
                    ]
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current', [
            "Authorization" => "test"
        ])->assertStatus(200)->assertJson([
            "data" => [
                "username" => "test",
                "name" => "test"
            ]
        ]);
    }

    public function testGetUnauthorize()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current')->assertStatus(401)->assertJson([
            "errors" => [
                "message" => [
                    "unauthorized"
                ]
            ]
        ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current', [
            "Authorization" => "salah"
        ])->assertStatus(401)->assertJson([
            "errors" => [
                "message" => [
                    "unauthorized"
                ]
            ]
        ]);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where("username", "test")->first();

        $this->patch(
            '/api/users/current',
            [
                "password" => "baru"
            ],
            [
                "Authorization" => "test"
            ]
        )->assertStatus(200)->assertJson([
            "data" => [
                "username" => "test",
                "name" => "test"
            ]
        ]);

        $newUser = User::where("username", "test")->first();

        assertNotEquals($oldUser->password, $newUser->password);
    }
    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where("username", "test")->first();

        $this->patch(
            '/api/users/current',
            [
                "name" => "baru"
            ],
            [
                "Authorization" => "test"
            ]
        )->assertStatus(200)->assertJson([
            "data" => [
                "username" => "test",
                "name" => "baru"
            ]
        ]);

        $newUser = User::where("username", "test")->first();

        assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->patch(
            '/api/users/current',
            [
                "name" => "barubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubaru"
            ],
            [
                "Authorization" => "test"
            ]
        )->assertStatus(400)->assertJson([
            "errors" => [
                "name" => [
                    "The name field must not be greater than 100 characters."
                ]
            ]
        ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->delete('/api/users/logout', [], [
            "Authorization" => "test"
        ])->assertStatus(200)->assertJson([
            "data" => true
        ]);

        $user = User::query()->where('username', '=', 'test')->first();
        assertNull($user->token);
    }

    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);
        $this->delete("/api/users/logout", [null], [
            "Authorization" => "salah"
        ])->assertStatus(401)->assertJson([
            "errors" => [
                "message" => [
                    "unauthorized"
                ]
            ]
        ]);
    }

    public function testLogout()
    {
        $this->seed([UserSeeder::class]);
        $response = $this->delete("/api/users/logout", [null], [
            "Authorization" => "test"
        ])->assertJson([
            "data" => true
        ]);
        assertNotNull($response);
    }
}
