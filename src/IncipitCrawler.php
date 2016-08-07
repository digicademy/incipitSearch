<?php
namespace ADWLM\IncipitSearch;

/**
 * Created by PhpStorm.
 * User: gaby
 * Date: 29/06/16
 * Time: 10:37
 */


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Elasticsearch\ClientBuilder;

use ADWLM\IncipitSearch\Incipit;
use ADWLM\IncipitSearch\CatalogEntry;

use Monolog\Logger;
use Monolog\Handler\BrowserConsoleHandler;


class IncipitCrawler
{

    protected $logger;

    protected $elasticClient;
    protected $catalogClient;

    protected $indexName = "catalog_entries";

    public function __construct()
    {

        $this->logger = new \Monolog\Logger('IncipitCrawlerLog');
        $console_handler = new \Monolog\Handler\BrowserConsoleHandler();
        $this->logger->pushHandler($console_handler);

        $jsonConfig = json_decode(file_get_contents("config.json"));
        $elasticHost = $jsonConfig->elasticSearch->host;
        if (empty($elasticHost)) {
            $elasticHost = "127.0.0.1";
        }

        $this->elasticClient = ClientBuilder::create()->setHosts([$elasticHost])->build();

        $this->catalogClient = new Client([
            'timeout' => 15.0,
        ]);


    }

    /**
     * Reads given url and saves the content
     * @param string $url URL to resouce
     * @return string content of url
     */
    public function contentOfURL(string $url) //can return null
    {
        try {
            $response = $this->catalogClient->request('GET', $url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
        $content = $response->getBody();
        return $content;
    }


    /**
     * @param CatalogEntry|null $catalogEntry
     */
    public function addCatalogEntryToElasticSearchIndex(CatalogEntry $catalogEntry = null)
    {
        if ($catalogEntry == null) {
            return;
        }

        $esId = $catalogEntry->getCatalog() . $catalogEntry->getCatalogItemID();
        $params = [
            'index' => $this->indexName,
            'type' => 'catalogEntry',
            'id' => $esId,
            'body' => $catalogEntry->getJSONString()
        ];
        $response = $this->elasticClient->index($params);

        $this->logger->addInfo("data: addCatalogEntryToES > Response " . trim(preg_replace('/\s\s+/', ' ', json_encode($response))));

    }


    /**
     * Deletes the catalog_entries index from Elastic Search.
     */
    public function resetIndex()
    {
        $this->logger->addInfo("reset Index");

        $params = [
            'index' => $this->indexName
        ];

        if ($this->elasticClient->indices()->exists($params) == false) { //already deleted
            return;
        }
        try {
            $response = $this->elasticClient->indices()->delete($params);
            $this->logger->addInfo("delete Index {$params['index']} > Response " . trim(preg_replace('/\s\s+/', ' ', json_encode($response))));
        } catch (Exception $e) {
            $this->logger->addInfo("delete Index {$params['index']} > Failed with Error:\n " . $e->getMessage());
        }


    }


    /**
     */
    public function createIndex()
    {
        $params = [
            'index' => $this->indexName
        ];
        if ($this->elasticClient->indices()->exists($params)) {
            return;
        }


        $notAnalyzedStringType = [
            'type' => 'string',
            'index' => 'not_analyzed'
        ];

        $params = [
            'index' => $this->indexName,
            'body' => [

                'mappings' => [
                    'catalogEntry' => [
                        '_source' => [
                            'enabled' => true
                        ],
                        'properties' => [
                            'catalog' => $notAnalyzedStringType,
                            'catalogItemID' => $notAnalyzedStringType,
                            'dataURL' => $notAnalyzedStringType,
                            'detailURL' => $notAnalyzedStringType,
                            'composer' => ["type" => "string"],
                            'title' => ["type" => "string"],
                            'year' => ["type" => "string"],

                            'incipit' => [
                                'type' => 'object',
                                'properties' => [
                                    'notes' => $notAnalyzedStringType,
                                    'clef' => $notAnalyzedStringType,
                                    'accidentals' => $notAnalyzedStringType,
                                    'time' => $notAnalyzedStringType,
                                    'completeIncipit' => $notAnalyzedStringType,
                                    'normalizedToPitch' => $notAnalyzedStringType,
                                    'normalizedToSingleOctave' => $notAnalyzedStringType
                                ]
                            ]

                        ] //proerties
                    ] //incipit
                ] //mappings
            ] //body
        ];

        // Create the index with mappings and settings now
        $response = $this->elasticClient->indices()->create($params);

        $this->logger->addInfo("created Index {$params['index']} > Response " . trim(preg_replace('/\s\s+/', ' ', json_encode($response))));
    }

    /**
     * @param mixed $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }


}