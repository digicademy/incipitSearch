<?php

     namespace ADWLM\IncipitSearch;

     require_once "SearchQuery.php";

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

     $incipit = $_POST["incipit"];

     $searchQuery = new SearchQuery();
     $searchQuery->setQuery($incipit);
     $resultSet = $searchQuery->performSearchQuery();

     echo $resultSet;
