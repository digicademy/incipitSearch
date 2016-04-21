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

    use GuzzleHttp\Client;
    use GuzzleHttp\Psr7\Request;

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


        public function setQuery(string $userInput)
        {
            //TODO: escape user input
            $this->query = $userInput;
            echo "Incipit is set \n ";
        }

        public function createJSONQuery(): string
        {
            $queryStructure = ["query" => [
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
            ];
        $jsonQuery = json_encode($queryStructure, JSON_PRETTY_PRINT);
        return "{$jsonQuery} \n\n";
        }


        public function performSearchQuery()
        {
            $elasticClient = new Client([
                // for some reason localhost not working sometimes => IP
                'base_uri' => 'http://127.0.0.1:9200',
                'timeout'  => 2.0,
            ]);

            $path = '/incipits/_search';

            $response = $elasticClient->request('POST', $path, ['body' => $this->createJSONQuery()]);
            return $response->getBody();

            //TODO: return array of IncipitsEntries
        }



    }