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

    require 'vendor/autoload.php';

    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    use Monolog\Logger;
    use Monolog\Handler\BrowserConsoleHandler;
    use Slim\Container as Container;
    use Slim\App as App;
    Use Slim\Views as Views;




    $configuration = [
        'settings' => [
            'displayErrorDetails' => true,

        ],
    ];

    $container = new Container($configuration);
    $app = new App($container);

    // Register component on container
    $container['view'] = function ($container) {
        $view = new Views\Twig('templates', [
            'cache' => false
        ]);
        $view->addExtension(new Views\TwigExtension(
            $container['router'],
            $container['request']->getUri()
        ));

        return $view;
    };

    $container['logger'] = function($c) {
        $logger = new \Monolog\Logger('IncipitLog');
        $console_handler = new \Monolog\Handler\BrowserConsoleHandler();
        $logger->pushHandler($console_handler);
        return $logger;
    };




    ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////

    $jsonConfig = json_decode(file_get_contents("config.json"));
    $adminPassword = $jsonConfig->security->adminPassword;



    $app->get('/', function (Request $request, Response $response) {
        $this->logger->addInfo("Get: /");

        return $this->view->render($response, 'index.twig', []);
    });

    $app->get('/hello/{name}', function (Request $request, Response $response) {
        $name = $request->getAttribute('name');
        $response->getBody()->write("Hello, $name");

        return $response;
    });


    $app->get('/results/', function (Request $request, Response $response) {
        $this->logger->addInfo("Get: /results/");

        $incipit = $request->getParam('incipit');

        $searchQuery = new SearchQuery();
        $searchQuery->setLogger($this->logger);
        $searchQuery->setQuery($incipit);
        $this->logger->addInfo("query: {$searchQuery->getQuery()}");

        $catalogEntries = $searchQuery->performSearchQuery();

        $response = $this->view->render($response, 'results.twig', ['catalogEntries' => $catalogEntries, 'searchString' => $searchQuery->getQuery()]);
        return $response;

    })->setName("results");



    $app->get('/crawler[/]', function (Request $request, Response $response) use ($adminPassword) {

        $this->logger->addInfo("Get: /crawler");

        $password = $request->getParam('password');
        if ($adminPassword != $password) {
            $password = Null;
            sleep(2);
        }

        $response = $this->view->render($response, 'crawler.twig', ['password' => $password]);
        return $response;

    })->setName("crawler");


    $app->get('/crawler/reset', function (Request $request, Response $response) use ($adminPassword) {

        $this->logger->addInfo("Get: /crawler/reset");

        $password = $request->getParam('password');
        if ($adminPassword != $password) {
            $password = Null;
            sleep(2);
            redirect("/");
        }
        $crawler = new IncipitCrawler();
        $crawler->setLogger($this->logger);
        $crawler->resetIndex();

        $response = $this->view->render($response, 'crawler.twig', ["password" => $password]);
        return $response;

    })->setName("crawler_resetIndex");


    $app->get('/crawler/index/RISM', function (Request $request, Response $response) use ($adminPassword) {

        $this->logger->addInfo("Get: /crawler/index/RISM");

        $password = $request->getParam('password');
        if ($adminPassword != $password) {
            $password = Null;
            sleep(2);
            redirect("/");
        }

        $crawler = new RISMIncipitCrawler();
        $crawler->setLogger($this->logger);

        $crawler->createIndex();
        $crawler->crawlCatalog();

        $response = $this->view->render($response, 'crawler.twig', []);
        return $response;

    })->setName("crawler_index_RISM");



    $app->get('/crawler/index/gluck', function (Request $request, Response $response) use ($adminPassword) {

        $this->logger->addInfo("Get: /crawler/index/gluck");

        $password = $request->getParam('password');
        if ($adminPassword != $password) {
            $password = Null;
            sleep(2);
            redirect("/");
        }

        $crawler = new GluckIncipitCrawler();
        $crawler->setLogger($this->logger);

        $crawler->createIndex();
        $crawler->crawlCatalog();
        $logs = $crawler->getLogs();
        $response = $this->view->render($response, 'operationResults.twig', ['logs' => $logs]);
        return $response;

    })->setName("crawler_index_gluck");


    $app->run();