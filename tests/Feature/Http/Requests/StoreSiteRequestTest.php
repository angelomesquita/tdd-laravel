<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreSiteRequest;
use App\Rules\ValidProtocol;
use Tests\TestCase;

class StoreSiteRequestTest extends TestCase
{
    /** @test */   
    public function it_has_the_correct_rules()
    {
        $request = new StoreSiteRequest;
        $rules = [
            'name' => ['required', 'string'],
            'url' => ['required', 'string', new ValidProtocol]
        ];
        $this->assertEquals($rules, $request->rules());
    }

    /** @test */
    public function it_authorizes_every_user()
    {
        $request = new StoreSiteRequest;
        $this->assertEquals(true, $request->authorize());
    }
}
