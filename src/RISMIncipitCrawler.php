<?php
namespace ADWLM\IncipitSearch;

use SimpleXMLElement;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Elasticsearch\ClientBuilder;

use ADWLM\IncipitSearch\Incipit;
use ADWLM\IncipitSearch\CatalogEntry;


/**
 * RISMIncipitCrawler is a sample implementation of an IncipitCrawler.
 * It crawls a small subset of the RISM catalog and adds found incipits
 * to the elastic search instance.
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
class RISMIncipitCrawler extends IncipitCrawler
{

    /**
     * Creates a CatalogEntry with Incipit from the data at the given URL.
     *
     * @param string $dataURL url of data in catalog
     * @param string $xml the xml data to parse
     * @return CatalogEntry null in case of error
     */
    public function catalogEntryFromXML(string $dataURL, string $xml) //can return null
    {
        try {
            $parentXMLElement = new SimpleXMLElement($xml);
        } catch (\Exception $e) {
            // Handle all other exceptions
            echo "error: catalogEntryFromXML at {$dataURL} > could not parse XML > {$e->getMessage()} <br>\n";
            return null;
        }
//nicer solution to get first element of array?
        $catalogItemID = $this->contentOfXMLPath($parentXMLElement,
            "/record/controlfield[@tag='001']");
        $incipitClef = $this->contentOfXMLPath($parentXMLElement,
            "/record/datafield[@tag='031']/subfield[@code='g']");
        $incipitAccidentals = $this->contentOfXMLPath($parentXMLElement, "/record/datafield[@tag='031']/subfield[@code='n']");
        $incipitTime = $this->contentOfXMLPath($parentXMLElement, "/record/datafield[@tag='031']/subfield[@code='o']");
        $incipitNotes = $this->contentOfXMLPath($parentXMLElement, "/record/datafield[@tag='031']/subfield[@code='p']");
        $composer = $this->contentOfXMLPath($parentXMLElement,
            "/record/datafield[@tag='100']/subfield[@code='a']");
        $title = $this->contentOfXMLPath($parentXMLElement,
            "/record/datafield[@tag='240']/subfield[@code='a']");
        $subtitle = $this->contentOfXMLPath($parentXMLElement,
            "/record/datafield[@tag='240']/subfield[@code='k']");
        $year = $this->contentOfXMLPath($parentXMLElement,
            "/record/datafield[@tag='260']/subfield[@code='c']");

        $detailURL = "https://opac.rism.info/search?id=" . $catalogItemID;

        $incipit = new Incipit($incipitNotes, $incipitClef, $incipitAccidentals, $incipitTime);
        $catalogEntry = new CatalogEntry($incipit, "RISM", $catalogItemID, $dataURL, $detailURL,
            $composer, $title, $subtitle, $year);

        return $catalogEntry;
    }

    /**
     * Extracts the string content of an XML element at the given xpath.
     * @param SimpleXMLElement $parentXmlElement
     * @param string $xpath
     * @return string the content, empty if not found
     */
    private function contentOfXMLPath(SimpleXMLElement $parentXmlElement, string $xpath): string {
        if ($parentXmlElement == null) {
            echo "error: contentOfXMLElementAtPath > no parentXmlElement given <br>\n";
            return "";
        }
        $matchingElements = $parentXmlElement->xpath($xpath);
        if ($matchingElements == null ||  empty($matchingElements)) {
            return "";
        }
        return (string) $matchingElements[0];
    }


    /**
     * Crawls catalog and adds found CatalogEntries to ElasticSearch.
     * For RISM this just crawls a selection of about 200 entries.
     * This operation might take quite some time to complete.
     */
    public function crawlCatalog()
    {

        $startID = 400110660;
        $endID =   400110999;

        for ($i = $startID; $i < $endID; $i++) {
            $url = "https://opac.rism.info/id/rismid/" . $i . "?format=marc";
            $xml = $this->contentOfURL($url);
            if ($xml == null || strlen($xml) == 0) {
                echo "error: crawlCatalog > not found at {$url}<br>\n";
                continue;
            }
            $catalogEntry = $this->catalogEntryFromXML($url, $xml);
            $this->addCatalogEntryToElasticSearchIndex($catalogEntry);
        }

    }

}