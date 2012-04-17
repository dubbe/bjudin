<?php

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

$regionList = array("Sweden", "Alvsborg", "Blekinge", "Gävleborg", "Goteborgs Och Bojus", 
    "Gotland", "Halland", "Jämtland", "Jönköping", "Kalmar", "Dalarna", "Kristianstad", "Kronoberg",
        "Malmohus", "Norrbotten", "Örebro", "Östergötland", "Skaraborg", "Södermanland", "NA", "NA", "Uppsala", "Värmland", "Västerbotten",
    "Västernorrland", "Västmanland", "Stockholm", "Skåne", "Västra Götaland") ;

$cities['city'] = array() ;

foreach ($region as $i => $item) {
    $city = array(
        'name' => $name[$i],
        'region' => $regionList[(int)$item],
        'geonameId' => $geonamesId[$i],
        'type' => "Stad"
    ) ;
    
    array_push($cities['city'], $city) ;
    
}

echo json_encode($cities) ;

?>



