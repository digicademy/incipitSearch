<?php
/**
 *
 * Anna Neovesky  Anna.Neovesky@adwmainz.de
 * Gabriel Reimers Gabriel@wokabulary.com
 *
 */

namespace ADWLM\IncipitSearch;

require_once "Incipit.php";

class IncipitEntry
{

    protected $catalog;
    protected $catalogItemID;
    protected $dataURL;
    protected $detailURL;
    protected $incipit;
    protected $composer;
    protected $title;
    protected $year;

    public function __construct(Incipit $incipit, string $catalog, string $catalogItemID,
                                string $dataURL, string $detailURL,
                                string $composer, string $title, string $year = null)
    {
        $this->incipit = $incipit;
        $this->catalog = $catalog;
        $this->catalogItemID = $catalogItemID;
        $this->dataURL = $dataURL;
        $this->detailURL = $detailURL;
        $this->composer = $composer;
        $this->title = $title;
        $this->year = $year ?? "";

    }


    public function getDictionaryRepresentation(): Array {
        $dict = ['catalog' => $this->getCatalog(),
            'catalogItemID' => $this->getCatalogItemID(),
            'dataURL' => $this->getDataURL(),
            'detailURL' => $this->getDetailURL(),
            'incipit' => $this->getIncipit()->getDictionaryRepresentation(),
            'composer' => $this->getComposer(),
            'title' => $this->getTitle(),
            'year' => $this->getYear()
        ];
        return $dict;
    }

    public function getJSONRepresentation(): string
    {
        $dict = $this->getDictionaryRepresentation();
        $json = json_encode($dict);
        return $json;
    }





    ///////////////////////
    // GETTERS
    ///////////////////////

    /**
     * @return string
     */
    public function getCatalog(): string
    {
        return $this->catalog;
    }

    /**
     * @return string
     */
    public function getCatalogItemID(): string
    {
        return $this->catalogItemID;
    }

    /**
     * @return string
     */
    public function getDataURL(): string
    {
        return $this->dataURL;
    }

    /**
     * @return string
     */
    public function getDetailURL(): string
    {
        return $this->detailURL;
    }



    /**
     * @return string
     */
    public function getComposer(): string
    {
        return $this->composer;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * @return Incipit
     */
    public function getIncipit()
    {
        return $this->incipit;
    }
}

