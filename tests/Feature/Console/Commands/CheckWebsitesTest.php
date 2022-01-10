<?php

namespace Tests\Feature\Console\Commands;

use App\Jobs\CheckWebsite;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CheckWebsitesTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function it_dispatches_jobs_for_all_of_the_websites_in_the_application(): void
    {
        Bus::fake();
        $user = User::factory()->create();
        $siteA = $user->sites()->save(Site::factory()->make());
        $siteB = $user->sites()->save(Site::factory()->make());
        Artisan::call('sites:check');
        Bus::assertDispatched(CheckWebsite::class, function($job) use ($siteA) {
            return $job->site->id === $siteA->id;
        });
        Bus::assertDispatched(CheckWebsite::class, function($job) use ($siteB) {
            return $job->site->id === $siteB->id;
        });
        Bus::assertDispatchedTimes(CheckWebsite::class, 2);
    }
}
