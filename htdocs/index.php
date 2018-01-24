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

// Set Logger
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
    $repository = $request->getParam('repository');
    $page = $request->getParam('page');
    $isPrefixSearch = $request->getParam('prefix') != null;
    $isTransposed = $request->getParam('transposition') != null;

	$searchQuery = new SearchQuery();

	//TODO: check if at least two notes (or maybe 3) were entered
    // use transposed string for stringlength eval, as it only contains notes (accidentals and octave removed)
    // in HTML: set min input to same value, so request will not be triggered
    $searchQuery->setUserInput($incipit);
    // SearchQuery's default page is 0,
    // so we only set it if it is > 0
    if ($page > 0) {
        // as ElasticSearch and SearchQuery starts counting pages from zero,
        // we subtract 1
        // for the user facing HTML and URL we start pages at 1, though
        $searchQuery->setPage($page -1);
    }
    // "query does not support array of values"
    $searchQuery->setCatalogFilter($repository);

    $searchQuery->setIsPrefixSearch($isPrefixSearch);
    $searchQuery->setisTransposed($isTransposed);


    $this->logger->addInfo('query: {$searchQuery->getIncipitQuery()}');

    $catalogEntries = $searchQuery->performSearchQuery();

    //construct baseUrl
    $baseUrl = "{$request->getUri()->getBasePath()}?incipit={$incipit}";
    if($repository != null)
    {
    	foreach ($repository as $index => $entry) {
			$baseUrl .= "&repository[]={$entry}";
		}
    }
    if($isPrefixSearch != null)
    {
        $baseUrl .= "&prefix={$isPrefixSearch}";
    }
    if($isTransposed != null)
    {
        $baseUrl .= "&transposition={$isTransposed}";
    }

    $response = $this->view->render($response, 'results.twig',
        [
            'catalogEntries' => $catalogEntries,
            'searchString' => $searchQuery->getSingleOctaveQuery(),
            'numberOfResults' => $searchQuery->getNumOfResults(),
            'currentPage' => $request->getParam('page'),
            'numberOfPages' => ceil($searchQuery->getNumOfResults() / $searchQuery->getPageSize()),
            //url will be used as base for pagination; in results.twig, the page number will be added
            'baseUrl' => $baseUrl
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
 * Route to Impressum.
 */
$app->get('/impressum[/]', function (Request $request, Response $response) {

    $this->logger->addInfo('Get: /impressum');

    $response = $this->view->render($response, 'impressum.twig');

    return $response;

})->setName('impressum');


$app->run();