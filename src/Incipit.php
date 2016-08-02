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


    protected $clef;
    protected $accidentals;
    protected $time;
    protected $notes;
    protected $completeIncipit;
    protected $notesNormalized;
    protected $notesNormalizedToPitch;

    /**
     * Incipit constructor.
     * @param string $notes
     * @param string|null $clef
     * @param string|null $accidentals
     * @param string|null $time
     */
    public function __construct(string $notes, string $clef = null,
                                string $accidentals = null, string $time = null)
    {
        $this->notes = $notes;
        $this->clef = $clef ?? "";
        $this->accidentals = $accidentals ?? "";
        $this->time = $time ?? "";
    }

    /**
     * Combines clef, accidentals, time and notes to one incipit string
     * @return mixed
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
     * Normalizes incipit for use in search: removes tone pitch and accidentals
     * @return string
     */
    public function getNotesNormalized(): string
    {
        if (empty($this->notesNormalized)) {
            $this->notesNormalized = preg_replace('/[\',]/', '', $this->getNotesNormalizedToPitch());
        }
        return $this->notesNormalized;
    }


    public function getSharpAccidentals() : Array {
        if (strlen($this->getAccidentals()) < 2) {
            return [];
        }
        if ($this->getAccidentals()[0] != "x") {
            return [];
        }
        $sharpAccidentals = [];
        for ($i = 1; $i < strlen($this->getAccidentals()); $i++) {
            array_push($sharpAccidentals, $this->getAccidentals()[$i]);
        }
        return $sharpAccidentals;
    }

    public function getFlatAccidentals() : Array {
        if (strlen($this->getAccidentals()) < 2) {
            return [];
        }
        if ($this->getAccidentals()[0] != "b") {
            return [];
        }
        $flatAccidentals = [];
        for ($i = 1; $i < strlen($this->getAccidentals()); $i++) {
            array_push($flatAccidentals, $this->getAccidentals()[$i]);
        }
        return $flatAccidentals;
    }

    /**
     * Normalizes incipit for use in search: removes accidentals
     * @return string
     */
    public function getNotesNormalizedToPitch(): string
    {
        if (!empty($this->notesNormalizedToPitch)) {
            return $this->notesNormalizedToPitch;
        }

        $notes = preg_replace('/[^\',xbnA-Z]/', '', $this->notes);
        $normalized = '';


        $sharpAccidentals = $this->getSharpAccidentals();
        $flatAccidentals = $this->getFlatAccidentals();

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
        $this->notesNormalizedToPitch = $normalized;

        return $this->notesNormalizedToPitch;
    }


    public static function getSanitizedIncipitString(string $dirtyString) : string
    {
        return preg_replace('/[^\',cbfgnoqrtxA-Z\d\{\}\/\-=()\.:\+;!%$@\^\?]|\s/', '', $dirtyString);
    }

    /**
     * Creates json array
     * @return mixed
     */
    public function getJSONArray(): Array {
        $json = ['notes' => $this->getNotes(),
            'clef' => $this->getClef(),
            'accidentals' => $this->getAccidentals(),
            'time' => $this->getTime(),
            'completeIncipit' => $this->getCompleteIncipit(),
            'normalizedIncipit' => $this->getNotesNormalized()
        ];
        return $json;
    }

    /**
     * Creates a new Incipit from a JSON associative array as generated by getJSONArray().
     *
     * The array must have the following keys: "notes"
     * The following keys are optional: "clef", "accidentals", "time"
     * @param array $json
     * @return Incipit
     */
    public static function incipitFromJSONArray(array $json): Incipit {
        $incipit = new Incipit( $json["notes"],$json["clef"], $json["accidentals"],$json["time"]);
        return $incipit;
    }




    //////////////////////////
    // GETTERS
    //////////////////////////

    /**
     * @return string
     */
    public function getClef()
    {
        return $this->clef;
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

    public static function filterPlaineEasieCode(string $input): string {
        $filterd = preg_replace('/[^$/a-zA-Z]/', '', $input);
    }
}