<?php

namespace ADWLM\IncipitSearch;


/**
 * IncipitNormalizer normalizes Plaine and Easie (PAE) encoded incipits
 * to normalized formats.
 * This normalization is necessary because PAE code is intended to
 * represent musical notation. Because of its ambiguity, PAE code is
 * is not fit for string matching search.
 * Normalization brings PAE codes to a unambiguous format.
 *
 *
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
 *
 * @package ADWLM\IncipitSearch
 */
class IncipitNormalizer
{


    /**
     * Creates incipit normalized to note values witouth octave markers.
     *  Removes all infomation except the note names.
     *
     * E.g. ''8{AB}2-/4C'F with xCF accidentals
     * becomes ABxCxF
     *
     * @return string normalized incipit
     */
    public static function normalizeToSingleOctave(string $paeCode, Array $sharpSignatureAccidentals = null, Array $flatSignatureAccidentals = null): string
    {
        $normalized = str_replace(["'",","], '', self::normalizeToPitch($paeCode, $sharpSignatureAccidentals, $flatSignatureAccidentals));
        return $normalized;
    }

    /**
     * Creates incipit normalized to note values for use in search.
     * Removes rhythmic values, breaks and beamings but keeps all pitch values.
     * Applies accidentals and octave markers to each note.
     *
     * E.g. ''8{AB}2-/4C'F with xCF accidentals
     * becomes ''A''B''xC'xF
     *
     * @return string normalized incipit
     */
    public static function normalizeToPitch(string $paeCode, Array $sharpSignatureAccidentals = null, Array $flatSignatureAccidentals = null): string
    {

        //first remove all unnecessary chars
        $notes = preg_replace('/[^\/\',xbnA-Z]/', '', $paeCode);

        //expand the accidentals that are valid for a single measure
        $notes = self::expandMeasureWideAccidentals($notes);
        //now remove the meassure marks (bars)
        $notes = str_replace('/', '', $notes);

        $normalized = '';

        //the accidentals marked in the signature (valid for entire incipit)
        $sharpAccidentals = $sharpSignatureAccidentals ?? [];
        $flatAccidentals = $flatSignatureAccidentals ?? [];

        $notesLength = strlen($notes);
        $octave = "'"; //current octave mark (default is ')
        $currentAccidental = ""; //accidental for next / current note

        for ($i = 0; $i < $notesLength; $i++) {
            $char = $notes[$i];

            //get the current octave (can be multiple ' or multiple ,)
            if ($char == "'") {
                $octave = "'";
                //now look ahead for multiple '
                while (($i + 1) < $notesLength && $notes[$i + 1] == "'") {
                    $octave .= "'";
                    $i++;
                }
                continue;
            }

            if ($char == ",") {
                $octave = ",";
                //now look ahead for multiple '
                while (($i + 1) < $notesLength && $notes[$i + 1] == ",") {
                    $octave .= ",";
                    $i++;
                }
                continue;
            }


            if ($char == "b" || $char == "x" || $char == "n") {
                //set accidental for next note
                $currentAccidental = $char;
            } else {

                //we set the signature accidentals to each single note
                if (empty($currentAccidental) && in_array($char, $sharpAccidentals, true)) {
                    $currentAccidental = "x";
                } else if (empty($currentAccidental) && in_array($char, $flatAccidentals, true)) {
                    $currentAccidental = "b";
                }

                //if note is marked with neutral accidental, we set the accidental to blank
                if ($currentAccidental == "n") {
                    $currentAccidental = "";
                }

                $normalized .= $octave . $currentAccidental . $char;
                $currentAccidental = "";
            }

        }
        return $normalized;
    }


    /**
     * Creates incipit by expanding all measure wide accidentals.
     * When marking a note with an accidental, that accidental is valid
     * for all notes of the same pitch throughout the entire measure.
     * This function applies the accidentals to all matching notes within a measure.
     *
     * E.g. ''xFGF/ABbAA
     * becomes ''xFGxF/ABbAbA
     *
     * @return string incipit with expanded measure accidentals
     */
    public static function expandMeasureWideAccidentals(string $notes): string
    {

        $notesLength = strlen($notes);
        $measureSharpAccidentals = [];
        $measureFlatAccidentals = [];
        $measureNeutralAccidentals = [];
        $currentAccidental = ""; //this is for the current note
        $normalized = "";
        for ($i = 0; $i < $notesLength; $i++) {
            $char = $notes[$i];

            if ($char == "/") {
                //when the measure ends all accidentals are reset
                $measureSharpAccidentals = [];
                $measureFlatAccidentals = [];
                $measureNeutralAccidentals = [];
                $currentAccidental = "";
                $normalized .= $char;
            } else if ($char == "b" || $char == "x" || $char == "n") {
                //we remember the last seen accidental
                $currentAccidental = $char;
            } else if (in_array($char, ["A", "B", "C", "D", "E", "F", "G"]) ) {
                //if there was an accidental before the note we put the note in the measureAccidentals list
                //example: xFAF -> measureSharpAccidentals == [F]
                //          ^
                if ($currentAccidental == "x") {
                    array_push($measureSharpAccidentals, $char);
                } else if ($currentAccidental == "b") {
                    array_push($measureFlatAccidentals, $char);
                } else if ($currentAccidental == "n") {
                    array_push($measureNeutralAccidentals, $char);
                }

                //now we check if the current note is marked to have an accidental for this measure
                if (in_array($char, $measureSharpAccidentals)) {
                    $currentAccidental = "x";
                } else if (in_array($char, $measureFlatAccidentals)) {
                    $currentAccidental = "b";
                } else if (in_array($char, $measureNeutralAccidentals)) {
                    $currentAccidental = "n";
                }

                $normalized .= $currentAccidental . $char;

                $currentAccidental = ""; //reset current note accidental (measures are saved in list)

            } else {
                $normalized .= $char; //any other char is just kept
            }

        }
        return $normalized;
    }


}