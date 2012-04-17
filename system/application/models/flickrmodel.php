<?php

class Flickrmodel extends My_Model {
   
    private $apiKey = "7091a401b83cc11fc3237684d6217d9c" ;
    private $secret = "22cad3ad2dc073ae" ;
      
    function Flickrmodel()
    {
        // Call the Model constructor
        parent::My_Model();
       
    }
    
    function getPhotoCluster($tag) {
        
        $apiCall = "http://api.flickr.com/services/rest/?method=flickr.tags.getClusterPhotos&api_key=".$this->apiKey."&tag=".$tag ;
        
        // Laddar simpleXml som vi sen kan jobba med
        
        $simpleXml = $this->loadSimpleXml($apiCall, $tag, "flickr/", 720) ;
        
        $xmlArray['flickr'] = array() ;
       
        /* Vi checkar så att det finns några foton i xml-filen vi får. */
       
        if(@!empty($simpleXml->photos)) {
            foreach($simpleXml->xpath("//photo") as $photo) {

                $tmpArray = array() ;
                
                $farm = $photo->xpath("@farm") ; 
                $secret = $photo->xpath("@secret") ;
                $server_id = $photo->xpath("@server")  ;
                
                //Sparar ner title och username i arrayen vi skickar vidare
                
                $title = $photo->xpath("@title") ;
                $tmpArray['title'] = $this->_stripText($title[0]) ;
                $username = $photo->xpath("@username") ;
                $tmpArray['username'] = $this->_stripText($username[0]) ;

                $id = $photo->xpath("@id");

                // Sparar ner länkarna också i arrayen
                
                $tmpArray['link'] = "http://farm$farm[0].static.flickr.com/$server_id[0]/$id[0]_$secret[0].jpg" ;
                $tmpArray['linkThumb'] = "http://farm$farm[0].static.flickr.com/$server_id[0]/$id[0]_$secret[0]_t.jpg" ;

                array_push($xmlArray['flickr'], $tmpArray) ;

            }
        }
        
        return $xmlArray ;
    }
    
    function _stripText($string) {
        
        // Kör igenom lite php säkerhet innan vi skriver till databasen
        
        $string = strip_tags($string) ;
        $string = htmlspecialchars($string) ;
        $string = nl2br($string) ;

        return $string ;
    }
    
}
?>
