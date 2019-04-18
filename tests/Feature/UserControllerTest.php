<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\UserController;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndex()
    {
        $response = $this->get('/admin/usuarios');
        $response->assertStatus(302);
    }

    public function testCreate()
    {
        $response = $this->get('/admin/usuarios/criar');
        $response->assertStatus(302);
    }
}
