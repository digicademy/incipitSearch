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
    use Twig_Loader_Filesystem;
    use Twig_Environment;
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;

    require 'vendor/autoload.php';

    $logger = new Logger('annilogger');
    $filehandler = new StreamHandler("logs/app.log");
    $logger->pushHandler($filehandler);

    $loader = new Twig_Loader_Filesystem("templates");
    $twig = new Twig_Environment($loader, array("cache" => "templates/cache"));

    $app = new \Slim\App;
    // currently without URL rewrite; access: http://incipitsearch.local/index.php/hello;
    // http://localhost:8080/hello
    //TODO: specify URL rewrite http://docs.slimframework.com/routing/rewrite/

    $app->get('/hello', function (Request $request, Response $response) {
        $output = $this->twig->render("index.html");
        $response->getBody()->write("$output");
        $this->logger->addInfo($output);
        return $response;
    });
    $app->run();