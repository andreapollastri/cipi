<?php

namespace Tests\Feature;

use Tests\TestCase;

class CipiTest extends TestCase
{
    public function testShowLoginPage()
    {
        $response = $this->get('/login');
        $response->assertSee('Cipi Control Panel');
        $response->assertStatus(200);
    }
}
