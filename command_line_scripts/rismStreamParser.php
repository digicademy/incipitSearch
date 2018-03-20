<?php

/**
 * Reads RISM OPAC dump (https://opac.rism.info/index.php?id=8&L=0)
 * into an XML stream and extracts all entries with incipits. Put
 * OPAC dump into catalogues directory and execute this script from
 * the command line. Dumps are excluded from version control.
 *
 * Copyright notice
 *
 * (c) 2018
 * Torsten Schrade <Torsten.Schrade@adwmainz.de>
 *
 * Digital Academy www.digitale-akademie.de
 * Academy of Sciences and Literatur | Mainz www.adwmainz.de
 *
 * Licensed under The MIT License (MIT)
 *
 * @package ADWLM\IncipitSearch
 */

use Prewk\XmlStringStreamer;
use Prewk\XmlStringStreamer\Parser;
use Prewk\XmlStringStreamer\Stream\File;

require __DIR__ . '/../vendor/autoload.php';

$file = "../catalogues/rism.xml";

// Save the total file size
$totalSize = filesize($file);

// Construct the file stream
$stream = new File($file, 16384, function($chunk, $readBytes) use ($totalSize) {
    echo "Progress: $readBytes / $totalSize\n";
});

// Construct the parser
$options = array(
    'uniqueNode' => 'record'
);
$parser = new Parser\UniqueNode($options);

// Construct the streamer
$streamer = new XmlStringStreamer($parser, $stream);

// reset outfile
$out = '../catalogues/rism.incipits.xml';
if (file_exists($out)) unlink($out);

// Start file
file_put_contents($out, '<records>', FILE_APPEND);

// Start stream parsing
$i = 1;
while ($node = $streamer->getNode()) {

    $simpleXmlNode = simplexml_load_string($node);

    if (count($simpleXmlNode->xpath('//datafield[@tag = \'031\']/subfield[@code = \'p\']'))) {

        $catalogItemID = $simpleXmlNode->xpath('//controlfield[@tag = \'001\']/text()')[0]->__toString();
        $title = $simpleXmlNode->xpath('//datafield[@tag = \'240\']/subfield[@code = \'a\']/text()');
        $subtitle = $simpleXmlNode->xpath('//datafield[@tag = \'240\']/subfield[@code = \'k\']/text()');
        $composer = $simpleXmlNode->xpath('//datafield[@tag = \'100\']/subfield[@code = \'a\']/text()');
        $year = $simpleXmlNode->xpath('//datafield[@tag = \'260\']/subfield[@code = \'c\']/text()');

        (count($title) > 0) ? $title = $title[0]->__toString() : $title = '';
        (count($subtitle) > 0) ? $subtitle = $subtitle[0]->__toString() : $subtitle = '';
        (count($composer) > 0) ? $composer = $composer[0]->__toString() : $composer = '';
        (count($year) > 0) ? $year = $year[0]->__toString() : $year = '';

        $incipits = $simpleXmlNode->xpath('//datafield[@tag = \'031\']');

        $incipitData = '';
        foreach ($incipits as $incipit) {
            $p = $incipit->xpath('subfield[@code = \'p\']');
            if (count($p) > 0) {
                $g = $incipit->xpath('subfield[@code = \'g\']');
                $n = $incipit->xpath('subfield[@code = \'n\']');
                $o = $incipit->xpath('subfield[@code = \'o\']');

                (count($g) > 0) ? $g = '<subfield code="g">'. $g[0]->__toString() .'</subfield>' : $g = '';
                (count($n) > 0) ? $n = '<subfield code="n">'. $n[0]->__toString() .'</subfield>' : $n = '';
                (count($o) > 0) ? $o = '<subfield code="o">'. $o[0]->__toString() .'</subfield>' : $o = '';
                (count($o) > 0) ? $p = '<subfield code="p">'. $p[0]->__toString() .'</subfield>' : $p = '';

                $incipitData .= '
        <datafield tag="031" ind1=" " ind2=" ">
            '. $g .'
            '. $n .'
            '. $o .'
            '. $p .'
        </datafield>';
            }
        }

$data = '
    <record>
        <controlfield tag="001">'. $catalogItemID .'</controlfield>
        <datafield tag="240" ind1="1" ind2="0">
            <subfield code="a">'. $title .'</subfield>
            <subfield code="k">'. $subtitle .'</subfield>
        </datafield>
        <datafield tag="100" ind1="1" ind2=" ">'. $composer .'</datafield>
        <datafield tag="260" ind1=" " ind2=" ">'. $year .'</datafield>'. $incipitData .'
    </record>';

        // append entry to file
        file_put_contents($out, $data, FILE_APPEND);
    }

    $i++;
}

// Close file
file_put_contents($out, '
</records>
', FILE_APPEND);
