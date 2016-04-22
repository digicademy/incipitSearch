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

    require_once "Incipit.php";
    require_once "IncipitEntry.php";

    class SearchQuery
    {

        /**
         *
         * Queries normalizedIncipit field for given string (wildcard enables to search for substrings)
         *{
         * "query": {
         *  "query_string": {
         *      "fields": ["incipit.normalizedIncipit"],
         *          "wildcard": {
         *              "name": {
         *                  "query": "*f*"
         *                  }
         *              }
         *          }
         *      },
         * "size": 10
         * }
         */
        private $query;
        private $fields = ["incipit.normalizedIncipit"];
        private $numOfResults;


        public function setQuery(string $userInput)
        {
            //TODO: escape user input
            $this->query = $userInput;
        }

        private function generateSearchParams(): array
        {

            $searchParams = [
                'index' => 'incipits',
                'type' => 'incipit',
                'body' => [
                    'query' => [
                        "query_string" => [
                            "fields" => $this->fields,
                            "wildcard" => [
                                "name" => [
                                    "query" => "*" . $this->query . "*"
                                ]
                            ]
                        ]
                    ],
                    "size" => 10
                ]
            ];

            return $searchParams;

        }


        public function performSearchQuery(): array
        {
            $client = ClientBuilder::create()->setHosts(["127.0.0.1:9200"])->build();
            $results = $client->search($this->generateSearchParams());
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
            $incipitEntries = [];
            foreach ($hits as $hit) {
                $incipitEntry = IncipitEntry::incipitEntryFromJSONArray($hit["_source"]);
                array_push($incipitEntries, $incipitEntry);
            }
            return $incipitEntries;
        }

        /**
         * @return mixed
         */
        public function getNumOfResults()
        {
            return $this->numOfResults;
        }




    }