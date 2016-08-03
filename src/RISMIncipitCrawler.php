<?php
namespace ADWLM\IncipitSearch;

/**
 * Created by PhpStorm.
 * User: gaby
 * Date: 29/06/16
 * Time: 10:39
 */


use SimpleXMLElement;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Elasticsearch\ClientBuilder;

use ADWLM\IncipitSearch\Incipit;
use ADWLM\IncipitSearch\CatalogEntry;

class RISMIncipitCrawler extends IncipitCrawler
{

    /**
     * @param string $file
     * @return CatalogEntry The incipit entry or null
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
        $fullTitle = $title . " " . $subtitle;

        $incipit = new Incipit($incipitNotes, $incipitClef, $incipitAccidentals, $incipitTime);
        $catalogEntry = new CatalogEntry($incipit, "RISM", $catalogItemID, $dataURL, $detailURL,
            $composer, $fullTitle, $year);

        return $catalogEntry;
    }

    /**
     * @param SimpleXMLElement $parentXmlElement
     * @param string $xpath
     * @return string
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
     * Crawls catalog from given url to given url
     */
    public function crawlCatalog()
    {

        $startID = 400110660;
        $endID =   400110862;

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