<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VerifyCmsApiKeyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('cms.api_key')->get('/__test/cms-api-key-probe', function () {
            return response()->json(['ok' => true]);
        });
    }

    public function test_request_without_key_is_rejected(): void
    {
        $this->getJson('/__test/cms-api-key-probe')->assertStatus(401);
    }

    public function test_request_with_wrong_key_is_rejected(): void
    {
        $this->getJson('/__test/cms-api-key-probe', ['X-API-Key' => 'wrong'])
            ->assertStatus(401);
    }

    public function test_request_with_correct_key_is_accepted(): void
    {
        $this->getJson('/__test/cms-api-key-probe', ['X-API-Key' => config('services.cms.api_key')])
            ->assertStatus(200)
            ->assertJson(['ok' => true]);
    }
}
