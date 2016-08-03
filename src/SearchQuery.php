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


    require 'vendor/autoload.php';

    use Elasticsearch\ClientBuilder;

    use ADWLM\IncipitSearch\Incipit;
    use ADWLM\IncipitSearch\CatalogEntry;

    use Monolog\Logger;
    use Monolog\Handler\BrowserConsoleHandler;

    class SearchQuery
    {

        /**
         *
         * Queries normalizedIncipit field for given string (wildcard enables to search for substrings)
         *{
         *  "query": {
         *      "wildcard": {
         *          "incipit.normalizedToPitch":  "*f*"
         *      }
         *  },
         *  "size": 10
         * }
         */
        private $query;
        private $fields = ["incipit.normalizedToSingleOctave", "incipit.normalizedToPitch"];
        private $numOfResults;
        private $elasticClient;

        private $page = 0;
        private $pageSize = 10;

        protected $logger;


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
        }


        public function setQuery(string $userInput)
        {
            //escape user input
            //$this->query = json_encode($userInput);
            $this->query = IncipitNormalizer::normalizeToPitch($userInput);
            $this->logger->addInfo("SearchQuery > set query to: " . $this->query);
        }

        /**
         * @return mixed
         */
        public function getQuery()
        {
            return $this->query;
        }

        private function generateSearchParams(): array
        {

            $searchParams = [
                'index' => 'catalog_entries',
                'type' => 'catalogEntry',
                'body' => [
                    'query' => [
//                        "query_string" => [
//                            "fields" => $this->fields,
                            "wildcard" => [
                                "incipit.normalizedToPitch" =>  "*" . $this->query . "*"

                            ]
                        ]
                    ],
                    "from" => 0,
                    "size" => 10
//                ]
            ];

            return $searchParams;

        }


        public function performSearchQuery(): array
        {
            $results = $this->elasticClient->search($this->generateSearchParams());
            return $this->parseSearchResponse($results);
        }

        /**
         * @param array $response
         * @return mixed
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

        /**
         * @return mixed
         */
        public function getNumOfResults()
        {
            return $this->numOfResults;
        }

        /**
         * @return int
         */
        public function getPage()
        {
            return $this->page;
        }

        /**
         * @param int $page
         */
        public function setPage($page)
        {
            $this->page = $page;
        }

        /**
         * @return int
         */
        public function getPageSize()
        {
            return $this->pageSize;
        }

        /**
         * @param int $pageSize
         */
        public function setPageSize($pageSize)
        {
            $this->pageSize = $pageSize;
        }


        /**
         * @param mixed $logger
         */
        public function setLogger(Logger $logger)
        {
            $this->logger = $logger;
        }

    }