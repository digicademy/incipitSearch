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
    public static $notes = [
        "bC" => 11,
        "C" => 0,
        "xC" => 1,
        "bD" => 1,
        "D" => 2,
        "xD" => 3,
        "bE" => 3,
        "E" => 4,
        "xE" => 5,
        "bF" => 4,
        "F" => 5,
        "xF" => 6,
        "bG" => 6,
        "G" => 7,
        "xG" => 8,
        "bA" => 8,
        "A" => 9,
        "xA" => 10,
        "bB" => 10,
        "B" => 11,
        "xB" => 0,
    ];


    /**
     * Creates an incipit with transposition
     *
     * @param string $notesNormalizedToPitch incipit normalized to note values expanded accidentals(''A''B''xC'xF)
     *
     * @return string incipit with transposition
     */
    public static function transposeNormalizedNotes(string $notesNormalizedToPitch): string
    {
        if (empty($notesNormalizedToPitch)) {
            return '';
        }
        echo "NORMALIZED STRING: " . $notesNormalizedToPitch . "\n";

        $highOctaveValue = 0;
        $lowOctaveValue = 0;
        $accidentalValue = "";
        $pitchValues = array();

        /**
         * foreach (str_split($notesNormalizedToPitch) as $token) {
        echo "TOKEN: " . $token . "\n";
        if($token = ",")
        {
        $lowOctaveValue += 1;
        }
        elseif($token = "'")
        {
        $highOctaveValue += 1;
        }
        elseif(preg_match('/(x|b)/', $token, $matches))
        {
        $accidentalValue = implode($matches);
        echo "IM IN B" . $accidentalValue. "\n";
        }
        elseif(preg_match('/[A-G]/', $token))
        {
        // must be added in this order
        $noteString = $token . $accidentalValue;
        echo "NOTE STRING: " . $noteString . "\n";
        array_push($pitchValues, IncipitTransposer::calculatePitch($lowOctaveValue, $highOctaveValue, $noteString));
        $accidentalValue = "";
        $lowOctaveValue = 0;
        $highOctaveValue = 0;
        }
        else
        {
        echo 'Invalid Incipit';
        }
         */

            foreach (str_split($notesNormalizedToPitch) as $token) {
            echo "TOKEN: " . $token . "\n";
            switch ($token) {
                case ",":
                    $lowOctaveValue += 1;
                    break;
                case "'":
                    $highOctaveValue += 1;
                    break;
                case "x":
                    $accidentalValue = "x";
                    echo "IM IN X" . $accidentalValue. "\n";
                    break;
                case "b":
                    $accidentalValue = "b";
                    echo "IM IN B" . $accidentalValue. "\n";
                    break;
                case "A":
                    // must be added in this order
                    $noteString = $token . $accidentalValue;
                    echo "NOTE STRING A: " . $noteString . "\n";
                    array_push($pitchValues, IncipitTransposer::calculatePitch($lowOctaveValue, $highOctaveValue, $noteString));
                    $accidentalValue = "";
                    $lowOctaveValue = 0;
                    $highOctaveValue = 0;
                    break;
                case "B":
                    // must be added in this order
                    $noteString = $token . $accidentalValue;
                    echo "NOTE STRING: " . $noteString . "\n";
                    array_push($pitchValues, IncipitTransposer::calculatePitch($lowOctaveValue, $highOctaveValue, $noteString));
                    $accidentalValue = "";
                    $lowOctaveValue = 0;
                    $highOctaveValue = 0;
                    break;
                case "C":
                    // must be added in this order
                    $noteString = $token . $accidentalValue;
                    echo "NOTE STRING: " . $noteString . "\n";
                    array_push($pitchValues, IncipitTransposer::calculatePitch($lowOctaveValue, $highOctaveValue, $noteString));
                    $accidentalValue = "";
                    $lowOctaveValue = 0;
                    $highOctaveValue = 0;
                    break;
                case "D":
                    // must be added in this order
                    $noteString = $token . $accidentalValue;
                    echo "NOTE STRING: " . $noteString . "\n";
                    array_push($pitchValues, IncipitTransposer::calculatePitch($lowOctaveValue, $highOctaveValue, $noteString));
                    $accidentalValue = "";
                    $lowOctaveValue = 0;
                    $highOctaveValue = 0;
                    break;
                case "E":
                    // must be added in this order
                    $noteString = $token . $accidentalValue;
                    echo "NOTE STRING: " . $noteString . "\n";
                    array_push($pitchValues, IncipitTransposer::calculatePitch($lowOctaveValue, $highOctaveValue, $noteString));
                    $accidentalValue = "";
                    $lowOctaveValue = 0;
                    $highOctaveValue = 0;
                    break;
                case "F":
                    // must be added in this order
                    $noteString = $token . $accidentalValue;
                    echo "NOTE STRING: " . $noteString . "\n";
                    array_push($pitchValues, IncipitTransposer::calculatePitch($lowOctaveValue, $highOctaveValue, $noteString));
                    $accidentalValue = "";
                    $lowOctaveValue = 0;
                    $highOctaveValue = 0;
                    break;
                case "G":
                    // must be added in this order
                    $noteString = $token . $accidentalValue;
                    echo "NOTE STRING: " . $noteString . "\n";
                    array_push($pitchValues, IncipitTransposer::calculatePitch($lowOctaveValue, $highOctaveValue, $noteString));
                    $accidentalValue = "";
                    $lowOctaveValue = 0;
                    $highOctaveValue = 0;
                    break;
                case "H":
                    // must be added in this order
                    $noteString = $token . $accidentalValue;
                    echo "NOTE STRING: " . $noteString . "\n";
                    array_push($pitchValues, IncipitTransposer::calculatePitch($lowOctaveValue, $highOctaveValue, $noteString));
                    $accidentalValue = "";
                    $lowOctaveValue = 0;
                    $highOctaveValue = 0;
                    break;
                default:
                    echo 'Invalid Incipit';
                    break;
            }

/**
 *
switch ($token) {
case ",":
$lowOctaveValue += 1;
break;
case "'":
$highOctaveValue += 1;
break;
case preg_match('/(x|b)/', $token, $matches):
$accidentalValue = implode($matches);
echo "IM IN B" . $accidentalValue. "\n";
break;
case preg_match('/[A-G]/', $token):
// must be added in this order
$noteString = $token . $accidentalValue;
echo "NOTE STRING: " . $noteString . "\n";
array_push($pitchValues, IncipitTransposer::calculatePitch($lowOctaveValue, $highOctaveValue, $noteString));
$accidentalValue = "";
$lowOctaveValue = 0;
$highOctaveValue = 0;
break;
default:
echo 'Invalid Incipit';
break;
}
 */
        }

        return IncipitTransposer::calculateIntervals($pitchValues);
    }

    /**
     * @param $lowOctaveValue
     * @param $highOctaveValue
     * @param $noteValue
     */
    public static function calculatePitch($lowOctaveValue, $highOctaveValue, $noteString): int
    {
        $noteValue = IncipitTransposer::$notes[$noteString];
        echo "NOTE STRING 2: " . $noteString . "\n";

        if ($lowOctaveValue) {
            return (-12 * $lowOctaveValue) + $noteValue;
        } elseif ($highOctaveValue > 0) {
            return (12 * $highOctaveValue) + $noteValue;
        }

        return $noteValue;

    }

    /**
     * @param $pitchValues
     *
     * @return string
     */
    public static function calculateIntervals($pitchValues): string
    {
        $calculatedIntervals = "";
        $currentPitch = current($pitchValues);
        while(next($pitchValues) !== false)
        {
            $nextPitch = current($pitchValues);
            $intervalValue = $nextPitch - $currentPitch;
            $interval = (string) $intervalValue;
            $calculatedIntervals = $calculatedIntervals. " " .$interval;
            $currentPitch = current($pitchValues);
        }
        echo "ERGEBNIS: " . $calculatedIntervals . "\n";
        return $calculatedIntervals;

    }


}