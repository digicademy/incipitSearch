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
    public static function transposeNormalizedNotes(string $notesNormalizedToPitch) : string
    {
        /**
         * dictionary mit noten anlegen (innehalb einer oktave (also cdefgah)
		 * FV: Die Grundidee. Jeder Note eine Zahl zuweisen, den Halbtonschritten entsprechend und jeder Oktave ebenso. Die Summen daraus ergeben dann die Intervalle. Hier werden also nun die Noten ihren entsprechenden Stufen zugewiesen, ausgehend von c'"
		 */

        $chords = [
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

		/**
         * berechnen, in welcher oktave eine note liegt
		 * FV: Eine Oktave umfasst immer 12 Halbtonschritte. Es gibt also immer einen Unterschied von 12.*/

		$octave = [
			",,,," => -50,
			",,," => -36,
			",," => -24,
			"," => -12,
			"'" => 0,
			"''" => 12,
			"'''" => 24,
			"''''" => 36,
			"'''''" => 50
		];

		/**
         * dann den incipit string durchgehen und den Wert der ersten Note angeben */

		/**
		 * FV: Ich versuche hier das Incipit in einzelne Informationseinheiten aufzusplitten.
		 * Aus ''A''B''xC'xF soll ["''A", "''B", "''xC", "'xF"] werden.
		 * Mein Ziel war es aus bspw. ''A im Ergebnis 12 + 9, also den Wert 21 zu bilden.
		 */

		$notes = preg_split('/(?=[A-Z])/', $notesNormalizedToPitch, -1, PREG_SPLIT_NO_EMPTY);

		/**
		 * FV: Hier soll also aus ["''A", "''B", "''xC", "'xF"] "21, 22, 13, 6" werden.
		 */

		foreach ($notes as $note) {
			$notes[$note] = strtr($notes, array_sum($chords, $octave));

		}

		/**
		 * dann für jeden danach folgende Note berechnen, wie der unetrschied zu der vorherigen ist und speichern
		 * string zurückgeben
		 * FV: Sorry, hier hat mir Google auf die Schnelle nicht weitergeholen. Jetzt müsste aus "21, 22, 13, 6" "1, 9, 7" werden. Dann hätten wir m.E. das gewünschte Ergebnis.
		 */

    }


}