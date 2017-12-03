<?php
namespace ADWLM\IncipitSearch;

require __DIR__ . '/../vendor/autoload.php';


/**
 * This is to be called from command line to index the Gluck Gesamtausgabe catalog.
 * ATTENTION: this must be called from the same directory as index.php
 * or paths will be wrong
 *
 *
 * Copyright notice
 *
 * (c) 2016
 * Anna Neovesky  Anna.Neovesky@adwmainz.de
 * Gabriel Reimers g.a.reimers@gmail.com
 *
 * Digital Academy www.digitale-akademie.de
 * Academy of Sciences and Literatur | Mainz www.adwmainz.de
 *
 * Licensed under The MIT License (MIT)
 *
 * @package ADWLM\IncipitSearch
 */


$crawler = new RISMIncipitCrawler();

$crawler->createIndex();
$crawler->crawlCatalog();