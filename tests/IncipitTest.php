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
        $this->incipit2 = new Incipit("''4DDD/D--/DFF/FGF/gF4EDE/2xE4F/", "C-1", "xFC", "3/4");
        $this->incipit3 = new Incipit("=14/{''6GD}4D8E/{8.C'3B''C8D}C/{'6.B''3C8D}-E/D{6EC}'8B6''C'A/B{AG}4G", "C-1", "bBE", "2/4");
        $this->incipit4 = new Incipit("=1/2-4(-)'8AA/4''DD-D/gF4EE-E/FD'B''E/({8CDC})({'B''C'B})4A8-", "C-1", "xFC", "c");
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


    public function testNormalization() {
        $normalized1 = $this->incipit1->getNotesNormalized();
        $this->assertEquals("DDDDDFAFGGGEFGEF", $normalized1);

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
