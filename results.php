<?php

    namespace ADWLM\IncipitSearch;

    require_once "SearchQuery.php";
    require_once "IncipitEntry.php";

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

<div id="output"></div>
<script type="text/javascript">
    /* The Plain and Easy code to be rendered */
    var data = "@clef:G-2\n\
    @keysig:xFCGD\n\
    @timesig:3/8\n\
    @data:'6B/{8B+(6B''E'B})({AFD})/{6.E3G},8B-/({6'EGF})({FAG})({GEB})/";
    /* Create the Vevorio toolkit instance */
    var vrvToolkit = new verovio.toolkit();
    /* Render the data and insert it as content of the #output div */
    document.getElementById("output").innerHTML = vrvToolkit.renderData(
        data,
        JSON.stringify({ inputFormat: 'pae' })
    );


    $( document ).ready(function() {
        //var vrvToolkit = new verovio.toolkit();

        $('.result').each(function(i, domElement) {
            var element = $(domElement);
            var incipit = element.find('.incipit').innerHTML;

            var notesDiv = element.find('.incipitNotes');
            //notesDiv.html("Hello");

            notesDiv.html  = vrvToolkit.renderData(
                incipit,
                JSON.stringify({ inputFormat: 'pae' })
            );

        });

    });

</script>


<?php
foreach ($incipitEntries as $incipitEntry) {
    echo "<div class='result'>" . $incipitEntry->getTitle() . " : " . $incipitEntry->getIncipit()->getNotesNormalized() . "\n";
    echo '<div class="incipit">' . $incipitEntry->getIncipit()->getCompleteIncipit() . "</div>\n";
    echo '<div class="incipitNotes"></div>\n';
    echo '</div><!--end entry-->';
}

?>

</body>
</html>





