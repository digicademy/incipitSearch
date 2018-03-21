<?php
namespace ADWLM\IncipitSearch;

/**
 *  Schema Crawler: Crawles catalogues with data according to IncipitSearch schema.org RDF format
 *
 * Copyright notice
 *
 * (c) 2017
 * Anna Neovesky  Anna.Neovesky@adwmainz.de
 * Torsten Schrade  Torsten.Schrade@adwmainz.de
 *
 * Digital Academy www.digitale-akademie.de
 * Academy of Sciences and Literatur | Mainz www.adwmainz.de
 *
 * Licensed under The MIT License (MIT)
 *
 * @package ADWLM\IncipitSearch
 */

require __DIR__ . '/../vendor/autoload.php';

use SimpleXMLElement;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use EasyRdf_Collection;
use EasyRdf_Graph;
use Elasticsearch\ClientBuilder;
use ADWLM\IncipitSearch\Incipit;
use ADWLM\IncipitSearch\CatalogEntry;

class SchemaIncipitCrawler extends IncipitCrawler
{


    /**
     * Crawls catalog and adds found CatalogEntries to ElasticSearch.
     *
     * This operation might take quite some time to complete.
     */
    public function crawlCatalog()
    {

        // @TODO: QUICK FIX !!! EASYRDF only supports http => how to address?
        $dataUrl = 'http://www.gluck-gesamtausgabe.de/fileadmin/incipitsearch/Breitkopf_Catalogo_delle_Sinfonie.txt';

        $graph = new EasyRdf_Graph();

        // try to load data from data source; if a parser error occurs, write error to log and return
        try {
            $graph->load($dataUrl, 'turtle');
            echo "\n SCHEMA" . " " . $graph . "\n";
        } catch (\Exception $e) {
            array_push($this->logs, $e->getMessage());
            return;
        }

        // get all resources of type schema:DataCatalog (top level)
        $dataCatalogs = $graph->allOfType('schema:DataCatalog');

        // iterate through all data catalogs and fetch catalog identifier (uri) and name
        foreach ($dataCatalogs as $dataCatalog) {

            $dataCatalogIdentifier = $dataCatalog->getUri();
            $dataCatalogName = $graph->get($dataCatalogIdentifier, 'schema:name')->getValue();

            // each catalog has 1:n datasets, each dataset equals a "work" and has 1:n composers
            $dataSets = $graph->all($dataCatalogIdentifier, 'schema:hasPart');

            // iterate through each data set and get composer and incipits
            foreach ($dataSets as $dataSet) {

                $composer = $dataSet->get('schema:composer/schema:name')->getValue();
                $detailUrl = $dataSet->get('schema:image')->getValue();

                // each data set can have 1:n included compositions
                $includedCompositions = $dataSet->all('schema:includedComposition');

                foreach ($includedCompositions as $includedComposition) {

                    $compositionTitle = $includedComposition->get('schema:name')->getValue();

                    // each composition has 1:n incipits
                    $compositionIncipits = $includedComposition->all('schema:includedComposition');

                    foreach ($compositionIncipits as $musicIncipit) {

                        // get incipit components
                        $incipitName = $musicIncipit->get('schema:name')->getValue();
                        $incipitValue = $musicIncipit->get('schema:incipitValue')->getValue();
                        $incipitClef = $musicIncipit->get('schema:incipitClef')->getValue();
                        $incipitKeysig = $musicIncipit->get('schema:incipitKeysig')->getValue();
                        $incipitTimesig = $musicIncipit->get('schema:incipitTimesig')->getValue();

                        // add log entry
                        $this->addLog('catalogEntryFromSchema > ' . $dataCatalogName  . ' - ' . $compositionTitle . ' - ' . $incipitName . "\n" .
                            $incipitClef . ' ' . $incipitKeysig . ' ' . $incipitTimesig . ' ' . $incipitValue);

                        // create ADWLM\IncipitSearch\Incipit instance for catalog entry
                        $incipit = new Incipit($incipitValue, $incipitClef, $incipitKeysig, $incipitTimesig);

                        // generate constant hash based uid on catalogue title and incipit name (both obligatory)
                        $entryUid = hash('crc32', $dataCatalogName) . '-' . hash('md5', $incipitName);

                        // create new catalog entry
                        $catalogEntry = new CatalogEntry(
                            $incipit,
                            $dataCatalogName,
                            $entryUid,
                            0,
                            $dataUrl,
                            $detailUrl,
                            $composer,
                            $compositionTitle,
                            $incipitName,
                            ""
                        );

                        // add entry to search index
                        $this->addCatalogEntryToElasticSearchIndex($catalogEntry);

                    }

                }
            }
        }
    }

    /**
     * Generates a universally unique identifier (UUID) according to RFC 4122 v4.
     * The algorithm used here, might not be completely random. Copied from the identity extension.
     *
     * @return string The universally unique id
     * @author Unknown
     */
    private function generateUUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff));
    }

}
