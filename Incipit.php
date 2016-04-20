<?php
/**
 * Created by PhpStorm.
 * User: gaby
 * Date: 20/04/16
 * Time: 12:27
 */

namespace ADWLM\IncipitSearch;


class Incipit
{


    protected $key;
    protected $accidentals;
    protected $time;
    protected $notes;
    protected $completeIncipit;
    protected $notesNormalized;

    public function __construct(string $notes, string $key = null,
                                string $accidentals = null, string $time = null)
    {
        $this->notes = $notes;
        $this->key = $key ?? "";
        $this->accidentals = $accidentals ?? "";
        $this->time = $time ?? "";
    }

    /**
     * @return mixed
     */
    public function getCompleteIncipit(): string
    {
        if (empty($this->completeIncipit)) {
            $this->completeIncipit = $this->key . $this->accidentals .
                $this->time . $this->notes;
        }
        return $this->completeIncipit;
    }

    public function getNotesNormalized(): string
    {
        if (empty($this->notesNormalized)) {
            $notes = $this->notes;
            $this->notesNormalized = preg_replace('/[^a-zA-Z]/', '', $notes);
        }
        return $this->notesNormalized;
    }



    public function getDictionaryRepresentation(): Array {
        $dict = ['key' => $this->getKey(),
            'accidentals' => $this->getAccidentals(),
            'time' => $this->getTime(),
            'notes' => $this->getNotes(),
            'completeIncipit' => $this->getCompleteIncipit(),
            'normalizedIncipit' => $this->getNotesNormalized()
        ];
        return $dict;
    }




    //////////////////////////
    // GETTERS
    //////////////////////////

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getAccidentals()
    {
        return $this->accidentals;
    }

    /**
     * The time signature.
     *
     * This is usually a fraction like '2/4' but can also be 'c' for commom time.
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }
}