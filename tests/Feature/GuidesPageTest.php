<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuidesPageTest extends TestCase
{
    public function test_guides_page_renders(): void
    {
        $response = $this->get(route('guides'));

        $response->assertOk();
        $response->assertSee('Guides');
    }
}
