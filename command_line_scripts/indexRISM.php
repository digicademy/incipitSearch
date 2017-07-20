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


/*
 *
 * ATTENTION:
 * This only crawls a small sample of RISM and is only for demo purposes
 *
 */

echo "====================\nATTENTION\n=======================\nThe RISM crawler is only for demo purposes and only crawls a small subset of RISM.\nThis is not fit for production\n\n";

$crawler = new RISMIncipitCrawler();

$crawler->createIndex();
$crawler->crawlCatalog();