<?php
/**
 *
 * Anna Neovesky  Anna.Neovesky@adwmainz.de
 * Gabriel Reimers Gabriel@wokabulary.com
 *
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

    // complete incipit?

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

