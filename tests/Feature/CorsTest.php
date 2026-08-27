<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class CorsTest extends TestCase
{
    public function test_timelines_endpoint_allows_frontend_origin(): void
    {
        $response = $this->get('/api/timelines', [
            'Origin' => 'https://zlatanstajic.github.io',
        ]);

        $response->assertHeader(
            'Access-Control-Allow-Origin',
            'https://zlatanstajic.github.io'
        );
    }

    public function test_timelines_endpoint_allows_local_dev_origin(): void
    {
        $response = $this->get('/api/timelines', [
            'Origin' => 'http://localhost:5173',
        ]);

        $response->assertHeader(
            'Access-Control-Allow-Origin',
            'http://localhost:5173'
        );
    }

    public function test_unknown_origin_gets_no_cors_header(): void
    {
        $response = $this->get('/api/timelines', [
            'Origin' => 'https://evil.example.com',
        ]);

        $response->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}
