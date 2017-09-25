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
    // array of chords with numerical values representing the pitch
    public static $chords = [

    "C" => 0,
    "xC" => 1,
    "D" => 2,
    "xD" => 3,
    "E" => 4,
    "F" => 5,
    "xF" => 6,
    "G" => 7,
    "xG" => 8,
    "A" => 9,
    "xA" => 10,
    "B" => 11
    ];

    // numerical values assigned
    public static $octave = [
    ",,,," => -48,
    ",,," => -36,
    ",," => -24,
    "," => -12,
    "'" => 0,
    "''" => 12,
    "'''" => 24,
    "''''" => 36,
    "'''''" => 48
    ];
    

    /**
     * Creates an incipit with transposition
     *
     * @param string $notesNormalizedToPitch incipit normalized to note values expanded accidentals(''A''B''xC'xF)
     *
     * @return string incipit with transposition
     */
    public static function transposeNormalizedNotes(string $notesNormalizedToPitch) : string
    {
        if(empty($notesNormalizedToPitch)){
            return '';
        }

		/**
         * dann den incipit string durchgehen und den Wert der ersten Note angeben */

		/**
		 * FV: Ich versuche hier das Incipit in einzelne Informationseinheiten aufzusplitten.
		 * Aus ''A''B''xC'xF soll ["''A", "''B", "''xC", "'xF"] werden.
		 * Mein Ziel war es aus bspw. ''A im Ergebnis 12 + 9, also den Wert 21 zu bilden.
		 */


        /*
         * AN: delimiter wird herausgelöscht, es muss PREG_SPLIT_DELIM_CAPTURE gesetzt werden
         * => nun stehen die Noten aber immer in einem eigenen Eintrag im Array
         * und müssen dann erst wieder zusammengebracht werden und je nachdem ob es eine
         *
         * andere Möglichkeiten:
         * 1) ausgehend von dem jetzigen Stand (oder vielleicht doch so splitten, dass auch die Vorzeichen nochal extra sind)
         *  durchgehen udn dann die Berechnung anstellen
         * 2) Array durchiterieren und die Oktave jeweils "hochzählen" und dann den Wert setzen, dann chord ermitteln, dann Wert berechnen
         *      => wahrscheinlich die sinnvollste Lösung
         * 3) viell. http://php.net/manual/de/function.preg-match-all.php
         */
		$notes = preg_split('/([A-Z])/', $notesNormalizedToPitch, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        print "Notes array: ";
        $i = 0;
		foreach ($notes as $note){
            print  $notes[$i] . ' | ';
            $i++;
        }


		/**
		 * FV: Hier soll also aus ["''A", "''B", "''xC", "'xF"] "21, 22, 13, 06" werden.
		 */

		foreach ($notes as $note) {
			//$notes[$note] = strtr($notes, array_sum(IncipitTransposer::$chords, IncipitTransposer::$octave));

		}

		/**
		 * dann für jeden danach folgende Note berechnen, wie der unetrschied zu der vorherigen ist und speichern
		 * string zurückgeben
		 * vorerst wird ein Teststring übergeben
		 */
		return "0,2,4,4,6";


    }


}