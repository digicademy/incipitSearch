<?php
namespace ADWLM\IncipitSearch;

use SimpleXMLElement;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Elasticsearch\ClientBuilder;

use ADWLM\IncipitSearch\Incipit;
use ADWLM\IncipitSearch\CatalogEntry;


/**
 * GluckIncipitCrawler gets all incipits from the Gluck Gesamtausgabe catalog
 * and adds them to the Elastic Search instance.
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
class GluckIncipitCrawler extends IncipitCrawler
{

    /**
     * Creates an Array  with Incipit from the data at the given URL.
     *
     * @param string $dataURL url of data in catalog
     *                        is: http://www.gluck-gesamtausgabe.de/rdf/collection/works/
     *
     * @return CatalogEntries Array of catalog entries
     */
    public function catalogEntriesFromWork(string $dataURL) //can return null
    {

        $xml = $this->contentOfURL($dataURL);

        // retrun null when emoty or invalid
        if ($xml == null || strlen($xml) == 0) {
            $this->addLog("error: catalogEntryFromWork > not found at {$dataURL}");
            return;
        }
        try {
            $parentXMLElement = new SimpleXMLElement($xml);
        } catch (\Exception $e) {
            // Handle all other exceptions
            $this->addLog("error: catalogEntryFromXML at {$dataURL} > could not parse XML > {$e->getMessage()}");
            return null;
        }

        // url to work
        $work = $parentXMLElement->xpath("/rdf:RDF/skos:Concept")[0];
        $workIdentifier = $this->contentOfXMLPath($work, "dc:identifier");
        $workTitle = $this->contentOfXMLPath($work, "dc:title");
        //$workDetailUrl = $work->attributes()["about"];

        // Array with urls to parts, e.g. http://www.gluck-gesamtausgabe.de/id/1-20-00-0#parts&456
        $parts = $parentXMLElement->xpath("/rdf:RDF/skos:Concept/skos:relatedMatch/skos:Concept");
        // all incipits will be pushed in this array
        $catalogEntries = [];

        // go through all parts
        foreach ($parts as $part) {

            foreach ($part->xpath(".//skos:relatedMatch/skos:Concept") as $item) {
                $incipitNotes = $this->contentOfXMLPath($item,"bsbmo:incipitScore");
                if(empty($incipitNotes)){
                    continue;
                }
                $partTitle = $this->contentOfXMLPath($part, "dc:title");
                $workDetailUrl = $part->attributes()["about"];
                $incipitClef = $this->contentOfXMLPath($item,"bsbmo:incipitClef");
                $incipitAccidentals = $this->contentOfXMLPath($item,"bsbmo:incipitKeysig");
                $incipitTime = $this->contentOfXMLPath($item,"bsbmo:incipitTimesig");
                $composer = "Christoph Willibald Gluck";

                $this->addLog("catalogEntryFromWork >" . " " . $workTitle . " " . $partTitle . "\n" .
                    $incipitClef . " " . $incipitAccidentals . " " . $incipitTime . " " . $incipitNotes);

                $incipit = new Incipit($incipitNotes, $incipitClef, $incipitAccidentals, $incipitTime);

                $incipitUID = $this->contentOfXMLPath($item,"dc:identifier");
                $entryUID = $workIdentifier . "-" . $incipitUID;
                $catalogEntry = new CatalogEntry($incipit, "GluckWV-online", $entryUID, $dataURL, $workDetailUrl,
                    $composer, $workTitle, $partTitle, "");


                array_push($catalogEntries, $catalogEntry);
            }

        }
        //echo "ALL ENTRIES: ";
        //print_r($catalogEntries);
        return $catalogEntries;

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
        //local
        // $url = "http://gluck-gesamtausgabe.local/rdf/collection/works/";
        //live
        $url = "http://gluck-gesamtausgabe.local/rdf/collection/works/";
        $xml = $this->contentOfURL($url);

        if ($xml == null || strlen($xml) == 0) {
            array_push($this->logs, "error: crawlCatalog > not found at {$url}");
            return;
        }
        $this->addLog("read index xml: \n\n {$xml}");

        try {
            $parentXMLElement = new SimpleXMLElement($xml);
        } catch (\Exception $e) {
            // Handle all other exceptions
            $this->addLog("error: catalogEntryFromXML at $url > could not parse XML > {$e->getMessage()}");
            return null;
        }

        $matchingElements = $parentXMLElement->xpath("/collection/resources/resource");
        foreach ($matchingElements as $resource) {
            $title = (string)$resource->xpath("title")[0];
            $workUrl = (string)$resource->attributes()["target"];
            $this->addLog("work: $title $workUrl ");
            // get all incipits entries
            $catalogEntries = $this->catalogEntriesFromWork($workUrl);
            foreach ($catalogEntries as $catalogEntry)
            {
            $this->addCatalogEntryToElasticSearchIndex($catalogEntry);
            }
        }


    }

}

