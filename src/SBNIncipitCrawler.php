<?php
namespace ADWLM\IncipitSearch;

use DOMDocument;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Elasticsearch\ClientBuilder;

use ADWLM\IncipitSearch\Incipit;
use ADWLM\IncipitSearch\CatalogEntry;


/**
 * SBNIncipitCrawler is an expansion of IncipitCrawler.
 * It crawls a small subset of the SBN catalog and adds found incipits
 * to the elastic search instance.
 *
 *
 * Copyright notice
 *
 * (c) 2016
 * Anna Neovesky  Anna.Neovesky@adwmainz.de
 *
 * Digital Academy www.digitale-akademie.de
 * Academy of Sciences and Literatur | Mainz www.adwmainz.des
 *
 * Licensed under The MIT License (MIT)
 *
 * @package ADWLM\IncipitSearch
 */
class SBNIncipitCrawler extends IncipitCrawler
{

    /**
     * Creates a CatalogEntry with Incipit from the data at the given URL.
     *
     * @param string $dataURL url of data in catalog
     * @param string $xml the xml data to parse
     * @return CatalogEntry null in case of error
     */
    public function catalogEntryFromHTML(string $dataURL, string $html, string $itemID) //can return null
    {
        $catalogHTML = new DOMDocument();
        $catalogHTML->loadHTML($html);

        $catalogItemID = $itemID;
        $incipitClef = "incipit chiave";
        $incipitAccidentals = "alterazioni:";
        $incipitTime = "misura";
        $incipitNotes = "contesto musicale";
        $composer = " Autore principale ";
        $title = "Titolo";
        $subtitle = " Presentazione ";
        $year = "year";

        $detailURL = $dataURL;

        $incipit = new Incipit($incipitNotes, $incipitClef, $incipitAccidentals, $incipitTime);
        $catalogEntry = new CatalogEntry($incipit, "SBN", $catalogItemID, $dataURL, $detailURL,
            $composer, $title, $subtitle, $year);

        return $catalogEntry;
    }


    /**
     * Crawls catalog and adds found CatalogEntries to ElasticSearch.
     * For SBN this just crawls a selection of about 200 entries.
     * This operation might take quite some time to complete.
     */
    public function crawlCatalog()
    {

        $startID = 1; // 0000001
        $endID =   3; // 0000003

        for ($i = $startID; $i < $endID; $i++) {
            $url = "http://opac.sbn.it/bid/MSM" . $i;
            $html = $this->contentOfURL($url);
            if ($html == null || strlen($html) == 0) {
                echo "error: crawlCatalog > not found at {$url}<br>\n";
                continue;
            }
            // create 8 digit number
            $itemID = sprintf('%07d', $i);
            $catalogEntry = $this->catalogEntryFromHTML($url, $html, $itemID);
            $this->addCatalogEntryToElasticSearchIndex($catalogEntry);
        }

    }

}