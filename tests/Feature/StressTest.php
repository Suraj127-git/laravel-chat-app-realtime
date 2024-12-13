<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;

test('stress test for high load', function () {
    // Number of concurrent requests to simulate
    $concurrentRequests = 1000;

    // URL to test
    $url = route('your.route.name');

    // Record start time
    $startTime = microtime(true);

    // Dispatch multiple concurrent requests
    $responses = collect(range(1, $concurrentRequests))
        ->map(fn () => Http::get($url));

    // Record end time
    $endTime = microtime(true);

    // Assert all requests were successful
    $responses->each(function ($response) {
        expect($response->status())->toBe(200);
    });

    // Calculate total duration
    $duration = $endTime - $startTime;

    // Log the duration or add assertions based on your SLA
    expect($duration)->toBeLessThan(10); // For example, under 10 seconds
});
