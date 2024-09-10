<?php

include 'vendor/autoload.php';
include 'CoolifyClient.php';

use Alfred\Workflows\Workflow;
use GuzzleHttp\Client;

$workflow = new Workflow();
$cacheSeconds = $workflow->env('CACHE_SECONDS', 30);


/* Prep data. */

$data = $workflow->cache()->readJson(null, false);
$lastCached = $data->saved ?? null;
$now = time();
$shouldRefreshCache = ! $lastCached || ($now - $lastCached) > $cacheSeconds;

if ($shouldRefreshCache) {
    $workflow->logger()->info('Refreshing data...');

    $coolify = new CoolifyClient(createGuzzleClient($workflow));

    $data = (object)[
        'servers' => $coolify->getServers(),
        'projects' => $coolify->getProjects(),
        'saved' => $now,
    ];

    if (! empty($data->servers) || ! empty($data->projects)) {
        // Only cache non-empty responses
        $workflow->cache()->writeJson($data);
    }
} else {
    $workflow->logger()->info('Using cached data...');
}


/* Create workflow items from data. */

$servers = $data->servers ?? [];
$projects = $data->projects ?? [];

foreach ($projects as $project) {
    $workflow->item()
        ->title($project->name)
        ->subtitle($project->description ?? '')
        ->arg(buildDashboardUrl($workflow, 'project/' . $project->uuid));
}

foreach ($servers as $server) {
    $workflow->item()
        ->title($server->name)
        ->subtitle($server->description)
        ->arg(buildDashboardUrl($workflow, 'server/' . $server->uuid));
}

$workflow->output();


/**
 * Builds a URL for an item in the Coolify Dashboard
 *
 * @param Workflow $workflow
 * @param string   $path
 * @return string
 */
function buildDashboardUrl(Workflow $workflow, string $path): string
{
    return sprintf('https://%s/%s',
        $workflow->env('COOLIFY_FQDN', 'app.coolify.io'),
        $path
    );
}

/**
 * Prepares a Guzzle client for connecting to a Coolify instance.
 *
 * @param Workflow $workflow
 * @return Client
 */
function createGuzzleClient(Workflow $workflow): Client
{
    return new Client([
        'base_uri' => 'https://' . $workflow->env('COOLIFY_FQDN', 'app.coolify.io') . '/api/v1/',
        'headers' => [
            'Authorization' => 'Bearer ' . $workflow->env('COOLIFY_API_TOKEN'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
    ]);
}
