<?php
namespace ADWLM\IncipitSearch;

require '../vendor/autoload.php';


/**
 * This is to be called from command line to reset the catalog_entries index.
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


$crawler = new IncipitCrawler();
$crawler->resetIndex();