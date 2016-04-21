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

        public function __construct(Incipit $incipit = null, string $catalog = null, string $catalogItemID = null,
                                    string $dataURL = null, string $detailURL = null,
                                    string $composer = null, string $title = null, string $year = null)
        {
            if ($incipit == null) {
                echo "IncipitEntry > construct > incipit is null";
            }
            if ($catalogItemID == null) {
                echo "IncipitEntry > construct > catalogItemID is null";

            }
            //TODO: catch invalid data
            $this->incipit = $incipit;
            $this->catalog = $catalog ?? "";
            $this->catalogItemID = $catalogItemID ?? "";
            $this->dataURL = $dataURL ?? "";
            $this->detailURL = $detailURL ?? "";
            $this->composer = $composer ?? "";
            $this->title = $title ?? "";
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

        public static function incipitEntryFromDictionary(array $dict): IncipitEntry
        {
            $incipit = Incipit::incipitFromDicitonary($dict["incipit"]);
            $incipitEntry = new IncipitEntry($incipit, $dict["catalog"],
            $dict["calatogItemID"],
            $dict["dataURL"],
            $dict["detailURL"], $dict["composer"], $dict["title"], $dict["year"]);
            return $incipitEntry;
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

