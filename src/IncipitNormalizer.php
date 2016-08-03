<?php
/**
 * Created by PhpStorm.
 * User: gaby
 * Date: 03/08/16
 * Time: 10:29
 */

namespace ADWLM\IncipitSearch;


class IncipitNormalizer
{


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


        $notes = preg_replace('/[^\',xbnA-Z]/', '', $paeCode);
        $normalized = '';


        $sharpAccidentals = $sharpSignatureAccidentals ?? [];
        $flatAccidentals = $flatSignatureAccidentals ?? [];

        $notesLength = strlen($notes);
        $octave = "'";
        $currentAccidental = "";

        for ($i = 0; $i < $notesLength; $i++) {
            $char = $notes[$i];

            if ($char == "'") {
                $octave = "'";
                while (($i + 1) < $notesLength && $notes[$i + 1] == "'") {
                    $octave .= "'";
                    $i++;
                }
                continue;
            }

            if ($char == ",") {
                $octave = ",";
                while (($i + 1) < $notesLength && $notes[$i + 1] == ",") {
                    $octave .= ",";
                    $i++;
                }
                continue;
            }

            if ($char == "b" || $char == "x" || $char == "n") {
                $currentAccidental = $char;
            } else {

                //we add the accidentals to each single note
                if (empty($currentAccidental) && in_array($char, $sharpAccidentals, true)) {
                    $currentAccidental = "x";
                } else if (empty($currentAccidental) && in_array($char, $flatAccidentals, true)) {
                    $currentAccidental = "b";
                }

                //as accidentals have already been applied, the n can be removed
                if ($currentAccidental == "n") {
                    $currentAccidental = "";
                }

                $normalized .= $octave . $currentAccidental . $char;
                $currentAccidental = "";
            }

        }
        return $normalized;
    }

}