<?php
namespace ADWLM\IncipitSearch;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Elasticsearch\ClientBuilder;

use ADWLM\IncipitSearch\Incipit;
use ADWLM\IncipitSearch\CatalogEntry;


/**
 * IncipitCrawler is a base class for crawler implementations for
 * different catalogs.
 * It provides common functionality like index management and http requests.
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
class IncipitCrawler
{

    protected $elasticClient;
    protected $catalogClient;

    protected $indexName = 'catalog_entries';

    protected $logs = [];
    protected function addLog(string $message) {
        array_push($this->logs, $message);
        echo $message . '\n';
    }

    /**
     * Returns an array of log entries generated during crawling.
     *
     * @return array of strings (log entries)
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * IncipitCrawler constructor.
     */
    public function __construct()
    {
        print(__DIR__);
        $jsonConfig = json_decode(file_get_contents(__DIR__ . '/../config.json'));
        $elasticHost = $jsonConfig->elasticSearch->host;

        if (empty($elasticHost)) {
            $elasticHost = '127.0.0.1';
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
     * Adds a CatalogEntry to the elastic search instance.
     *
     * @param CatalogEntry|null $catalogEntry
     */
    public function addCatalogEntryToElasticSearchIndex(CatalogEntry $catalogEntry = null)
    {
        if ($catalogEntry == null) {
            return;
        }

        $esId = $catalogEntry->getCatalog() . '-' . $catalogEntry->getCatalogItemID();
        $params = [
            'index' => $this->indexName,
            'type' => 'catalogEntry',
            'id' => $esId,
            'body' => $catalogEntry->getJSONString()
        ];
        $response = $this->elasticClient->index($params);

        //$this->addLog("data: addCatalogEntryToES > Response " . trim(preg_replace('/\s\s+/', ' ', json_encode($response))));

    }


    /**
     * Deletes the catalog_entries index from Elastic Search.
     */
    public function resetIndex()
    {
        $this->addLog('reset Index');

        $params = [
            'index' => $this->indexName
        ];

        if ($this->elasticClient->indices()->exists($params) == false) { //already deleted
            return;
        }
        try {
            $response = $this->elasticClient->indices()->delete($params);
            $this->addLog("delete Index {$params['index']} > Response " . trim(preg_replace('/\s\s+/', ' ', json_encode($response))));
        } catch (Exception $e) {
            $this->addLog("delete Index {$params['index']} > Failed with Error:\n " . $e->getMessage());
        }

    }


    /**
     * Creates a new catalog_entries index in the elastic search instance.
     *
     * Does nothing if index already exists.
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

        // fields need to be defined as raw when they should be sorted alphabetically
        $rawType = [
            'type' => 'string',
            'fields' => [
                'raw' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ]
            ]
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
                            'composer' => ['type' => 'string'],
                            'title' => $rawType,
                            'subTitle' => ['type' => 'string'],
                            'year' => ['type' => 'string'],

                            'incipit' => [
                                'type' => 'object',
                                'properties' => [
                                    'notes' => $notAnalyzedStringType,
                                    'clef' => $notAnalyzedStringType,
                                    'accidentals' => $notAnalyzedStringType,
                                    'time' => $notAnalyzedStringType,
                                    'completeIncipit' => $notAnalyzedStringType,
                                    'normalizedToPitch' => $notAnalyzedStringType,
                                    'normalizedToSingleOctave' => $notAnalyzedStringType,
                                    'withoutOrnaments' => $notAnalyzedStringType,
                                    'transposedNotes' => $notAnalyzedStringType
                                ]
                            ]

                        ] //properties
                    ] //incipit
                ] //mappings
            ] //body
        ];

        // Create the index with mappings and settings now
        $response = $this->elasticClient->indices()->create($params);

        //$this->addLog("created Index {$params['index']} > Response " . trim(preg_replace('/\s\s+/', ' ', json_encode($response))));
    }



}