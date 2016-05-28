<?php
namespace ADWLM\IncipitSearch;

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


    // autoload muss ach anders gehn
    require 'vendor/autoload.php';
    
    use SimpleXMLElement;

    use GuzzleHttp\Client;
    use GuzzleHttp\Psr7\Request;

    use Elasticsearch\ClientBuilder;


    use ADWLM\IncipitSearch\Incipit;
    use ADWLM\IncipitSearch\IncipitEntry;
    

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
            $jsonConfig = json_decode(file_get_contents("config.json"));
            $elasticHost = $jsonConfig->elasticSearch->host;
            if (empty($elasticHost)) {
                $elasticHost = "127.0.0.1";
            }
            
            $this->elasticClient = ClientBuilder::create()->setHosts([$elasticHost])->build();

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
                echo "error: incipitEntryFromXML at {$dataURL} > could not parse XML > {$e->getMessage()} <br>\n";
                return null;
            }
//nicer solution to get first element of array?
            $catalogItemID = $this->contentOfXMLElementAtPath($parentXMLElement,
                "/record/controlfield[@tag='001']");
            $incipitClef = $this->contentOfXMLElementAtPath($parentXMLElement,
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
            
            $incipit = new Incipit($incipitNotes, $incipitClef, $incipitAccidentals, $incipitTime);
            $incipitEntry = new IncipitEntry($incipit, "RISM", $catalogItemID, $dataURL, $detailURL,
                $composer, $fullTitle, $year);

            return $incipitEntry;
        }

        /**
         * @param SimpleXMLElement $xmlElement
         * @param string $xpath
         * @return string
         */
        private function contentOfXMLElementAtPath(SimpleXMLElement $xmlElement, string $xpath): string {
            if ($xmlElement == null) {
                echo "error: contentOfXMLElementAtPath > no xmlElement given <br>\n";
                return "";
            }
            $matchingElements = $xmlElement->xpath($xpath);
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
                $response = $this->catalogClient->request('GET', $url);
                $xml = $response->getBody();
                if ($xml == null || strlen($xml) == 0) {
                    echo "error: crawlCatalog > not found at {$url}<br>\n";
                    continue;
                }
                $incipit = $this->incipitEntryFromXML($url, $xml);
                $this->addIncipitEntryToElasticSearchIndex($incipit);
            }

        }

        /**
         * @param IncipitEntry|null $incipitEntry
         */
        public function addIncipitEntryToElasticSearchIndex(IncipitEntry $incipitEntry = null)
        {
            if ($incipitEntry == null) {
                return;
            }

            $esId = $incipitEntry->getCatalog() . $incipitEntry->getCatalogItemID();
            $params = [
                'index' => 'incipits',
                'type' => 'incipit',
                'id' => $esId,
                'body' => $incipitEntry->getJSONString()
            ];
            $response = $this->elasticClient->index($params);

            echo "data: addIncipitToES > Response " . trim(preg_replace('/\s\s+/', ' ', json_encode($response))) . "<br>\n";

        }


    }

//ostrich-sara-pint-riot
$password = $_GET["password"];
if ($password != "ostrich-sara-pint-riot") {
    sleep(10);
    echo "<b>Wrong password.</b>";
} else {
    $crawler = new IncipitCrawler();

    $crawler->crawlCatalog();
}

//$xml = $crawler->readFileFromURL("https://opac.rism.info/id/rismid/400110699?format=marc");
//$incipit = $crawler->incipitEntryFromXML("https://opac.rism.info/id/rismid/400110699?format=marc",$xml);
//echo $incipit->json() . "\n";
//$crawler->addIncipitEntryToElasticSearchIndex($incipit);

