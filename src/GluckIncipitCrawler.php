
<?php
require '../vendor/autoload.php';

$client = new EasyRdf_Http_Client('http://gluck-gesamtausgabe.local/rdf/collection/works/1-38-00-0');
$response = $client->request();

$graph = new EasyRdf_Graph('http://gluck-gesamtausgabe.local/rdf/collection/works/1-38-00-0');
$graph->parse($response->getBody(), 'rdfxml');

foreach ($graph->allOfType('skos:Concept') as $concept) {
    $incipit = $concept->get('<http://bsb-muenchen.de/ont/bsbMusicOntology#incipitScore>');
    $identifier = $concept->get('dc:identifier');
    if ($incipit) {
        echo json_encode(
            array(
                'identifier' => $identifier->getValue(),
                'incipit' => $incipit->getValue()
            ));
    }
}


?>
