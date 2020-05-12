<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_post_can_be_created()
    {
        $post = factory('App\Post')->create();

        $this->assertDatabaseHas('posts', ['titulo' => $post->titulo]);
    }

    /** @test */
    function a_post_is_shown_correctly_after_its_creation()
    {
        $post = factory('App\Post')->create();

        $this
            ->get('/blog/' . $post->slug)
            ->assertOk()
            ->assertSee($post->titulo);
    }

    /** @test */
    function the_blog_page_is_shown_correctly()
    {
        $post = factory('App\Post')->create();

        $this
            ->get('/blog')
            ->assertOk()
            ->assertSee($post->titulo);
    }
}
