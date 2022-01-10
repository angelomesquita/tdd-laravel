<?php

namespace Tests\Feature\Models;

use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
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

    /** @test */
    public function it_gets_archives_sites()
    {
        Carbon::setTestNow(today());
        $user = User::factory()->create();
        $site = $user->sites()->save(Site::factory()->make(['is_online' => false]));
        $this->assertNull($site->archived_at);
        $site->archive();
        $site->refresh();
        $this->assertEquals(today(), $site->archived_at);
    }

    /** @test */
    public function it_fetches_archived_sites(): void
    {
        $user = User::factory()->create();
        $site = $user->sites()->save(Site::factory()->make(['is_online' => false]));
        $site->archive();
        $noneArchivedSite = $user->sites()->save(Site::factory()->make(['is_online' => false]));
        $foundSites = Site::archived()->get();
        $this->assertEquals(1, $foundSites->count());
        $this->assertEquals($site->fresh(), $foundSites->first());
    }

    /** @test */
    public function it_fetches_active_sites(): void
    {
        $user = User::factory()->create();
        $archivedSite = $user->sites()->save(Site::factory()->make(['is_online' => false]));
        $archivedSite->archive();
        $activeSite = $user->sites()->save(Site::factory()->make(['is_online' => false]));
        $foundSites = Site::active()->get();
        $this->assertEquals(1, $foundSites->count());
        $this->assertEquals($activeSite->fresh(), $foundSites->first());
    }
}
