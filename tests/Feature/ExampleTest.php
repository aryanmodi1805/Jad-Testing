<?php

namespace Tests\Feature;


use App\Jobs\ReviewRequestAIJob;
use App\Models\CustomerAnswer;
use App\Models\Request;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $requestId = Request::find("9cb68e67-fbab-4a8a-9849-1b07479506e1")->id;
        dispatch(new ReviewRequestAIJob(CustomerAnswer::where('request_id',$requestId)->whereNotNull('voice_note')->first()));
    }
}
