<?php
namespace ADWLM\IncipitSearch;

require __DIR__ . '/../vendor/autoload.php';


use SimpleXMLElement;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use EasyRdf_Collection;
use EasyRdf_Graph;

use Elasticsearch\ClientBuilder;

use ADWLM\IncipitSearch\Incipit;
use ADWLM\IncipitSearch\CatalogEntry;


/**
 *  Schema Crawler: Crawles catalogues with data according to IncipitSearch - Standard
 *  first Version of the Crawler is adapted to Breitkopf Catalogo delle Sinfonie
 *  generic usage and configuration file will be added to easily add further catalogues
 *
 * Copyright notice
 *
 * (c) 2016
 * Anna Neovesky  Anna.Neovesky@adwmainz.de
 *
 * Digital Academy www.digitale-akademie.de
 * Academy of Sciences and Literatur | Mainz www.adwmainz.de
 *
 * Licensed under The MIT License (MIT)
 *
 * @package ADWLM\IncipitSearch
 */
class SchemaIncipitCrawler extends IncipitCrawler
{


    /**
     * Crawls catalog and adds found CatalogEntries to ElasticSearch.
     *
     * This operation might take quite some time to complete.
     */
    public function crawlCatalog()
    {

        //$url = __DIR__ . '/../catalogues/Breitkopf_Catalogo_delle_Sinfonie.txt';

        // QUICK FIX !!! EASYRDF only supports http => how to address?
        $url = 'http://www.gluck-gesamtausgabe.de/fileadmin/incipitsearch/Breitkopf_Catalogo_delle_Sinfonie.txt';

        $schema = new EasyRdf_Graph($url);
        $schema->load();
        echo "\n SCHEMA" . " " . $schema . "\n";

        if ($schema == null || strlen($schema) == 0) {
            array_push($this->logs, "error: crawlCatalog > not found at {$url}");
            return;
        }
        $this->addLog("read index xml: \n\n {$schema}");

        // check for valid schema?

        // set catalog information

        //TODO: how to read tag that is used just once
        $catalog = $schema->resource($url, 'schema:name');
        echo "CATALOG" . $catalog;
        // find generic solution for ID
        $catalogItemID = 'urn:nbn:de:bvb:12-bsb10624203-4';
        $dataURL = $schema->resourcesMatching($url, 'schema:image');


        //TODO: how to read tags that appear more than once and add to array
        $parts =$schema->get($url, 'schema:hasPart');
        // go through all parts
        /* @var  $part foreach($parts as $part){
            $composer = $part->get($url, 'schema:hasPart/schema:composer/schema:name');
            $title =  $part->get($url, 'schema:hasPart/schema:includedComposition/schema:name');
            echo "TITLE" . $title;
        }
        */

        // is this the way to access all
        $incipitsInCatalog = $schema->get($url, 'schema:hasPart/schema:includedComposition/');

/**
        // go through all incipits within schema:includedComposition
        foreach ($incipitsInCatalog as $incipit) {
            $title = $schema->get('schema:hasPart/schema:includedComposition/schema:name');
            $workUrl = $schema->get('schema:url');
            $this->addLog("work: $title $workUrl ");
            $work = $schema->get();
            $workIdentifier = $schema->get();
            $workTitle = $schema->get();
            $workDetailUrl = $schema->get();
            $composer = $schema->get();
            $workDetailUrl = $schema->get();

            $partTitle = $schema->get();
            $incipitNotes = $schema->get();
            $incipitClef = $schema->get();
            $incipitAccidentals = $schema->get();
            $incipitTime = $schema->get();


            $this->addLog("catalogEntryFromWork >" . " " . $workTitle . " " . $partTitle . "\n" .
                $incipitClef . " " . $incipitAccidentals . " " . $incipitTime . " " . $incipitNotes);

            $incipit = new Incipit($incipitNotes, $incipitClef, $incipitAccidentals, $incipitTime);

            $incipitUID = "";
            $entryUID = "";
            $catalogEntry = new CatalogEntry($incipit, "Breitkopf Catalogo delle Sinfonie", $entryUID, $dataURL, $workDetailUrl,
                $composer, $workTitle, $partTitle, "");
            // get all incipits entries
            $catalogEntries = $this->catalogEntriesFromWork($url);

            $this->addCatalogEntryToElasticSearchIndex($catalogEntry);

        }
 **/

    }

}

