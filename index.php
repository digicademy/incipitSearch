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

    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;

    require 'vendor/autoload.php';

    $logger = new Logger('annilogger');
    $filehandler = new StreamHandler("logs/app.log");
    $logger->pushHandler($filehandler);


    $configuration = [
        'settings' => [
            'displayErrorDetails' => true,
        ],
    ];

    $container = new \Slim\Container($configuration);
    $app = new \Slim\App($container);

    // Register component on container
    $container['view'] = function ($container) {
        $view = new \Slim\Views\Twig('templates', [
            'cache' => false
        ]);
        $view->addExtension(new \Slim\Views\TwigExtension(
            $container['router'],
            $container['request']->getUri()
        ));

        return $view;
    };


    $app->get('/', function (Request $request, Response $response) {
        return $this->view->render($response, 'index.twig', []);
    });


    $app->get('/results', function (Request $request, Response $response) {
        $incipit = $request->getParam('incipit');

        $searchQuery = new SearchQuery();
        $searchQuery->setQuery($incipit);
        $incipitEntries = $searchQuery->performSearchQuery();

        $response = $this->view->render($response, 'results.twig', ['incipitEntries' => $incipitEntries]);
        return $response;

    })->setName("results");

    

    $app->run();