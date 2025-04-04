<?php

class RequestControllerTest extends TestCase
{
    public function testPagination()
    {
        // Prevent throttling
        $this->withoutMiddleware();

        $number = 175;

        $tokenId = $this->json('POST', 'token')->json()['uuid'];

        for ($i = 0; $i < $number; $i++) {
            $this->call('GET', $tokenId);
        }

        $requests = $this->json('GET', "token/$tokenId/requests?per_page=40&page=1");

        $requests->assertJson([
            'total' => $number,
            'per_page' => 40,
            'current_page' => 1,
            'is_last_page' => false,
            'from' => 1,
            'to' => 40,
        ]);

        $requests = $this->json('GET', "token/$tokenId/requests?per_page=40&page=2");

        $requests->assertJson([
            'current_page' => 2,
            'is_last_page' => false,
            'from' => 41,
            'to' => 80,
        ]);

        $requests = $this->json('GET', "token/$tokenId/requests?per_page=40&page=3");

        $requests->assertJson([
            'current_page' => 3,
            'is_last_page' => false,
            'from' => 81,
            'to' => 120,
        ]);

        $requests = $this->json('GET', "token/$tokenId/requests?per_page=40&page=5");

        $requests->assertJson([
            'current_page' => 5,
            'is_last_page' => true,
            'from' => 161,
            'to' => 175,
        ]);

        $requests = $this->json('GET', "token/$tokenId/requests?per_page=40&page=6");

        $requests->assertExactJson([
            'data' => [],
            'total' => $number,
            'per_page' => 40,
            'current_page' => 6,
            'is_last_page' => true,
            'from' => 201,
            'to' => 175,
        ]);
    }

    public function testSorting() {
        // Prevent throttling
        $this->withoutMiddleware();

        $number = 175;

        $tokenId = $this->json('POST', 'token')->json()['uuid'];

        for ($i = 0; $i < $number; $i++) {
            $this->call('GET', $tokenId);
        }

        // Test newest
        $requests = $this->json('GET', "token/$tokenId/requests?sorting=newest");

        $requests->assertJson([
            'total' => $number,
            'per_page' => 50,
            'current_page' => 1,
            'is_last_page' => false,
            'from' => 1,
            'to' => 50,
        ]);

        $data = $requests->json()['data'];
        $timestamps = array_column($data, 'created_at');
        $sortedDescTimestamps = $timestamps;
        rsort($sortedDescTimestamps);
        $this->assertSame($sortedDescTimestamps, $timestamps, "The 'created_at' field is not sorted in descending order.");

        // Test oldest
        $requests = $this->json('GET', "token/$tokenId/requests?sorting=oldest");

        $requests->assertJson([
            'total' => $number,
            'per_page' => 50,
            'current_page' => 1,
            'is_last_page' => false,
            'from' => 1,
            'to' => 50,
        ]);

        $data = $requests->json()['data'];
        $timestamps = array_column($data, 'created_at');
        $sortedAscTimestamps = $timestamps;
        sort($sortedAscTimestamps);
        $this->assertSame($sortedAscTimestamps, $timestamps,'The "created_at" field is not sorted in ascending order.');
    }
}
