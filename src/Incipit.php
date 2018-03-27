<?php

namespace ADWLM\IncipitSearch;

/**
 * The Incipit class represents a single incipit in Plaine and Easie code.
 * To work conveniently with the Veriovio rendering kit, it separates clef,
 * accidentals, time and notes.
 *
 * To allow searching for incipits by string matching it provides
 * normalization functions that resolve ambiguity in the PAE-Encoding.
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
class Incipit
{


    protected $clef;
    protected $accidentals;
    protected $time;
    protected $notes;
    protected $completeIncipit;
    protected $notesNormalized;
    protected $notesWithoutOrnaments;
    protected $notesNormalizedToPitch;
    protected $transposedNotes;

    /**
     * Incipit constructor.
     *
     * @param string      $notes
     * @param string|null $clef        will be set to empty if null
     * @param string|null $accidentals will be set to empty if null
     * @param string|null $time        will be set to empty if null
     */
    public function __construct(
        string $notes,
        string $clef = null,
        string $accidentals = null,
        string $time = null
    ) {
        $this->notes = $notes;
        $this->clef = $clef ?? '';
        $this->accidentals = $accidentals ?? '';
        $this->time = $time ?? '';
    }

    /**
     * Creates a single incipit string by combining clef, accidentals, time and notes.
     *
     * @return string
     */
    public function getCompleteIncipit(): string
    {
        if (empty($this->completeIncipit)) {
            $this->completeIncipit = '%' . $this->clef . '$' . $this->accidentals .
                '@' . $this->time . $this->notes;
        }

        return $this->completeIncipit;
    }

    /**
     * Creates incipit normalized on a single octave for use in search.
     * Removes all infomation except the note names.
     *
     * E.g. ''8{AB}2-/4C'F with xCF accidentals
     * becomes ABxCxF
     *
     * @return string incipit normalized on single octave
     */
    public function getNotesNormalizedToSingleOctave(): string
    {
        if (empty($this->notesNormalized)) {
            //we don't call the IncipitNormalizer function here
            //because normalizedToPitch has to be called anyway and is buffered here
            $this->notesNormalized = str_replace(["'", ","], '', $this->getNotesNormalizedToPitch());
        }

        return $this->notesNormalized;
    }

    /**
     * Creates incipit without Ornaments for use in search.
     * Removes all infomation except the note names including ornaments.
     *
     * E.g. 4'A/2''Cq8D{C'Bq''C'BA}
     * becomes ACCBBA
     *
     * @return string incipit without Ornaments
     */
    public function getNotesWithoutOrnaments(): string
    {
        if (!empty($this->notesWithoutOrnaments)) {
            return $this->notesWithoutOrnaments;
        }

        $this->notesWithoutOrnaments = IncipitNormalizer::normalizeOrnaments(
            $this->notes,
            $this->getSharpAccidentals(),
            $this->getFlatAccidentals()
        );

        return $this->notesWithoutOrnaments;
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
    public function getNotesNormalizedToPitch(): string
    {
        // cached variable
        if (!empty($this->notesNormalizedToPitch)) {
            return $this->notesNormalizedToPitch;
        }

        $this->notesNormalizedToPitch = IncipitNormalizer::normalizeToPitch(
            $this->notes,
            $this->getSharpAccidentals(),
            $this->getFlatAccidentals()
        );

        return $this->notesNormalizedToPitch;
    }

    /**
     * Creates incipit string containing proportional information on up and
     * down pitch only (transposition)
     *
     * Starting point is  getNotesNormalizedToPitch, as it contains a normalized
     * incipit with accidentals marked at each note
     * E.g.
     *
     * @return string string containing transposition only
     */
    public function getTransposedNotes(): string
    {
        if (!empty($this->transposedNotes)) {
            return $this->transposedNotes;
        }
        $this->transposedNotes = IncipitTransposer::transposeNormalizedNotes(
            $this->getNotesNormalizedToSingleOctave()
        );

        return $this->transposedNotes;
    }


    /**
     * Creates a string that only contain valid PAE characters.
     *
     * @param string $dirtyString the string to sanatize
     *
     * @return string
     */
    public static function getSanitizedIncipitString(string $dirtyString): string
    {
        return preg_replace('/[^\',cbfgnoqrtxA-Z\d\{\}\/\-=()\.:\+;!%$@\^\?]|\s/', '', $dirtyString);
    }

    /**
     * Creates JSON associative array from the Incipit.
     *
     * The array has the following keys: "notes", "clef", "accidentals",
     * "time", "completeIncipit", "normalizedIncipit"
     *
     * @return array of string
     */
    public function getJSONArray(): Array
    {
        $json = [
            'notes' => $this->getNotes(),
            'clef' => $this->getClef(),
            'accidentals' => $this->getAccidentals(),
            'time' => $this->getTime(),
            'completeIncipit' => $this->getCompleteIncipit(),
            'normalizedToSingleOctave' => $this->getNotesNormalizedToSingleOctave(),
            'withoutOrnaments' => $this->getNotesWithoutOrnaments(),
            'normalizedToPitch' => $this->getNotesNormalizedToPitch(),
            'transposedNotes' => $this->getTransposedNotes()
        ];

        return $json;
    }

    /**
     * Creates a new Incipit from a JSON associative array as generated by getJSONArray().
     *
     * The array must have the following keys: "notes"
     * The following keys are optional: "clef", "accidentals", "time"
     *
     * @param array $json
     *
     * @return Incipit
     */
    public static function incipitFromJSONArray(array $json): Incipit
    {
        $incipit = new Incipit(
            $json['notes'],
            $json['clef'],
            $json['accidentals'],
            $json['time']
        );

        return $incipit;
    }


    /**
     * Returns the note values of all sharp accidentals.
     *
     * E.g. for 'xFC' accidentals this returns ['F', 'C']
     *
     * @return array of strings with note values of sharp accidentals; empty if none
     */
    public function getSharpAccidentals(): Array
    {
        if (strlen($this->getAccidentals()) < 2) {
            return [];
        }
        if ($this->getAccidentals()[0] != 'x') {
            return [];
        }
        $sharpAccidentals = [];
        for ($i = 1; $i < strlen($this->getAccidentals()); $i++) {
            array_push($sharpAccidentals, $this->getAccidentals()[$i]);
        }

        return $sharpAccidentals;
    }

    /**
     * Returns the note values of all flat accidentals.
     *
     * E.g. for 'bBE' accidentals this returns ['B', 'E']
     *
     * @return array of strings with note values of sharp accidentals; empty if none
     */
    public function getFlatAccidentals(): Array
    {
        if (strlen($this->getAccidentals()) < 2) {
            return [];
        }
        if ($this->getAccidentals()[0] != 'b') {
            return [];
        }
        $flatAccidentals = [];
        for ($i = 1; $i < strlen($this->getAccidentals()); $i++) {
            array_push($flatAccidentals, $this->getAccidentals()[$i]);
        }

        return $flatAccidentals;
    }

    //////////////////////////
    // GETTERS
    //////////////////////////

    /**
     * The clef.
     *
     * Usually looks like 'G-2'
     *
     * @return string empty if none
     */
    public function getClef()
    {
        return $this->clef;
    }

    /**
     * The accidentals.
     *
     * Usually looks like 'xFC' or ''
     *
     * @return string empty if none
     */
    public function getAccidentals()
    {
        return $this->accidentals;
    }

    /**
     * The time signature.
     *
     * This is usually a fraction like '2/4' but can also be 'c' for commom time.
     *
     * @return string empty if none
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * The actual musical notes in PAE code.
     *
     * @return string empty if none
     */
    public function getNotes()
    {
        return $this->notes;
    }

}