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
        'cache' => false,
        'debug' => true
    ]);

    $view->addExtension(new \Twig_Extension_Debug());

    $view->addExtension(new Views\TwigExtension($container['router'], $container['request']->getUri()));

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

    return $this->view->render($response, 'en/index.twig', []);
})->setName('index');

/**
 * Route for german index / start page.
 */
$app->get('/de', function (Request $request, Response $response) {
    $this->logger->addInfo('Get: /de');

    return $this->view->render($response, 'de/index.twig', []);
})->setName('index');


/**
 * Route for search results.
 */
$app->get('/{langkey}/results/', function (Request $request, Response $response, $args) {
    $this->logger->addInfo('Get: /{langkey}/results/');

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
        $searchQuery->setPage($page - 1);
    }
    // "query does not support array of values"
    $searchQuery->setCatalogFilter($repository);

    $searchQuery->setIsPrefixSearch($isPrefixSearch);
    $searchQuery->setisTransposed($isTransposed);


    $this->logger->addInfo('query: {$searchQuery->getIncipitQuery()}');

    $catalogEntries = $searchQuery->performSearchQuery();

    //construct baseUrl
    $baseUrl = "{$request->getUri()->getBasePath()}?incipit={$incipit}";
    if ($repository != null) {
        foreach ($repository as $index => $entry) {
            $baseUrl .= "&repository[]={$entry}";
        }
    }
    if ($isPrefixSearch != null) {
        $baseUrl .= "&prefix={$isPrefixSearch}";
    }
    if ($isTransposed != null) {
        $baseUrl .= "&transposition={$isTransposed}";
    }

    if ($args['langkey'] === 'en')
    {
        $response = $this->view->render($response, 'en/results.twig', [
            'catalogEntries' => $catalogEntries,
            'searchString' => $searchQuery->getSingleOctaveQuery(),
            'numberOfResults' => $searchQuery->getNumOfResults(),
            'currentPage' => $request->getParam('page'),
            'numberOfPages' => ceil($searchQuery->getNumOfResults() / $searchQuery->getPageSize()),
            //url will be used as base for pagination; in results.twig, the page number will be added
            'baseUrl' => $baseUrl
        ]);
    }
    elseif ($args['langkey'] === 'de')
    {
        $response = $this->view->render($response, 'de/results.twig', [
            'catalogEntries' => $catalogEntries,
            'searchString' => $searchQuery->getSingleOctaveQuery(),
            'numberOfResults' => $searchQuery->getNumOfResults(),
            'currentPage' => $request->getParam('page'),
            'numberOfPages' => ceil($searchQuery->getNumOfResults() / $searchQuery->getPageSize()),
            //url will be used as base for pagination; in results.twig, the page number will be added
            'baseUrl' => $baseUrl
        ]);
    }
    return $response;
}
)->setName('results');

/**
 * Route to About.
 */
$app->get('/{langkey}/about[/]', function (Request $request, Response $response, $args) {

    $this->logger->addInfo('Get: /{langkey}/about');

    if ($args['langkey'] === 'en')
    {
        $response = $this->view->render($response, 'en/about.twig');
    }
    elseif ($args['langkey'] === 'de')
    {
        $response = $this->view->render($response, 'de/about.twig');
    }

    return $response;
}
)->setName('about');

/**
 * Route to Repositories.
 */
$app->get('/{langkey}/repositories[/]', function (Request $request, Response $response, $args) {

    $this->logger->addInfo('Get: /{langkey}/repositories');

    if ($args['langkey'] === 'en')
    {
        $response = $this->view->render($response, 'en/repositories.twig');
    }
    elseif ($args['langkey'] === 'de')
    {
        $response = $this->view->render($response, 'de/repositories.twig');
    }

    return $response;
}
)->setName('repositories');

/**
 * Route to participation.
 */
$app->get('/{langkey}/participation[/]', function (Request $request, Response $response, $args) {

    $this->logger->addInfo('Get: /{langkey}/participation');

    if ($args['langkey'] === 'en')
    {
        $response = $this->view->render($response, 'en/participation.twig');
    }
    elseif ($args['langkey'] === 'de')
    {
        $response = $this->view->render($response, 'de/participation.twig');
    }

    return $response;
}
)->setName('participation');

/**
 * Route to Impressum.
 */
$app->get('/{langkey}/impressum[/]', function (Request $request, Response $response, $args) {

    $this->logger->addInfo('Get: /{langkey}/impressum');

    if ($args['langkey'] === 'en')
    {
        $response = $this->view->render($response, 'en/impressum.twig');
    }
    elseif ($args['langkey'] === 'de')
    {
        $response = $this->view->render($response, 'de/impressum.twig');
    }

    return $response;
}
)->setName('impressum');

/**
 * Route to Privacy policy.
 */
$app->get('/{langkey}/privacy[/]', function (Request $request, Response $response, $args) {

    $this->logger->addInfo('Get: /{langkey}/privacy');

    if ($args['langkey'] === 'en')
    {
        $response = $this->view->render($response, 'en/privacy.twig');
    }
    elseif ($args['langkey'] === 'de')
    {
        $response = $this->view->render($response, 'de/privacy.twig');
    }

    return $response;
}
)->setName('privacy');


/**
 * Route for search json.
 */
$app->get('/json/', function (Request $request, Response $response, $args) {

    $this->logger->addInfo('Get: /json/');

    $incipit = $request->getParam('incipit');
    $repository = $request->getParam('repository');
    $isPrefixSearch = $request->getParam('prefix') != null;
    $isTransposed = $request->getParam('transposition') != null;

    $searchQuery = new SearchQuery();

    $searchQuery->setUserInput($incipit);
    $searchQuery->setCatalogFilter($repository);
    $searchQuery->setIsPrefixSearch($isPrefixSearch);
    $searchQuery->setisTransposed($isTransposed);

    $this->logger->addInfo('query: {$searchQuery->getIncipitQuery()}');

    $result = $searchQuery->performJsonSearchQuery();
    $response->write($result);

    return $response->withHeader('Content-Type', 'application/json');

}
)->setName('json');

$app->run();