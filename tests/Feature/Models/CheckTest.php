<?php

namespace Tests\Feature\Models;

use App\Models\Check;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class CheckTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_determines_wether_a_check_was_successful()
    {
        $check = Check::factory()->make(['response_status' => Response::HTTP_OK]);
        $this->assertTrue($check->successful());
        $check->response_status = 299;
        $this->assertTrue($check->successful());
        $check->response_status = 300;
        $this->assertFalse($check->successful());
    }
}
