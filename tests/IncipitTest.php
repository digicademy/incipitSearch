<?php

/**
 * Created by PhpStorm.
 * User: gaby
 * Date: 31/05/16
 * Time: 09:40
 */

require dirname(__DIR__) . '/src/Incipit.php';

use \ADWLM\IncipitSearch\Incipit;

class IncipitTest extends PHPUnit_Framework_TestCase
{
    private $incipit1;
    private $incipit2;
    private $incipit3;
    private $incipit4;
    private $incipit5;

    public function setUp() {
        $this->incipit1 = new Incipit("''4D8DD4D-/DFAF/8GG4--G/EFGE/F--", "G-2", "xFC", "c");
        $this->incipit2 = new Incipit("''4DDD/D--/,D,,FF/FGF/gF4EDE/2xE4F/", "C-1", "xFC", "3/4");
        $this->incipit3 = new Incipit("=14/{''6GD}4D8E/{8.C'3B''C8D}C/{'6.B''3C8D}-nE/D{6EC}'8B6''C'A/B{AG}4G", "C-1", "bBE", "2/4");
        $this->incipit4 = new Incipit("=1/2-4(-)'8AA/4''DD-D/gF4EE-E/nFD'B''E/({8CDC})({'B''C'B})4A8-", "C-1", "xFC", "c");
        $this->incipit5 = new Incipit("''8-{CD}+/{DC'B}/{xAA''F+}/{FED}/{nCGB+}/{bBBG+}/{GFE}/", "G-2", "xFCG", "3/8");
    }

    public function testConstructor() {
        $incipit = new Incipit("''4D8DD4D-/DFAF/8GG4--G/EFGE/F--");
        $this->assertEquals("''4D8DD4D-/DFAF/8GG4--G/EFGE/F--", $incipit->getNotes());
        $this->assertEmpty($incipit->getClef());
        $this->assertEmpty($incipit->getAccidentals());
        $this->assertEmpty($incipit->getTime());

        $incipit = new Incipit("''4D8DD4D-/DFAF/8GG4--G/EFGE/F--", "G-2", "xFC", "c");
        $this->assertEquals("''4D8DD4D-/DFAF/8GG4--G/EFGE/F--", $incipit->getNotes());
        $this->assertEquals("G-2", $incipit->getClef());
        $this->assertEquals("xFC", $incipit->getAccidentals());
        $this->assertEquals("c", $incipit->getTime());
    }


    public function testNormalizationToPitch() {
        $normalized1 = $this->incipit1->getNotesNormalizedToPitch(); //xFC accidentals
        $this->assertEquals("''D''D''D''D''D''xF''A''xF''G''G''G''E''xF''G''E''xF", $normalized1);

        $normalized2 = $this->incipit2->getNotesNormalizedToPitch(); //xFC accidentals
        $this->assertEquals("''D''D''D''D,D,,xF,,xF,,xF,,G,,xF,,xF,,E,,D,,E,,xE,,xF", $normalized2);

        $normalized3 = $this->incipit3->getNotesNormalizedToPitch(); //bBE accidentals
        $this->assertEquals("''G''D''D''bE''C'bB''C''D''C'bB''C''D''E''D''bE''C'bB''C'A'bB'A'G'G", $normalized3);

        $normalized4 = $this->incipit4->getNotesNormalizedToPitch(); //xFC accidentals
        $this->assertEquals("'A'A''D''D''D''xF''E''E''E''F''D'B''E''xC''D''xC'B''xC'B'A", $normalized4);

        $normalized5 = $this->incipit5->getNotesNormalizedToPitch(); //xFCG accidentals
        $this->assertEquals("''xC''D''D''xC'B'xA'xA''xF''xF''E''D''C''xG''B''bB''bB''xG''xG''xF''E", $normalized5);
    }

    public function testNormalizationToSingleOctave() {
        $normalized1 = $this->incipit1->getNotesNormalizedToSingleOctave();//xFC accidentals
        $this->assertEquals("DDDDDxFAxFGGGExFGExF", $normalized1);

        $normalized2 = $this->incipit2->getNotesNormalizedToSingleOctave();//xFC accidentals
        $this->assertEquals("DDDDDxFxFxFGxFxFEDExExF", $normalized2);

        $normalized3 = $this->incipit3->getNotesNormalizedToSingleOctave();//bBE accidentals
        $this->assertEquals("GDDbECbBCDCbBCDEDbECbBCAbBAGG", $normalized3);

        $normalized4 = $this->incipit4->getNotesNormalizedToSingleOctave();//xFC accidentals
        $this->assertEquals("AADDDxFEEEFDBExCDxCBxCBA", $normalized4);

        $normalized5 = $this->incipit5->getNotesNormalizedToSingleOctave(); //xFCG accidentals
        $this->assertEquals("xCDDxCBxAxAxFxFEDCxGBbBbBxGxGxFE", $normalized5);
    }

    public function testSanitizedString() {
        //critical characters
        $sanitized = Incipit::getSanitizedIncipitString("\"\\_");
        $this->assertEquals("", $sanitized);
        //special chars
        $sanitized = Incipit::getSanitizedIncipitString("äö*<>#");
        $this->assertEquals("", $sanitized);
        //whitespace
        $sanitized = Incipit::getSanitizedIncipitString(" \t\n\r");
        $this->assertEquals("", $sanitized);

        //pae
        $legalPaeChars = "ABCDEFGHbfgnoqrtx{}().%\$@/,';:-=+?^";
        $sanitized = Incipit::getSanitizedIncipitString($legalPaeChars);
        $this->assertEquals($legalPaeChars, $sanitized);

        //incipit samples
        $sanitized = Incipit::getSanitizedIncipitString($this->incipit1->getCompleteIncipit());
        $this->assertEquals($this->incipit1->getCompleteIncipit(), $sanitized);

        $sanitized = Incipit::getSanitizedIncipitString($this->incipit2->getCompleteIncipit());
        $this->assertEquals($this->incipit2->getCompleteIncipit(), $sanitized);

        $sanitized = Incipit::getSanitizedIncipitString($this->incipit3->getCompleteIncipit());
        $this->assertEquals($this->incipit3->getCompleteIncipit(), $sanitized);

        $sanitized = Incipit::getSanitizedIncipitString($this->incipit4->getCompleteIncipit());
        $this->assertEquals($this->incipit4->getCompleteIncipit(), $sanitized);

        //further examples from iaml website
        $legalPaeChars = '%C-1$bBEA@c\'2A-//$xFC8B-4-2-/@3/21C2-//';
        $sanitized = Incipit::getSanitizedIncipitString($legalPaeChars);
        $this->assertEquals($legalPaeChars, $sanitized);
    }

    public function testJSON() {
        $json = $this->incipit4->getJSONArray();

        $this->assertEquals($this->incipit4->getNotes(), $json["notes"]);
        $this->assertEquals($this->incipit4->getClef(), $json["clef"]);
        $this->assertEquals($this->incipit4->getTime(), $json["time"]);
        $this->assertEquals($this->incipit4->getAccidentals(), $json["accidentals"]);
        $this->assertEquals($this->incipit4->getCompleteIncipit(), $json["completeIncipit"]);
        $this->assertEquals($this->incipit4->getNotesNormalizedToSingleOctave(), $json["normalizedToSingleOctave"]);
        $this->assertEquals($this->incipit4->getNotesNormalizedToPitch(), $json["normalizedToPitch"]);


        $newIncpit = Incipit::incipitFromJSONArray($json);

        $this->assertEquals($this->incipit4->getNotes(), $newIncpit->getNotes());
        $this->assertEquals($this->incipit4->getClef(), $newIncpit->getClef());
        $this->assertEquals($this->incipit4->getTime(), $newIncpit->getTime());
        $this->assertEquals($this->incipit4->getAccidentals(), $newIncpit->getAccidentals());

    }
}
