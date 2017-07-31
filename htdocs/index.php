<?php

namespace ADWLM\IncipitSearch;

/**
 * Copyright notice
 *
 * (c) 2016
 * Anna Neovesky Anna.Neovesky@adwmainz.de
 * Gabriel Reimers g.a.reimers@gmail.com
 *
 * Digital Academy www.digitale-akademie.de
 * Academy of Sciences and Literatur | Mainz www.adwmainz.de
 *
 * Licensed under The MIT License (MIT)
 */

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as App;
use Slim\Container as Container;
use Slim\Views as Views;

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,

    ],
];

$container = new Container($configuration);
$app = new App($container);

// Register component on container
$container['view'] = function ($container) {
    $view = new Views\Twig('../templates', [
        'cache' => false
    ]);
    $view->addExtension(new Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));

    return $view;
};

$container['logger'] = function ($c) {
    $logger = new \Monolog\Logger('IncipitLog');
    $console_handler = new \Monolog\Handler\BrowserConsoleHandler();
    $logger->pushHandler($console_handler);

    return $logger;
};


////////////////////////////////////////////////////
////////////////////////////////////////////////////

/**
 * Route for index / start page.
 */
$app->get('/', function (Request $request, Response $response) {
    $this->logger->addInfo('Get: /');

    return $this->view->render($response, 'index.twig', []);
})->setName('index');


/**
 * Route for search results.
 */
$app->get('/results/', function (Request $request, Response $response) {
    $this->logger->addInfo('Get: /results/');

    $incipit = $request->getParam('incipit');

    $searchQuery = new SearchQuery();
    $searchQuery->setIncipitQuery($incipit);
    $this->logger->addInfo('query: {$searchQuery->getIncipitQuery()}');

    $catalogEntries = $searchQuery->performSearchQuery();

    $response = $this->view->render($response, 'results.twig',
        [
            'catalogEntries' => $catalogEntries,
            'searchString' => $searchQuery->getIncipitQuery(),
            'numberOfResults' => $searchQuery->getNumOfResults()
        ]);

    return $response;

})->setName('results');

/**
 * Route to About.
 */
$app->get('/about[/]', function (Request $request, Response $response) {

	$this->logger->addInfo('Get: /about');

	$response = $this->view->render($response, 'about.twig');

	return $response;

})->setName('about');

/**
 * Route to Repositories.
 */
$app->get('/repositories[/]', function (Request $request, Response $response) {

	$this->logger->addInfo('Get: /repositories');

	$response = $this->view->render($response, 'repositories.twig');

	return $response;

})->setName('repositories');

/**
 * Route to participation.
 */
$app->get('/participation[/]', function (Request $request, Response $response) {

	$this->logger->addInfo('Get: /participation');

	$response = $this->view->render($response, 'participation.twig');

	return $response;

})->setName('participation');


/**
 * Route to Contact.
 */
$app->get('/contact[/]', function (Request $request, Response $response) {

	$this->logger->addInfo('Get: /contact');

	$response = $this->view->render($response, 'contact.twig');

	return $response;

})->setName('contact');

/**
 * Route to Impressum.
 */
$app->get('/impressum[/]', function (Request $request, Response $response) {

    $this->logger->addInfo('Get: /impressum');

    $response = $this->view->render($response, 'impressum.twig');

    return $response;

})->setName('impressum');


$app->run();