<?php
    /**
     * Created by PhpStorm.
     * User: annaneovesky
     * Date: 18.04.16
     * Time: 11:12
     */

    namespace ADWLM\IncipitSearch;

    use SimpleXMLElement;

    /**
     * Class IncipitCrawler
     * @package ADWLM\IncipitSearch
     */
    class IncipitCrawler
    {

// Documents for Testing:
// Gluck, Artaserse https://opac.rism.info/search?id=400110699
// RDF: https://opac.rism.info/id/rismid/400110699?format=rdf
// MARC XML: https://opac.rism.info/id/rismid/400110699?format=marc

        /**
         * Reads given url and saves the content
         * @param string $url URL to resouce
         * @return string content of url
         */
        public function readFileFromURL(string $url): string
        {

            $options = array(
                'http'=>array(
                    'method'=>"GET",
                    // user agent must be set, otherwise RISM will throw "403 Forbidden"; in case of problems: try to change user agent string (newest version)
                    'header'=>"User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0"
                )
            );
            $context = stream_context_create($options);

            $content = file_get_contents($url, false, $context);
            return $content;
        }

        /**
         * @param string $file
         * @return IncipitEntry
         */
        public function incipitEntryFromXML(string $xml): string
        {
            $catalog;
            $catalogItemIDXPath;
            $dataURL;
            $detailURL;
            $incipitKeyXPath;
            $incipitTimeXPath;
            $incipitNoteXPath = "//datafield[@tag='031']/subfield[@code='p']";
            $composer;
            $title;
            $year;


            $xmlDict = new SimpleXMLElement($xml);
            return $xmlDict->xpath($incipitNoteXPath)[0];
        }

    }

    $crawler = new IncipitCrawler();
$xml = $crawler->readFileFromURL("https://opac.rism.info/id/rismid/400110699?format=marc");
echo $crawler->incipitEntryFromXML($xml);

