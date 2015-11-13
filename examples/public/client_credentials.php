<?php

use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Server;

use OAuth2ServerExamples\Repositories\AccessTokenRepository;
use OAuth2ServerExamples\Repositories\ClientRepository;
use OAuth2ServerExamples\Repositories\ScopeRepository;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

include(__DIR__ . '/../vendor/autoload.php');

// Setup the authorization server
$server = new Server();

// Init our repositories
$clientRepository = new ClientRepository();
$scopeRepository = new ScopeRepository();
$accessTokenRepository = new AccessTokenRepository();

// Enable the client credentials grant on the server
$server->enableGrantType(new ClientCredentialsGrant($clientRepository, $scopeRepository, $accessTokenRepository));

// App
$app = new App([Server::class => $server]);
unset($app->getContainer()['errorHandler']);

$app->post('/access_token', function (Request $request, Response $response) {
    /** @var Server $server */
    $server = $this->getContainer()->get(Server::class);
    try {
        return $server->respondToRequest($request);
    } catch (OAuthException $e) {
        return $e->generateHttpResponse();
    } catch (\Exception $e) {
        return $response->withStatus(500)->write($e->getMessage());
    }
});

$app->run();