<?php
class Yrmodel extends My_Model {

    private $place ;
    private $xmlWeather ;
    
    function yrmodel()
    {
        // Call the Model constructor
        parent::My_Model();

    }
    
    function setPlace($place, $region) {
        $this->place = $place ;
        
        $this->xmlWeather = $this->loadSimpleXml("http://www.yr.no/place/Sweden/".$region."/".$place."/forecast.xml", $place, "yr/cities/", 15) ;
    } 
    
    function getForecastDay($day, $time) {
        
        // Jag kollar så att //link finns, då borde det vara ett komplett yr-dokument... 
        if(@$this->xmlWeather->xpath("//link")) {
            $link = $this->xmlWeather->xpath("//link") ;
            $link = $link[0]->attributes() ;

            $yr['link'] = (string)$link[1] ;

            foreach($this->xmlWeather->xpath("//time") as $timeNode) {
                
                $arr = $timeNode->attributes() ;
                
                $from = split("T", $arr['from']) ; 
                $to = split("T", $arr['to']) ;

                if ($day == $from[0] && $time >= $from[1] && $time <= $to[1])  {

                    $symbol = $timeNode->xpath("symbol") ;
                    $symbol = $symbol[0]->attributes() ;

                    // Vilka symboler
                    $yr['symbolNr'] = (string)$symbol[0] ;
                    $yr['symbolName'] = (string)$this->transformSymbolNr($symbol[0]) ;
                    $yr['symbolVar'] = (string)$symbol[2] ;

                    // Skriver in tiden det gäller
                    $yr['from'] = (string)$from[1] ;
                    $yr['to'] = (string)$to[1] ;

                    // Och skriver in temperaturen
                    $temperature = $timeNode->xpath("temperature") ;
                    $temperature = $temperature[0]->attributes() ;

                    $yr['temperature'] = (string)$temperature[1] ;

                    // Plocka fram lite vind-attribut kanske
                    $wind = $timeNode->xpath("windDirection") ;
                    $wind = $wind[0]->attributes() ;
                    $yr['windCode'] = (string)$wind[1] ;

                    $windSpeed = $timeNode->xpath("windSpeed") ;
                    $windSpeed = $windSpeed[0]->attributes() ;
                    $yr['windSpeed'] = (string)$windSpeed[0] ;

                    $precipitation = $timeNode->xpath("precipitation") ;
                    $precipitation = $precipitation[0]->attributes() ;
                    $yr['precip'] = (string)$precipitation[0] ;
                    
                    // Om den har nått hit innebär det att det här är den tid vi är ute efter, då vill vi inte att den går vidare med nästa elseif, så vi bryter här
                    break ;

                } elseif ($day == $from[0]) {                                               // Om den inte hittar någon tid som stämmer, men en dag så plockar den den vädret från den dagen

                    $symbol = $timeNode->xpath("symbol") ;
                    $symbol = $symbol[0]->attributes() ;

                    // Vilka symboler
                    $yr['symbolNr'] = (string)$symbol[0] ;
                    $yr['symbolName'] = (string)$this->transformSymbolNr($symbol[0]) ;
                    $yr['symbolVar'] = (string)$symbol[2] ;

                    // Skriver in tiden det gäller
                    $yr['from'] = (string)$from[1] ;
                    $yr['to'] = (string)$to[1] ;

                    // Och skriver in temperaturen
                    $temperature = $timeNode->xpath("temperature") ;
                    $temperature = $temperature[0]->attributes() ;

                    $yr['temperature'] = (string)$temperature[1] ;

                    // Plocka fram lite vind-attribut kanske
                    $wind = $timeNode->xpath("windDirection") ;
                    $wind = $wind[0]->attributes() ;
                    $yr['windCode'] = (string)$wind[1] ;

                    $windSpeed = $timeNode->xpath("windSpeed") ;
                    $windSpeed = $windSpeed[0]->attributes() ;
                    $yr['windSpeed'] = (string)$windSpeed[0] ;

                    $precipitation = $timeNode->xpath("precipitation") ;
                    $precipitation = $precipitation[0]->attributes() ;
                    $yr['precip'] = (string)$precipitation[0] ;

                }

            }
            
            if (isset($yr)) {
                $yr['set'] = true ;
            } else {
                $yr['set'] = false ;
            }
            return $yr ;
        }
         else {
            $yr['set'] = false ;
            return $yr ;
        }
 
        
    }
    
    function transformSymbolNr($nr) {
     
        // Översätter namnen till svenska
        
       $symbolName = array(
           "",
           "Sol/Klart väder", 
           "Lätt molnligt",
           "Delvis molnligt", 
           "Molnligt",
           "Regnskurar",
           "Regnskurar med åska",
           "Snöblandat regn",
           "Snöbyar",
           "Regn",
           "Kraftigt regn",
           "Regn med åska",
           "Snöblandat regn",
           "Snö",
           "Snö och åska",
           "Dimma") ;
       
       return $symbolName[(int)$nr] ;
        
    }
}
?>
