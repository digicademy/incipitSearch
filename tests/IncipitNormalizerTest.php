<?php

/**
 * Created by PhpStorm.
 * User: gaby
 * Date: 31/05/16
 * Time: 09:40
 */

require dirname(__DIR__) . '/src/Incipit.php';

use \ADWLM\IncipitSearch\IncipitNormalizer;

class IncipitNormalizerTest extends PHPUnit_Framework_TestCase
{

    public function setUp() {
 }



    public function testNormalizationToPitch() {
        $normalized1 = IncipitNormalizer::normalizeToPitch("''4D8DD4D-/DFAF/8GG4--G/EFGE/F--",["F", "C"], null);
        $this->assertEquals("''D''D''D''D''D''xF''A''xF''G''G''G''E''xF''G''E''xF", $normalized1);


    }

    public function testNormalizationToSingleOctave() {
        $normalized1 = IncipitNormalizer::normalizeToSingleOctave("''4D8DD4D-/DFAF/8GG4--G/EFGE/F--",["F", "C"], null);
        $this->assertEquals("DDDDDxFAxFGGGExFGExF", $normalized1);


    }


}
