<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

beforeEach(function () {
    require_once __DIR__ . '/../CoolifyClient.php';
});

it('can be instantiated with a Guzzle client', function () {
    $client = new Client();
    $coolifyClient = new CoolifyClient($client);

    expect($coolifyClient)->toBeInstanceOf(CoolifyClient::class);
    expect($coolifyClient->client)->toBeInstanceOf(Client::class);
});

it('fetches projects successfully', function () {
    $mockProjects = [
        (object) ['uuid' => 'abc123', 'name' => 'Project 1', 'description' => 'First project'],
        (object) ['uuid' => 'def456', 'name' => 'Project 2', 'description' => 'Second project'],
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($mockProjects)),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);
    $coolifyClient = new CoolifyClient($client);

    $projects = $coolifyClient->getProjects();

    expect($projects)->toBeArray();
    expect($projects)->toHaveCount(2);
    expect($projects[0]->name)->toBe('Project 1');
    expect($projects[1]->uuid)->toBe('def456');
});

it('fetches servers successfully', function () {
    $mockServers = [
        (object) ['uuid' => 'srv001', 'name' => 'Server 1', 'description' => 'Production server'],
        (object) ['uuid' => 'srv002', 'name' => 'Server 2', 'description' => 'Staging server'],
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($mockServers)),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);
    $coolifyClient = new CoolifyClient($client);

    $servers = $coolifyClient->getServers();

    expect($servers)->toBeArray();
    expect($servers)->toHaveCount(2);
    expect($servers[0]->name)->toBe('Server 1');
    expect($servers[1]->description)->toBe('Staging server');
});

it('returns empty array on request failure', function () {
    $mock = new MockHandler([
        new RequestException('Connection error', new Request('GET', 'projects')),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);
    $coolifyClient = new CoolifyClient($client);

    $projects = $coolifyClient->getProjects();

    expect($projects)->toBeArray();
    expect($projects)->toBeEmpty();
});

it('returns empty array on invalid JSON response', function () {
    $mock = new MockHandler([
        new Response(200, [], 'not valid json'),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);
    $coolifyClient = new CoolifyClient($client);

    $projects = $coolifyClient->getProjects();

    expect($projects)->toBeArray();
    expect($projects)->toBeEmpty();
});

it('handles empty response', function () {
    $mock = new MockHandler([
        new Response(200, [], json_encode([])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);
    $coolifyClient = new CoolifyClient($client);

    $servers = $coolifyClient->getServers();

    expect($servers)->toBeArray();
    expect($servers)->toBeEmpty();
});
