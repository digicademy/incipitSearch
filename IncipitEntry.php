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


class IncipitEntry
{

// TODO: getter & setter
    /**
     * @var  catalog
     */
    public $catalog;

    /**
     * @var
     */
    public $catalogItemID;

    /**
     * @var
     */
    public $dataURL;

    /**
     * @var
     */
    public $detailURL;

    /**
     * @var
     */
    public $incipitKey;

    /**
     * @var
     */
    public $incipitAccidentals;

    /**
     * @var
     */
    public $incipitTime;

    /**
     * @var
     */
    public $incipitNotes;

    /**
     * @var
     */
    public $incipitFull;

    /**
     * @var
     */
    public $composer;

    /**
     * @var
     */
    public $title;

    /**
     * @var
     */
    public $year;

    /**
    public function __construct($catalog, $catalogID)
    {

    }

     **/

    public function json()
    {
        return json_encode($this);
    }
}

