<?php

use Illuminate\Http\Response;

class TokenControllerTest extends TestCase
{
    private function getTokenJsonStructure()
    {
        return [
            'uuid',
            'ip',
            'user_agent',
            'default_content',
            'default_status',
            'default_content_type',
            'timeout',
            'created_at',
            'updated_at',
        ];
    }

    public function testCreateToken()
    {
        $tokenData = [
            'default_content' => $this->faker()->text,
            'default_content_type' => 'application/json',
            'default_status' => 201,
            'timeout' => 0,
        ];

        $tokenId = $this->json('POST', 'token', $tokenData)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure($this->getTokenJsonStructure())
            ->json()['uuid'];

        // Verify persistence
        $this->json('GET', "token/$tokenId")
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure($this->getTokenJsonStructure())
            ->assertJson($tokenData);

        $tokenData = [
            'default_content' => $this->faker()->text,
            'default_content_type' => 'text/plain',
            'default_status' => 200,
            'timeout' => 1,
        ];

        $this->json('PUT', "token/$tokenId", $tokenData)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure($this->getTokenJsonStructure())
            ->assertJson($tokenData);

        $this->json('GET', "token/$tokenId")
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure($this->getTokenJsonStructure())
            ->assertJson($tokenData);

        $this->json('DELETE', "token/$tokenId")
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->json('GET', "token/$tokenId")
            ->assertStatus(Response::HTTP_GONE)
            ->assertJsonStructure([
                'success',
                'error' => [
                    'message'
                ]
            ])
            ->assertJsonMissing(['uuid']);
    }

    public function testInvalidTimeout()
    {
        $tokenData = ['timeout' => 11];

        $this->json('POST', 'token', $tokenData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $tokenId = $this->json('POST', 'token')->json()['uuid'];
        $this->json('PUT', "token/$tokenId", $tokenData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
