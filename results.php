<?php

    namespace ADWLM\IncipitSearch;

    require_once "SearchQuery.php";
    require_once "IncipitEntry.php";
    require_once "Incipit.php";

    /**
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
     */

    $incipit = $_GET["incipit"];

    $searchQuery = new SearchQuery();
    $searchQuery->setQuery($incipit);
    $incipitEntries = $searchQuery->performSearchQuery();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <script src="http://www.verovio.org/javascript/latest/verovio-toolkit.js"></script>
    <title>Search results</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
</head>
<body>
<!-- The div where we are going to insert the SVG -->

<script type="text/javascript">

    $( document ).ready(function() {
        //var vrvToolkit = new verovio.toolkit();

        $('.result').each(function(i, domResult) {
            var result = $(domResult);
            var incipit = result.find('.incipit').text();

            var data = "@clef:" + result.find('.incipitClef').text() + "\n";
            data += "@keysig:" + result.find('.incipitAccidentals').text() + "\n";
            data += "@timesig:" + result.find('.incipitTime').text() + "\n";
            data += "@data:" + result.find('.incipitNotes').text();

            options = JSON.stringify({
                inputFormat: 'pae',
                pageHeight: 300,
                pageWidth: 400,
                ignoreLayout: 1,
                border: 20,
                scale: 100
            });

            var vrvToolkit = new verovio.toolkit();
            var notesSVG = vrvToolkit.renderData(data, options);
            var svgContainerDiv = result.find('.incipitSVG');
            svgContainerDiv.html(notesSVG);

        });//end for each result

    });//end doc ready

</script>


<?php
foreach ($incipitEntries as $incipitEntry) {
    $completeIncipit = $incipitEntry->getIncipit()->getCompleteIncipit();
    $incipitNotes = $incipitEntry->getIncipit()->getNotes();
    $incipitClef = $incipitEntry->getIncipit()->getKey();
    $incipitAccidentals = $incipitEntry->getIncipit()->getAccidentals();
    $incipitTime = $incipitEntry->getIncipit()->getTime();

    $title = $incipitEntry->getTitle();
    $incipitNormalized = $incipitEntry->getIncipit()->getNotesNormalized();

    echo <<< EOS
    <div class="result">
        $title $incipitNormalized
        <span class="incipitNotes hidden">$incipitNotes</span>
        <span class="incipitClef hidden">$incipitClef</span>
        <span class="incipitAccidentals hidden">$incipitAccidentals</span>
        <span class="incipitTime hidden">$incipitTime</span>

        <div class="incipitSVG"></div>
    </div><!--end result-->
    <br/>
    
EOS;
}

?>

</body>
</html>





