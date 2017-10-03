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
     * @param string $catalogEntryUrl url of data in catalog
     * @param string string $catalogEntryID ID of the entry
     * @return CatalogEntries Array of catalog entries
     */
    public function catalogEntriesFromHTML(string $catalogEntryUrl, string $catalogEntryID) //can return null
    {

        // GET HTML AND SAVE AS DOM
        $html = $this->contentOfURL($catalogEntryUrl);
        $catalogEntryDOM = new DOMDocument();
        $catalogEntryDOM->loadHTML($html);


        // EXTRACT BODY: get and save content of body-tag
        $body = $catalogEntryDOM->getElementsByTagName('body');
        // maybe check if tag is existing
            $body = $body->item(0);
            $bodyHTML = $catalogEntryDOM->saveHTML($body);
        //TODO: this doesn't work because it always gets full HTML;
        echo "BODY: " . $bodyHTML;

        //TODO: extract tbody, this can be used to get the works information
        // $tbody  = $catalogHTML->getElementsByTagName('tbody');

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

        // GET WORK INFORMATION
        // the information is the same for all incipits of the work
        $composer = "";
        $title = "";
        $subtitle = "";
        $year = "";

        // GET INCIPITS
        //use regex to get encoded incipits: search for text in between var "incipit_1_1" and "</script>
        //TODO: find regex that gets each occurence splitted; this grabs the text until last occurence of regex
        $incipitRegex = '/incipit_1_[0-9][\\s|\\S]+load_data/';
        preg_match($incipitRegex, $bodyHTML, $matches);
        echo "MATCHES:" . print_r($matches);

        $incipitEntryID = 0;
        $catalogEntries = [];
        foreach ($matches as $incipitPAE){
            $incipitUID = $catalogEntryID . "-" . $incipitEntryID ;
            $catalogEntry = createCatalogEntry($incipitPAE, $catalogEntryUrl, $incipitUID, $composer, $title, $subtitle, $year);
            $incipitEntryID++;
            array_push($catalogEntries, $catalogEntry);

        }

        return $catalogEntries;
    }




    /**
     * @param $match
     * @param $dataURL
     * @param $catalogEntry
     * @param $composer
     * @param $title
     * @param $subtitle
     * @param $year
     *
     * @return \ADWLM\IncipitSearch\CatalogEntry
     */
    public function createCatalogEntry($incipitPAE, $dataURL, $incipitUID , $composer, $title, $subtitle, $year){

        //TODO add functionality to grab incipit encoding using regex

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

        $incipitClef = "incipit chiave";
        $incipitAccidentals = "alterazioni:";
        $incipitTime = "misura";
        $incipitNotes = "contesto musicale";

        // create incipit an catalog entry
        $detailURL = $dataURL;
        $incipit = new Incipit($incipitNotes, $incipitClef, $incipitAccidentals, $incipitTime);
        $catalogEntry = new CatalogEntry($incipit, "SBN", $incipitUID, $dataURL, $detailURL,
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
        // real endID is 0178310

        for ($i = $startID; $i < $endID; $i++) {
            $catalogEntryUrl = "http://opac.sbn.it/bid/MSM" . $i;
            $html = $this->contentOfURL($catalogEntryUrl);
            if ($html == null || strlen($html) == 0) {
                echo "error: crawlCatalog > not found at {catalogEntryUrl}<br>\n";
                continue;
            }
            // create 8 digit number
            $catalogEntryID = sprintf('%07d', $i);
            $catalogEntries = $this->catalogEntriesFromHTML($catalogEntryUrl, $catalogEntryID);
            foreach ($catalogEntries as $catalogEntry)
            {
            $this->addCatalogEntryToElasticSearchIndex($catalogEntry);
            }
        }

    }

}