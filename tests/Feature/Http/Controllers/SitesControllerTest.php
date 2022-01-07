<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CheckWebsite;
use App\Models\Site;
use App\Models\User;
use App\Notifications\SiteAdded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SitesControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_create_sites_and_sends_a_notification_to_the_user()
    {
        $this->withoutExceptionHandling();
        Notification::fake();
        Bus::fake();
        $user = User::factory()->create();
        $siteData = ['name' => 'Google', 'url' => 'https://google.com'];
        $response = $this->followingRedirects()
            ->actingAs($user)
            ->post(route('sites.store'), $siteData);
        $site = Site::first();
        $this->assertEquals(1, Site::count());
        $this->assertEquals('Google', $site->name);
        $this->assertEquals('https://google.com', $site->url);
        $this->assertNull($site->is_online);
        $this->assertEquals($user->id, $site->user->id);
        $response->assertSeeText('Google');
        $this->assertEquals(route('sites.show', $site), url()->current());
        Notification::assertSentTo($user, SiteAdded::class, function($notification) use ($site) {
            return $notification->site->id === $site->id;
        });
        Bus::assertDispatched(CheckWebsite::class, function ($job) use ($site) {
            return $job->site->id === $site->id;
        });
    }

    /** @test */
    public function it_create_sites()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $siteData = ['name' => 'Google', 'url' => 'https://google.com'];
        $response = $this->followingRedirects()
            ->actingAs($user)
            ->post(route('sites.store'), $siteData);
        $site = Site::first();
        $this->assertEquals(1, Site::count());
        $this->assertEquals('Google', $site->name);
        $this->assertEquals('https://google.com', $site->url);
        $this->assertNull($site->is_online);
        $this->assertEquals($user->id, $site->user->id);
        $response->assertSeeText('Google');
        $this->assertEquals(route('sites.show', $site), url()->current());
    }

    /** @test */
    public function it_requires_all_field_to_be_present()
    {
        Notification::fake();
        $user = User::factory()->create();
        $siteData = ['name' => '', 'url' => ''];
        $response = $this->actingAs($user)->post(route('sites.store'), $siteData);
        $this->assertEquals(0, Site::count());
        $response->assertSessionHasErrors(['name', 'url']);
        Notification::assertNothingSent();
    }

    /** @test */
    public function it_redirects_a_user_to_a_previous_site_if_they_try_to_add_a_duplicate()
    {
        Notification::fake();
        $user = User::factory()->create();
        $site = $user->sites()->save(Site::factory()->make());
        $siteData = ['name' => 'Google 2', 'url' => $site->url];
        $response = $this->actingAs($user)->post(route('sites.store'), $siteData);
        $response->assertRedirect(route('sites.show', $site));
        $response->assertSessionHasErrors(['url']);
        Notification::assertNothingSent();
        $this->assertEquals(1, Site::count());
    }

    /** @test */
    public function it_requires_the_url_to_have_a_valid_protocol()
    {
        Notification::fake();
        $user = User::factory()->create();
        $siteData = ['name' => 'Google', 'url' => 'google.com'];
        $response = $this->actingAs($user)->post(route('sites.store'), $siteData);
        $this->assertEquals(0, Site::count());
        $response->assertSessionHasErrors(['url']);
        Notification::assertNothingSent();
    }

    /** @test */
    public function it_allows_a_user_to_see_their_sites(): void
    {
        $user = User::factory()->create();
        $site = $user->sites()->save(Site::factory()->make());
        $response = $this->actingAs($user)->get(route('sites.index'));
        $response->assertStatus(200);
        $response->assertSeeText($site->url);
        $response->assertSeeText($site->name);
        $response->assertSeeText($site->is_online ? 'Online' : 'Offline');
    }

    /** @test */
    public function it_allows_a_user_to_see_their_site(): void
    {
        $user = User::factory()->create();
        $site = $user->sites()->save(Site::factory()->make());
        $response = $this->actingAs($user)->get(route('sites.show'));
        $response->assertStatus(200);
        $response->assertSeeText($site->url);
        $response->assertSeeText($site->name);
        $response->assertSeeText($site->is_online ? 'Your site is online' : 'Your site is offline');
    }
}
