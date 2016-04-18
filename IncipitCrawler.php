<?php
    /**
     * Created by PhpStorm.
     * User: annaneovesky
     * Date: 18.04.16
     * Time: 11:12
     */

    namespace ADWLM\IncipitSearch;

    use SimpleXMLElement;
    require_once "IncipitEntry.php";

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
        public function incipitEntryFromXML(string $url, string $xml): IncipitEntry
        {

            $xmlDict = new SimpleXMLElement($xml);

//nicer solution to get first element of array?
            $catalogItemID = $xmlDict->xpath("/record/controlfield[@tag='001']")[0];
            $incipitKey = $xmlDict->xpath("/record/datafield[@tag='031']/subfield[@code='g']")[0];
            $incipitAccidentals= $xmlDict->xpath("/record/datafield[@tag='031']/subfield[@code='n']")[0];
            $incipitTime = $xmlDict->xpath("/record/datafield[@tag='031']/subfield[@code='o']")[0];
            $incipitNote = $xmlDict->xpath("/record/datafield[@tag='031']/subfield[@code='p']")[0];
            $composer = $xmlDict->xpath("/record/datafield[@tag='100']/subfield[@code='a']")[0];
            $title = $xmlDict->xpath("/record/datafield[@tag='240']/subfield[@code='a']")[0];
            $part = $xmlDict->xpath("/record/datafield[@tag='240']/subfield[@code='k']")[0];
            $year = $xmlDict->xpath("/record/datafield[@tag='260']/subfield[@code='c']")[0];

            $incipitEntry = new IncipitEntry();
            $incipitEntry->catalog = "RISM";
            $incipitEntry->dataURL = $url;
            $incipitEntry->detailURL = "https://opac.rism.info/search?id=" . $catalogItemID;
            $incipitEntry->catalogItemID = (string) $catalogItemID;
            $incipitEntry->incipitKey = (string) $incipitKey;
            $incipitEntry->incipitAccidentals = (string) $incipitAccidentals;
            $incipitEntry->incipitTime = (string) $incipitTime;
            $incipitEntry->incipitNotes = (string) $incipitNote;
            $incipitEntry->composer = (string) $composer;
            $incipitEntry->title = $title . " " . $part;
            $incipitEntry->year = (string) $year;

            return $incipitEntry;

        }

    }

    $crawler = new IncipitCrawler();
$xml = $crawler->readFileFromURL("https://opac.rism.info/id/rismid/400110699?format=marc");
$incipit = $crawler->incipitEntryFromXML("https://opac.rism.info/id/rismid/400110699?format=marc",$xml);
echo $incipit->json();

