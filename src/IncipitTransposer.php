<?php

namespace ADWLM\IncipitSearch;

/**
 * IncipitTransposer transforms the normilized Incipit,
 * with accidentals mapped to each note into a notation with transposiiton.
 *
 *
 * Copyright notice
 *
 * (c) 2016
 * Anna Neovesky  Anna.Neovesky@adwmainz.de
 * Frederic von Vlahovits Frederic.vonVlahovits@adwmainz.de
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

    // notes with value assigned to each semitone
    public static $notes = ['xB', 'C', 'xC', 'bD', 'D', 'xD', 'bE', 'E', 'bF', 'xE', 'F', 'xF', 'bG', 'G', 'xG', 'bA', 'A', 'xA' , 'bB', 'B', 'bC'];

    /**
     * Creates an incipit with transposition (relative distance between two pitches)
     *
     * @param string incipit normalized to note values expanded accidentals(ABxCxF)
     *
     * @return string incipit with transposition
     */
    public static function transposeNormalizedNotes(string $incipit): string
    {
        if (empty($incipit) || !preg_match('/[xbCDEFGAB]/', $incipit[0])) {
            return '';
        }

        $transposedNotes = '';
        $notesIndex = [];

        // default values
        $accidentalValue = ''; // x or b
        $initialNote = '';

        if (preg_match('/[xbCDEFGAB]/', $incipit[0])) {
            if ($incipit[0] === 'x') {
                $initialNote = 'x' . $incipit[1];
            } elseif ($incipit[0] === 'b') {
                $initialNote = 'b' . $incipit[1];
            } else {
                $initialNote = $incipit[0];
            }
        };

        $x = 0;

        $i = array_search($initialNote, IncipitTransposer::$notes);

        for($i; $i < count(IncipitTransposer::$notes); $i++) {
            if (IncipitTransposer::$notes[$i] === 'xB' ||
                IncipitTransposer::$notes[$i] === 'xC' ||
                IncipitTransposer::$notes[$i] === 'xD' ||
                IncipitTransposer::$notes[$i] === 'E' ||
                IncipitTransposer::$notes[$i] === 'xE' ||
                IncipitTransposer::$notes[$i] === 'xF' ||
                IncipitTransposer::$notes[$i] === 'xG' ||
                IncipitTransposer::$notes[$i] === 'A' ||
                IncipitTransposer::$notes[$i] === 'B') {
                $notesIndex[IncipitTransposer::$notes[$i]] = $x;
            } else {
                $notesIndex[IncipitTransposer::$notes[$i]] = $x;
                $x++;
            }
        }

        for($a = 0; $a < array_search($initialNote, IncipitTransposer::$notes); $a++) {
            if (IncipitTransposer::$notes[$a] === 'xB'||
                IncipitTransposer::$notes[$a] === 'xC'||
                IncipitTransposer::$notes[$a] === 'xD'||
                IncipitTransposer::$notes[$a] === 'E' ||
                IncipitTransposer::$notes[$a] === 'xE'||
                IncipitTransposer::$notes[$a] === 'xF'||
                IncipitTransposer::$notes[$a] === 'xG'||
                IncipitTransposer::$notes[$a] === 'A' ||
                IncipitTransposer::$notes[$a] === 'B') {
                $notesIndex[IncipitTransposer::$notes[$a]] = $x;
            } else {
                $notesIndex[IncipitTransposer::$notes[$a]] = $x;
                $x++;
            }
        }

        foreach (str_split($incipit) as $token) {
            if (preg_match('/[xb]/', $token)) {
                $accidentalValue = $token;
            }
            if (preg_match('/[CDEFGAB]/', $token)) {
                $note = $accidentalValue . $token;
                $transposedNotes .= $notesIndex[$note] . ' ';
                $accidentalValue = '';
            }
            else {
                continue;
            }
        }

        return $transposedNotes;

    }
}
