<?php

namespace Tests\Feature\Models;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_determines_wether_the_host_is_resolved(): void
    {
        $site = new Site();
        $site->url = 'https://google.com';
        $this->assertTrue($site->isCurrentlyResolving());
        $site->url = 'https://' . Str::random(12) . '.com';
        $this->assertFalse($site->isCurrentlyResolving());
    }

    /** @test */
    public function it_gets_offline_sites()
    {
        $user = User::factory()->create();
        $offlineSite = $user->sites()->save(Site::factory()->make(['is_online' => false]));
        $onlineSite = $user->sites()->save(Site::factory()->make(['is_online' => true]));
        $offlineSites = Site::offline()->get();
        $this->assertCount(1, $offlineSites);
        $this->assertTrue($offlineSite->is($offlineSites->first()));
    }
}
