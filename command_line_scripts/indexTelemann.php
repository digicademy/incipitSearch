<?php
namespace ADWLM\IncipitSearch;

require __DIR__ . '/../vendor/autoload.php';


/**
 * This is to be called from command line to index the Telemann works catalog.
 * ATTENTION: this must be called from the same directory as index.php
 * or paths will be wrong
 *
 *
 * Copyright notice
 *
 * (c) 2016
 * Carlo Licciulli  Carlo.Licciulli@adwmainz.de
 *
 * Digital Academy www.digitale-akademie.de
 * Academy of Sciences and Literatur | Mainz www.adwmainz.de
 *
 * Licensed under The MIT License (MIT)
 *
 * @package ADWLM\IncipitSearch
 */


$crawler = new SchemaIncipitCrawler();

$crawler->createIndex();
$crawler->crawlCatalog('https://adwmainz.pages.gitlab.rlp.net/nfdi4culture/cdmd/telemann-indexes/peritext/incipits/telemann-incipits-schema.ttl');