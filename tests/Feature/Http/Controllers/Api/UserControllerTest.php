<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_a_user_to_fetch_their_profile_information(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('api/user');
        
        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'sites' => []
            ]
        ]);
    }

    /** @test */
    public function it_allows_a_user_with_their_sites(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $site = $user->sites()->save(Site::factory()->make());
        Sanctum::actingAs($user);
        $response = $this->getJson('api/user');
        
        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'sites' => [
                    [
                        'name' => $site->name,
                        'url' => $site->url,
                        'is_online' => $site->is_online
                    ]
                ]
            ]
        ]);
    }
}
