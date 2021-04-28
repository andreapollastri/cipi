<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CipiTest extends TestCase
{
  
    public function testRedirectToLogin()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function testShowLoginPage()
    {
        $response = $this->get('/login');
        $response->assertSee('Cipi Control Panel');
        $response->assertStatus(200);
    }
  
    public function testDefaultLogin()
    {
        $this->visit('/login')
             ->type('administrator', 'username')
             ->type('12345678', 'password')
             ->press('OK')
             ->seePageIs('/dashboard');
    }

}
