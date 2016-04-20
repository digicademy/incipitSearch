<?php
    /**
     * Created by PhpStorm.
     * User: annaneovesky
     * Date: 18.04.16
     * Time: 11:12
     */

    namespace ADWLM\IncipitSearch;

    use SimpleXMLElement;

    use GuzzleHttp\Pool;
    use GuzzleHttp\Client;
    use GuzzleHttp\Psr7\Request;

    // autoload muss ach anders gehn
    require_once "IncipitEntry.php";
    require 'vendor/autoload.php';

    /**
     * Class IncipitCrawler
     * @package ADWLM\IncipitSearch
     */
    class IncipitCrawler
    {

        private $elasticClient;
        private $catalogClient;

// Documents for Testing:
// Gluck, Artaserse https://opac.rism.info/search?id=400110699
// RDF: https://opac.rism.info/id/rismid/400110699?format=rdf
// MARC XML: https://opac.rism.info/id/rismid/400110699?format=marc


        public function __construct()
        {
            $this->elasticClient = new Client([
            // for some reason localhost not working sometimes => IP
                'base_uri' => 'http://127.0.0.1:9200',
                'timeout'  => 2.0,
            ]);

            $this->catalogClient = new Client([
                'timeout'  => 2.0,
            ]);
        }

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
         * @return IncipitEntry The incipit entry or null
         */
        public function incipitEntryFromXML(string $url, string $xml) //can return null
        {
            try {
                $parentXMLElement = new SimpleXMLElement($xml);
            } catch (\Exception $e) {
                // Handle all other exceptions
                echo "incipitEntryFromXML at {$url} > could not parse XML > {$e->getMessage()} \n";
                return null;
            }
//nicer solution to get first element of array?
            $catalogItemID = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/controlfield[@tag='001']");
            $incipitKey = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/datafield[@tag='031']/subfield[@code='g']");
            $incipitAccidentals = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/datafield[@tag='031']/subfield[@code='n']");
            $incipitTime = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/datafield[@tag='031']/subfield[@code='o']");
            $incipitNote = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/datafield[@tag='031']/subfield[@code='p']");
            $composer = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/datafield[@tag='100']/subfield[@code='a']");
            $title = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/datafield[@tag='240']/subfield[@code='a']");
            $part = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/datafield[@tag='240']/subfield[@code='k']");
            $year = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/datafield[@tag='260']/subfield[@code='c']");

            $incipitEntry = new IncipitEntry();
            $incipitEntry->catalog = "RISM";
            $incipitEntry->dataURL = $url;
            $incipitEntry->detailURL = "https://opac.rism.info/search?id=" . $catalogItemID;
            $incipitEntry->catalogItemID = $catalogItemID;
            $incipitEntry->incipitKey = $incipitKey;
            $incipitEntry->incipitAccidentals = $incipitAccidentals;
            $incipitEntry->incipitTime = $incipitTime;
            $incipitEntry->incipitNotes = $incipitNote;
            $incipitEntry->composer = $composer;
            $incipitEntry->title = $title . " " . $part;
            $incipitEntry->year = $year;

            return $incipitEntry;

        }

        private function contentOfXMLElementAtPath(SimpleXMLElement $xmlElement, string $xpath): string {
            if ($xmlElement == null) {
                echo "contentOfXMLElementAtPath > no xmlElement given\n";
                return "";
            }
            $matchingElements = $xmlElement->xpath($xpath);
            if ($matchingElements == null ||  empty($matchingElements)) {
                return "";
            }
            return (string) $matchingElements[0];
        }

        public function crawlCatalog()
        {

            $startID = 400110860;
            $endID =   400112000;

            for ($i = $startID; $i < $endID; $i++) {
                $url = "https://opac.rism.info/id/rismid/" . $i . "?format=marc";
                $response = $this->catalogClient->request('GET', $url);
                $xml = $response->getBody();
                if ($xml == null || strlen($xml) == 0) {
                    echo "crawlCatalog > not found at {$url}\n";
                    continue;
                }
                $incipit = $this->incipitEntryFromXML($url, $xml);
                $this->addIncipitEntryToElasticSearchIndex($incipit);
            }

        }


        public function addIncipitEntryToElasticSearchIndex(IncipitEntry $incipit = null)
        {
            if ($incipit == null) {
                return;
            }
            $path = '/incipits/incipit/' . $incipit->catalog . $incipit->catalogItemID;
            $response = $this->elasticClient->request('PUT', $path, ['body' => $incipit->json()]);
            echo "addIncipidToES > Response: {$response->getBody()} \n";

        }

    }

    $crawler = new IncipitCrawler();
//$xml = $crawler->readFileFromURL("https://opac.rism.info/id/rismid/400110699?format=marc");
//$incipit = $crawler->incipitEntryFromXML("https://opac.rism.info/id/rismid/400110699?format=marc",$xml);
//echo $incipit->json() . "\n";
//$crawler->addIncipitEntryToElasticSearchIndex($incipit);
$crawler->crawlCatalog();

