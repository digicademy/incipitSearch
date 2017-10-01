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
        protected $composer = "Nicht angegeben";
        protected $title = "Nicht angegeben";
        protected $subtitle = "";
        protected $year = "Nicht angegeben";


    /**
     * Creates a CatalogEntry with Incipit from the data at the given URL.
     *
     * @param string $dataURL url of data in catalog
     * @param string $xml the xml data to parse
     * @return CatalogEntry null in case of error
     */
    public function catalogEntryFromHTML(string $dataURL, string $html, string $itemID) //can return null
    {
        /**
         * The incipits in each entry are stored as:
         *  <script type="text/javascript">
         *   var incipit_1_2 =
         *   "@clef:G-2\n@keysig:none\n@timesig:3/4      \n@data:''2F8FG/FE6({6EFE})8DEE\n";
         * load_data( incipit_1_2, $('#svg_1_2') );
         * </script>
         *
         * =>
         * begin with "var incipit_1_1" and "var incipit_1_2"
         * (later: see if there are further incipit variations)
         * end with "</script>"
         */


        $catalogHTML = new DOMDocument();
        $catalogHTML->loadHTML($html);

        //TODO: this doesn't work because it always gets full HTML;
        // check how to sav ethe HTML as string or as DOM

        // get and save content of body-tag
        $body = $catalogHTML->getElementsByTagName('body');
        // maybe check if is existing
            $body = $body->item(0);
            $bodyHTML = $catalogHTML->saveHTML($body);
            echo "BODY: " . $bodyHTML;

        $tbody  = $catalogHTML->getElementsByTagName('tbody');
        $tbody = $tbody->item(0);
            $tbodyHTML = $catalogHTML->saveHTML($tbody);
            echo "TBODY" . $tbodyHTML;


        //use regex to get encoded incipits: search for text in between var "incipit_1_1" and "</script>
        //TODO: find regex that gets each occurence splitted; this grabs the text until last occurence of regex
        $incipitRegex = '/incipit_1_[0-9][\\s|\\S]+load_data/';
        preg_match($incipitRegex, $bodyHTML, $matches);
        echo "MATCHES:" . print_r($matches);

        foreach ($matches as $match){
            echo "Match: " . $match;
            $this->createIncipitEntry($match, $itemID);
        }


        /**
         * Autor und Titel stehen in diesen Feldern:
         *
         * die "abbr" und die nummer weisen dabei immer auf das jeweilige Objekt hin
         *
         * th abbr=number > td class= detail value
         *
         * <th scope="col" abbr="700"/>
            <td class="detail_key">
            Autore principale
            </td>
            <td class="detail_value"> Barilli, Bruno</td>
            </tr>
            <tr>
            <th scope="col" abbr="200"/>
            <td class="detail_key">
            Titolo
            </td>
            <td class="detail_value">
            <strong> Emiral</strong>
            </td>
            </tr>
            <tr>
            <th scope="col" abbr="208"/>
            <td class="detail_key">
            Presentazione
            </td>
            <td class="detail_value">partitura e parti</td>
            </tr>
            <tr>
            <th scope="col" abbr="210"/>
            <td class="detail_key">
            Pubblicazione
            </td>
            <td class="detail_value"> : autografo in parte, 1915<br/>
            </td>
         *
         */





        /*
         * man könnte vorher hier noch die script tags, in denen die incipits stehen rausholen und dann
         * nur in denen suchen
         */
    }


    /**
     * @param $match
     * @param $dataURL
     * @param $itemID
     * @param $composer
     * @param $title
     * @param $subtitle
     * @param $year
     *
     * @return \ADWLM\IncipitSearch\CatalogEntry
     */
    public function createIncipitEntry($match, $dataURL, $itemID, $composer, $title, $subtitle, $year){
        $catalogItemID = $itemID;
        $incipitClef = "incipit chiave";
        $incipitAccidentals = "alterazioni:";
        $incipitTime = "misura";
        $incipitNotes = "contesto musicale";

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