<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Uses a preconfigured Guzzle client to connect to the Coolify instance and get stuff.
 */
class CoolifyClient
{
    public Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @see https://coolify.io/docs/api/operations/list-projects
     * @return mixed
     */
    public function getProjects(): mixed
    {
        return $this->fetchResults('projects');
    }

    /**
     * @see https://coolify.io/docs/api/operations/list-servers
     * @return mixed
     */
    public function getServers(): mixed
    {
        return $this->fetchResults('servers');
    }

    /**
     * Returns an array of response objects, or an empty array if something went wrong.
     * @param $endpoint
     * @return mixed
     */
    private function fetchResults($endpoint): mixed
    {
        try {
            $response = $this->client->get($endpoint);
            return json_decode(
                $response->getBody()->getContents(),
                false,
                10,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException|GuzzleException $e) {
            return [];
        }
    }
}