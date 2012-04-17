<?php
/**
 * Extendar modelen med en egen som har funktioner för att ladda in XML, göra om till simpleXml och en enkel cache-funktion.
 */
class MY_Model extends Model {

    private $baseFolder = "./files/cache/" ;
    private $folder ;
    private $xml ;
    private $name ;

    function my_model()
    {
        // Call the Model constructor 
        parent::Model();
      


    }


    function loadXml($link, $name, $folder, $cacheTime) {

        $this->name = urlencode($name) ;
        $this->folder = $folder ;

        $cacheTime = $cacheTime * 60 ; // Konverterar minuter till sekunder

        $cacheFile = $this->baseFolder.$this->folder.$this->name.".xml" ;

        $filemtime = @filemtime($cacheFile);  // returns FALSE if file does not exist
        if (!$filemtime || (time() - $filemtime >= $cacheTime)){

                set_time_limit(60);
                $this->xml = new DOMDocument() ;
                if (@$this->xml->load($link) === false) { ;
                    if ($filemtime) {
                        return $this->loadCachedXml() ;
                    } else {
                        return false ;
                    }
                
                } else {
                    $this->xml->load($link) ;
                    $this->saveXml() ; 
                    return $this->xml ; 
                }
                 
            
        } else {
            return $this->loadCachedXml() ;
        }

        
    }

    function loadSimpleXml($link, $name, $folder, $cacheTime) {
        $this->loadXml($link, $name, $folder, $cacheTime) ;
        if(@simplexml_import_dom($this->xml)) {
            return simplexml_import_dom($this->xml);
        } else {
            return false ;
        }
    }

    function loadCachedXml() {
        $this->xml = new DOMDocument() ;
        $this->xml->load($this->baseFolder.$this->folder.$this->name.".xml");
    }

    function saveXml() {
        $data = 'Some file data';

        if ( ! $this->xml->save($this->baseFolder.$this->folder.$this->name.".xml"))
        {
             echo 'Unable to write the file';
        }

    }
    
    

}

?>