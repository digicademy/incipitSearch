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

    public function setUp() {
        $this->incipit1 = new Incipit("''4D8DD4D-/DFAF/8GG4--G/EFGE/F--", "G-2", "xFC", "c");
        $this->incipit2 = new Incipit("''4DDD/D--/,D,,FF/FGF/gF4EDE/2xE4F/", "C-1", "xFC", "3/4");
        $this->incipit3 = new Incipit("=14/{''6GD}4D8E/{8.C'3B''C8D}C/{'6.B''3C8D}-nE/D{6EC}'8B6''C'A/B{AG}4G", "C-1", "bBE", "2/4");
        $this->incipit4 = new Incipit("=1/2-4(-)'8AA/4''DD-D/gF4EE-E/nFD'B''E/({8CDC})({'B''C'B})4A8-", "C-1", "xFC", "c");
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
        $normalized1 = $this->incipit1->getNotesNormalizedToPitch();
        $this->assertEquals("''DDDDDFAFGGGEFGEF", $normalized1);

        $normalized2 = $this->incipit2->getNotesNormalizedToPitch();
        $this->assertEquals("''DDDD,D,,FFFGFFEDExEF", $normalized2);

        $normalized3 = $this->incipit3->getNotesNormalizedToPitch();
        $this->assertEquals("''GDDEC'B''CDC'B''CDEDEC'B''C'ABAGG", $normalized3);

        $normalized4 = $this->incipit4->getNotesNormalizedToPitch();
        $this->assertEquals("'AA''DDDFEEEFD'B''ECDC'B''C'BA", $normalized4);
    }

    public function testNormalizationToSingleOctave() {
        $normalized1 = $this->incipit1->getNotesNormalized();
        $this->assertEquals("DDDDDFAFGGGEFGEF", $normalized1);

        $normalized2 = $this->incipit2->getNotesNormalized();
        $this->assertEquals("DDDDDFFFGFFEDExEF", $normalized2);

        $normalized3 = $this->incipit3->getNotesNormalized();
        $this->assertEquals("GDDECBCDCBCDEDECBCABAGG", $normalized3);

        $normalized4 = $this->incipit4->getNotesNormalized();
        $this->assertEquals("AADDDFEEEFDBECDCBCBA", $normalized4);
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
        $this->assertEquals($this->incipit4->getNotesNormalized(), $json["normalizedIncipit"]);


        $newIncpit = Incipit::incipitFromJSONArray($json);

        $this->assertEquals($this->incipit4->getNotes(), $newIncpit->getNotes());
        $this->assertEquals($this->incipit4->getClef(), $newIncpit->getClef());
        $this->assertEquals($this->incipit4->getTime(), $newIncpit->getTime());
        $this->assertEquals($this->incipit4->getAccidentals(), $newIncpit->getAccidentals());

    }
}
