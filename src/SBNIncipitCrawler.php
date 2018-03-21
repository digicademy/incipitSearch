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
 * (c) 2017
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
        // blocks PHP warnings caused by invalid HTML
        libxml_use_internal_errors(true);
        // GET HTML AND SAVE AS DOM
        $html = $this->contentOfURL($catalogEntryUrl);
        $catalogEntryDOM = new DOMDocument();
        $catalogEntryDOM->loadHTML($html);


        // EXTRACT TBODY: get and save content of body-tag
        $body = $catalogEntryDOM->getElementsByTagName('tbody');
        // maybe check if tag is existing
            $body = $body->item(0);
            $bodyHTML = $catalogEntryDOM->saveHTML($body);
        //echo "TBODY: " . $bodyHTML;
        //echo "URL: " . $catalogEntryUrl;

        // GET WORK INFORMATION - the information is the same for all incipits of the work

        // regex statements
        $composerRegEx = '/Autore principale\s*<\/td>\s*<td class="detail_value">(.*)<\/td>/';
        $titleRegEx = '/Titolo\s*<\/td>\s*<td class="detail_value">\s*<strong>(.*)<\/strong>/';
        $yearRegEx = '/Pubblicazione\s*<\/td>\s*<td class="detail_value">(.*)<br>/';


        //TODO: undefined offest errors appear in lines 79,96,98
        /*
         * 1. get pattern
         * 2. remove regex before and after needed information
         * 3. trim whitespaces
         */
        preg_match($composerRegEx, $bodyHTML, $matches);
        $composer = preg_replace('/Autore principale\s*<\/td>\s*<td class="detail_value">/', "", $matches[0]);
        $composer = preg_replace('/<\/td>/', "", $composer);
        $composer = trim($composer);
        //cleanup
        $composer = str_replace("&lt;", "", $composer);
        $composer = str_replace("&gt;", "", $composer);

        preg_match($titleRegEx, $bodyHTML, $matches);
        $title = preg_replace('/Titolo\s*<\/td>\s*<td class="detail_value">\s*<strong>/', "", $matches[0]);
        $title = preg_replace('/<\/strong>/', "", $title);
        $title = trim($title);
        // some titles are in quotation marks => remove them
        $title = trim($title, "\"");

        $subtitle = "";


        preg_match($yearRegEx, $bodyHTML, $matches);
        $year = preg_replace('/Pubblicazione\s*<\/td>\s*<td class="detail_value">/', "", $matches[0]);
        preg_match('/[0-9]+/', $year, $matches);
        $year = $matches[0];

        //echo " COMPOSER " . $composer . " TITLE " . $title . " YEAR " . $year;

        // GET INCIPITS - use regex to get encoded incipits: search for text in between var "incipit_1_1" and "</script>
        //$incipitRegex = '/incipit_1_[\\s|\\S]+load_data/';
        $incipitRegex = '/@clef:.*;/';
        preg_match_all($incipitRegex, $bodyHTML, $matches);
        //print_r($matches);


        $incipitEntryID = 0;
        $catalogEntries = [];
        foreach ($matches[0] as $incipit){
            $incipitUID = $catalogEntryID . "-" . $incipitEntryID ;
            $catalogEntry = $this->createCatalogEntry($incipit, $catalogEntryUrl, $incipitUID, $composer, $title, $subtitle, $year);
            $incipitEntryID++;
            array_push($catalogEntries, $catalogEntry);
           // echo "INCIPIT " . $incipitEntryID . "\n" . $incipit . "\n";
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
    public function createCatalogEntry($incipit, $dataURL, $incipitUID , $composer, $title, $subtitle, $year){

        /**
         * get specific values from $incipit:
         * example string for incipt: @clef:G-2\n@keysig:none\n@timesig:3/4      \n@data:6-{'FEF}{DAGA}{''D'AGA}\n";
         */
        $incipitClef ="";
        $incipitAccidentals = "";
        $incipitTime = "";
        $incipitNotes = "";

        $incipitValues = explode("\\n", $incipit);
        //get array that contains all values
        // trim array
        for($i = 0; $i < count($incipitValues); $i++){
            $incipitValues[$i] = trim($incipitValues[$i]);
            //trim everything before :
            $colonIndex = strpos($incipitValues[$i], ":");
            $incipitValues[$i] = substr($incipitValues[$i], $colonIndex+1);
            //echo "CLEANED VALUE: " . $incipitValues[$i];
        }

        $incipitClef = $incipitValues[0];
        if($incipitValues[1] != "none"){
            $incipitAccidentals = $incipitValues[1];
        }
        $incipitTime = $incipitValues[2];
        $incipitNotes = $incipitValues[3];


        // create incipit an catalog entry
        $detailURL = $dataURL;
        $incipit = new Incipit($incipitNotes, $incipitClef, $incipitAccidentals, $incipitTime);
        $catalogEntry = new CatalogEntry(
            $incipit,
            "SBN",
            $incipitUID,
            0,
            $dataURL,
            $detailURL,
            $composer,
            $title,
            $subtitle,
            $year)
        ;

        return $catalogEntry;
    }


    /**
     * Crawls catalog and adds found CatalogEntries to ElasticSearch.
     * For SBN this just crawls a selection of about 300 entries.
     * This operation might take quite some time to complete.
     */
    public function crawlCatalog()
    {
        $startID = 1; // 0000001
        $endID =   2000; // 0000300
        // real endID is 0178310

        for ($i = $startID; $i <= $endID; $i++) {
            // create 8 digit number used for catalogEntry and creation of URL
            $catalogEntryID = sprintf('%07d', $i);
            $catalogEntryUrl = "http://opac.sbn.it/bid/MSM" . $catalogEntryID;
            $html = $this->contentOfURL($catalogEntryUrl);
            if ($html == null || strlen($html) == 0) {
                echo "error: crawlCatalog > not found at {catalogEntryUrl}<br>\n";
                continue;
            }
            $catalogEntries = $this->catalogEntriesFromHTML($catalogEntryUrl, $catalogEntryID);
            foreach ($catalogEntries as $catalogEntry)
            {
            $this->addCatalogEntryToElasticSearchIndex($catalogEntry);
            }
        }

    }

}