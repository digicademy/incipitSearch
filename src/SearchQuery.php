<?php
namespace ADWLM\IncipitSearch;

require 'vendor/autoload.php';

use Elasticsearch\ClientBuilder;

use ADWLM\IncipitSearch\Incipit;
use ADWLM\IncipitSearch\CatalogEntry;


/**
 * The SearchQuery class encapsulates search queries to elastic search
 * and its results.
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
class SearchQuery
{

    private $incipitQuery = "";
    private $catalogFilter = null;


    private $numOfResults = 0;
    private $elasticClient;

    private $page = 0;
    private $pageSize = 100;


    /**
     * SearchQuery constructor.
     */
    public function __construct()
    {

        $jsonConfig = json_decode(file_get_contents("config.json"));
        $elasticHost = $jsonConfig->elasticSearch->host;
        if (empty($elasticHost)) {
            $elasticHost = "127.0.0.1";
        }

        $this->elasticClient = ClientBuilder::create()->setHosts([$elasticHost])->build();
    }


    /**
     * Creates the search query for elastic search as JSON-compatible
     * associated array.
     * Inserts the set incipit query and filter to the elastic
     * search query DSL.
     *
     * @return array associative array of JSON-formatted query
     */
    private function generateSearchParams(): array
    {

        /* original REST query looks like this:

        {
            "query": {
                "bool": {
                    "must": {
                        "wildcard" : {
                            "incipit.normalizedToPitch" :  "*F*"
                        }
                    },
                    "filter": {
                        "term": {
                            "catalog": "Gluck-Gesamtausgabe"
                        }
                    }
                }
            }

        }

        */

        $searchParams = [
            'index' => 'catalog_entries',
            'type' => 'catalogEntry',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'wildcard' => [
                                "incipit.normalizedToSingleOctave" => "*" . $this->incipitQuery . "*"
                            ]
                        ],
                        'filter' => $this->getFilterArray() //there might be multiple filter set or not
                    ]

                ]
            ],
            "from" => $this->page,
            "size" => $this->pageSize
        ];

        return $searchParams;

    }

    /**
     * Generates an associative array of all set filters for the search query.
     *
     * @return array filters for search query, empty array if none
     */
    private function getFilterArray(): array
    {
        $filter = [];

        if (!empty($this->getCatalogFilter())) {
            $filter[] = [
                'term' =>
                    ['catalog' => $this->getCatalogFilter()]
            ];
        }
        return $filter;
    }

    /**
     * Performs the actual search for the set query and filters
     * and returns the matching results as an array of CatalogEntry.
     *
     * @return array matching CatalogEntrys, emtpy if noen
     */
    public function performSearchQuery(): array
    {
        $results = $this->elasticClient->search($this->generateSearchParams());
        return $this->parseSearchResponse($results);
    }

    /**
     * Creates an array of CatalogEntry by parsing
     * the JSON result returned by a search request.
     *
     * @param array $results associative array as returned by elastic search
     * @return array of CatalogEntry
     */
    private function parseSearchResponse(array $results): array
    {
        $this->numOfResults = $results["hits"]["total"];
        $hits = $results["hits"]["hits"];
        $catalogEntries = [];
        foreach ($hits as $hit) {
            $catalogEntry = CatalogEntry::catalogEntryFromJSONArray($hit["_source"]);
            array_push($catalogEntries, $catalogEntry);
        }
        return $catalogEntries;
    }



    //////////////////////////////////////////
    // GETTERS AND SETTERS
    //////////////////////////////////////////



    /**
     * Sets the incipit search query.
     * @param string $userInput
     */
    public function setIncipitQuery(string $userInput)
    {
        $this->incipitQuery = IncipitNormalizer::normalizeToSingleOctave($userInput);
        $this->addLog("SearchQuery > set query to: " . $this->incipitQuery);
    }

    /**
     * Gets the currently set incipitQuery string
     * @return mixed
     */
    public function getIncipitQuery(): string
    {
        return $this->incipitQuery;
    }

    /**
     * Gets the currently set catalog filter.
     * @return string|null
     */
    public function getCatalogFilter()
    {
        return $this->catalogFilter;
    }

    /**
     * Sets the catalog filter.
     * @param string|null $catalogFilter
     */
    public function setCatalogFilter(string $catalogFilter = null)
    {
        $this->catalogFilter = $catalogFilter;
    }

    /**
     * Gets number of search results.
     * @return int
     */
    public function getNumOfResults():int
    {
        return $this->numOfResults;
    }

    /**
     * Gets the current results page.
     * @return int
     */
    public function getPage():int
    {
        return $this->page;
    }

    /**
     * Sets the current results page.
     * @param int $page
     */
    public function setPage(int $page)
    {
        $this->page = $page;
    }

    /**
     * Gets the page size.
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * Sets the page size.
     * @param int $pageSize
     */
    public function setPageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;
    }


    /////////////////////
    // LOGGING
    ////////////////////

    protected $logs = [];

    protected function addLog(string $message)
    {
        array_push($this->logs, $message);
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


}