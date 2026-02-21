<?php

namespace Tests\Feature;

use App\Models\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VoiceNote extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {  $response = $this->get('/');
        $response = Request::find("9c2a6b59-caef-499c-a243-eeb81d420b51")->customerAnswers->voice_note;
        $this->expectOutputString($response);

        $response->assertStatus(200);
    }
}
