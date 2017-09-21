<?php

namespace ADWLM\IncipitSearch;


/**
 * IncipitTransposer transforms the normilized Incipit with accidentals mapped to each note into a notation with transposiiton.
 *
 *
 * Copyright notice
 *
 * (c) 2016
 * Anna Neovesky  Anna.Neovesky@adwmainz.de
 *
 * Digital Academy www.digitale-akademie.de
 * Academy of Sciences and Literatur | Mainz www.adwmainz.de
 *
 * Licensed under The MIT License (MIT)
 *
 * @package ADWLM\IncipitSearch
 */
class IncipitTransposer
{

    /**
     * Creates an incipit with transposition
     *
     * @param string $notesNormalizedToPitch incipit normalized to note values expanded accidentals(''A''B''xC'xF)
     *
     * @return string incipit with transposition
     */
    public static function transposeNormilizedNotes(string $notesNormalizedToPitch) : string
    {
        /**
         * dictionary mit noten anlegen (innehalb einer oktave (also cdefgah)
         * berechnen, in welcher oktave eine note liegt
         * dann den incipit string durchgehen und den Wert der ersten Note angeben
         * dann für jeden danach folgende Note berechnen, wie der unetrschied zu der vorherigen ist und speichern
         * string zurückgeben
         */

    }


}