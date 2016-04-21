<?php
    /**
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
     */

    namespace ADWLM\IncipitSearch;

    // autoload muss ach anders gehn
    require 'vendor/autoload.php';
    
    use SimpleXMLElement;

    use GuzzleHttp\Client;
    use GuzzleHttp\Psr7\Request;

    require_once "Incipit.php";
    require_once "IncipitEntry.php";
    

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
        public function incipitEntryFromXML(string $dataURL, string $xml) //can return null
        {
            try {
                $parentXMLElement = new SimpleXMLElement($xml);
            } catch (\Exception $e) {
                // Handle all other exceptions
                echo "incipitEntryFromXML at {$dataURL} > could not parse XML > {$e->getMessage()} \n";
                return null;
            }
//nicer solution to get first element of array?
            $catalogItemID = $this->contentOfXMLElementAtPath($parentXMLElement,
                "/record/controlfield[@tag='001']");
            $incipitKey = $this->contentOfXMLElementAtPath($parentXMLElement,
                "/record/datafield[@tag='031']/subfield[@code='g']");
            $incipitAccidentals = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/datafield[@tag='031']/subfield[@code='n']");
            $incipitTime = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/datafield[@tag='031']/subfield[@code='o']");
            $incipitNotes = $this->contentOfXMLElementAtPath($parentXMLElement, "/record/datafield[@tag='031']/subfield[@code='p']");
            $composer = $this->contentOfXMLElementAtPath($parentXMLElement,
                "/record/datafield[@tag='100']/subfield[@code='a']");
            $title = $this->contentOfXMLElementAtPath($parentXMLElement,
                "/record/datafield[@tag='240']/subfield[@code='a']");
            $subtitle = $this->contentOfXMLElementAtPath($parentXMLElement,
                "/record/datafield[@tag='240']/subfield[@code='k']");
            $year = $this->contentOfXMLElementAtPath($parentXMLElement,
                "/record/datafield[@tag='260']/subfield[@code='c']");

            $detailURL = "https://opac.rism.info/search?id=" . $catalogItemID;
            $fullTitle = $title . " " . $subtitle;
            
            $incipit = new Incipit($incipitNotes, $incipitKey, $incipitAccidentals, $incipitTime);
            $incipitEntry = new IncipitEntry($incipit, "RISM", $catalogItemID, $dataURL, $detailURL,
                $composer, $fullTitle, $year);

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

            $startID = 400110660;
            $endID =   400110862;

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


        public function addIncipitEntryToElasticSearchIndex(IncipitEntry $incipitEntry = null)
        {
            if ($incipitEntry == null) {
                return;
            }
            $path = '/incipits/incipit/' . $incipitEntry->getCatalog() . $incipitEntry->getCatalogItemID();
            $response = $this->elasticClient->request('PUT', $path, ['body' => $incipitEntry->getJSONRepresentation()]);
            echo "addIncipidToES > Response: {$response->getBody()} \n";

        }

    }

    $crawler = new IncipitCrawler();
//$xml = $crawler->readFileFromURL("https://opac.rism.info/id/rismid/400110699?format=marc");
//$incipit = $crawler->incipitEntryFromXML("https://opac.rism.info/id/rismid/400110699?format=marc",$xml);
//echo $incipit->json() . "\n";
//$crawler->addIncipitEntryToElasticSearchIndex($incipit);
$crawler->crawlCatalog();

