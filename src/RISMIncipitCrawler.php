<?php
namespace ADWLM\IncipitSearch;

use SimpleXMLElement;
use Prewk\XmlStringStreamer;
use Prewk\XmlStringStreamer\Parser;
use Prewk\XmlStringStreamer\Stream\File;

/**
 * The RISMIncipitCrawler
 *
 * Copyright notice
 *
 * (c) 2016-2018
 * Anna Neovesky  Anna.Neovesky@adwmainz.de
 * Gabriel Reimers g.a.reimers@gmail.com
 * Torsten Schrade <Torsten.Schrade@adwmainz.de>
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
     * @param string $xml the xml data to parse
     * @return array or null in case of error
     */
    public function catalogEntriesFromXML(string $xml) //can return null
    {
        try {
            $parentXMLElement = new SimpleXMLElement($xml);
        } catch (\Exception $e) {
            // Handle all other exceptions
            return null;
        }

        $result = array();

        $catalogItemID = $this->contentOfXMLPath($parentXMLElement,'controlfield[@tag=\'001\']');
        $composer = $this->contentOfXMLPath($parentXMLElement,'datafield[@tag=\'100\']');
        $title = $this->contentOfXMLPath($parentXMLElement,'datafield[@tag=\'240\']/subfield[@code=\'a\']');
        $subtitle = $this->contentOfXMLPath($parentXMLElement,'datafield[@tag=\'240\']/subfield[@code=\'k\']');
        $year = $this->contentOfXMLPath($parentXMLElement, 'datafield[@tag=\'260\']');
        $detailURL = 'https://opac.rism.info/search?id=' . $catalogItemID;
        $dataURL = 'https://opac.rism.info/id/rismid/' . $catalogItemID . '?format=marc';

        $incipits = $parentXMLElement->xpath('//subfield[@code = \'p\']');

        $i = 1;
        foreach ($incipits as $incipit) {
            $incipitClef = $this->contentOfXMLPath($parentXMLElement,'datafield[@tag=\'031\']['. $i .']/subfield[@code=\'g\']');
            $incipitAccidentals = $this->contentOfXMLPath($parentXMLElement,'datafield[@tag=\'031\']['. $i .']/subfield[@code=\'n\']');
            $incipitTime = $this->contentOfXMLPath($parentXMLElement, 'datafield[@tag=\'031\']['. $i .']/subfield[@code=\'o\']');
            $incipitNotes = $this->contentOfXMLPath($parentXMLElement, 'datafield[@tag=\'031\']['. $i .']/subfield[@code=\'p\']');

            $incipit = new Incipit(
                $incipitNotes,
                $incipitClef,
                $incipitAccidentals,
                $incipitTime
            );

            $catalogEntry = new CatalogEntry(
                $incipit,
                "RISM",
                $catalogItemID,
                $dataURL,
                $detailURL,
                $composer,
                $title,
                $subtitle,
                $year
            );

            $result[] = $catalogEntry;

            $i++;
        }

        return $result;
    }

    /**
     * Extracts the string content of an XML element at the given xpath.
     * @param SimpleXMLElement $parentXmlElement
     * @param string $xpath
     * @return string the content, empty if not found
     */
    private function contentOfXMLPath(SimpleXMLElement $parentXmlElement, string $xpath): string {
        if ($parentXmlElement == null) {
            return '';
        }
        $matchingElements = $parentXmlElement->xpath($xpath);
        if ($matchingElements == null ||  empty($matchingElements)) {
            return '';
        }
        return (string) $matchingElements[0];
    }


    /**
     * Crawls RISM catalog and adds entries to ElasticSearch.
     * @throws
     */
    public function crawlCatalog()
    {
        // crawl catalogue from file using a stream
        $url = '../catalogues/rism.incipits.xml';
        if (file_exists($url)) {
            // Construct stream, parser and streamer
            $stream = new File($url, 1024);
            $parser = new Parser\UniqueNode(array('uniqueNode' => 'record'));
            $streamer = new XmlStringStreamer($parser, $stream);
            while ($node = $streamer->getNode()) {
                $catalogEntries = $this->catalogEntriesFromXML($node);
                foreach ($catalogEntries as $catalogEntry) {
                    $this->addCatalogEntryToElasticSearchIndex($catalogEntry);
                }
            }
        } else {
            throw new \Exception('RISM incipit file does not exist. Use rismStreamParser to create it', 1521580546);
        }
    }

}
