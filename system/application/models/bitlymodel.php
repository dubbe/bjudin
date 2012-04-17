<?php
/**
 * Bitlymodellen använder vi för att förkorta urlen innan vi skickar dem till twitter. 
 */
class Bitlymodel extends My_Model {
   
    private $login = "tdhlbrg" ;
    private $apiKey = "R_44f134a71f61be7f3e63a75532019504" ;
      
    function Bitlymodel()
    {
        // Call the Model constructor
        parent::My_Model();
       
    }
    
    function shorten($longLink) {
        
       
        $longLink = urlencode($longLink);
         
        $apiCall = "http://api.bit.ly/v3/shorten?login=$this->login&apiKey=$this->apiKey&longUrl=$longLink&format=xml";        
        
        $xmlLink = $this->loadSimpleXml($apiCall, $longLink, "bitly/", 60) ;
        
        if (@$xmlLink->data) {
            $status_code = $xmlLink->xpath("//status_code") ;
            $url = $xmlLink->xpath("//url") ;

            if ($status_code[0] == "200") {
                return $url[0] ;
            }
        }
        
    }

    
}
?>
