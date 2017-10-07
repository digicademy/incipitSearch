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
     * Creates an Array  with Incipit Entries from the data at the given URL.
     *
     * @param string $dataURL url to catalog => local catalogues stored in incipitSearch/catalogues
     *                        (currently hard coded)
     *
     * @return CatalogEntries Array of catalog entries
     */
    public function catalogEntriesFromWork(string $dataURL) //can return null
    {
        $schema = new EasyRdf_Graph($dataURL);

        // url to work
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

        //echo "ALL ENTRIES: ";
        //print_r($catalogEntries);
        //return $catalogEntries;

    }


    /**
     * Extracts the string content of an XML element at the given xpath.
     *
     * @param SimpleXMLElement $parentXmlElement
     * @param string $xpath
     * @return string the content, empty if not found
     */
    private function contentOfXMLPath(SimpleXMLElement $parentXmlElement, string $xpath): string
    {
        if ($parentXmlElement == null) {
            $this->addLog("error: contentOfXMLElementAtPath > no parentXmlElement given");
            return "";
        }
        $matchingElements = $parentXmlElement->xpath($xpath);
        if ($matchingElements == null || empty($matchingElements)) {
            return "";
        }
        return (string)$matchingElements[0];
    }


    /**
     * Crawls catalog and adds found CatalogEntries to ElasticSearch.
     *
     * This operation might take quite some time to complete.
     */
    public function crawlCatalog()
    {

        $url = __DIR__ . '/../catalogues/Breitkopf_Catalogo_delle_Sinfonie.txt';
        $schema = new EasyRdf_Graph($url);

        if ($schema == null || strlen($schema) == 0) {
            array_push($this->logs, "error: crawlCatalog > not found at {$url}");
            return;
        }
        $this->addLog("read index xml: \n\n {$schema}");

        // check for valid schema?

        // is this the way to access all
        $incipitsInCatalog = $schema->get('schema:hasPart/schema:includedComposition/a schema:MusicIncipit/schema:name');
        foreach ($incipitsInCatalog as $incipit) {
            $title = $schema->get('schema:hasPart > schema:includedComposition > schema:name');
            $workUrl = $schema->get('schema:url');
            $this->addLog("work: $title $workUrl ");
            // get all incipits entries
            $catalogEntries = $this->catalogEntriesFromWork($url);
            foreach ($catalogEntries as $catalogEntry)
            {
            $this->addCatalogEntryToElasticSearchIndex($catalogEntry);
            }
        }


    }

}

