<?php

namespace Tests\Browser;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SitesTest extends DuskTestCase
{
    use DatabaseMigrations;
    
    /** @test */
    public function it_fetches_archived_sites()
    {
        $user = User::factory()->create();
        $archivedSite = $user->sites()->save(Site::factory()->make([
            'url' => 'archivedsite.com',
            'is_online' => false,
        ]));
        $archivedSite->archive();
        $regularSite = $user->sites()->save(Site::factory()->make([
            'url' => 'regularsite.com',
            'is_online' => true,
        ]));
        $this->browse(function (Browser $browser) use ($user, $archivedSite, $regularSite) {
            $browser->loginAs($user)
                ->visitRoute('sites.index')
                ->assertSee('status')
                ->click('@status-selector')
                ->click('@status-archived')
                ->assertSee($archivedSite->url)
                ->assertSee($archivedSite->name)
                ->assertSeeIn('@status-badges', 'Offline')
                ->assertDontSee($regularSite->url)
                ->assertDontSee($regularSite->name)
                ->assertDontSeeIn('@status-badges', 'Online');
        });
    }
}
