<?PHP

class Geonamemodel extends My_Model {

    private $geonameId = '' ;
    private $city = '' ;
    private $cityAnsi = '' ;
    private $region = '' ;

    
    
    function geonamemodel()
    {
        // Call the Model constructor
        parent::My_Model();
        
        
        
    }
    function getCity() {
        return $this->city ;
    }
    function getCityAnsi() {
        return $this->cityAnsi ;
    }
    function getRegion() {
        return $this->region ;
    }
    
    function setGeonameId($newGeonameId) {
        
        if(!empty($newGeonameId)) {
            $this->geonameId = $newGeonameId ;



            $query = $this->db->query("SELECT name, ansiname, admin1_code FROM geonames WHERE geonameid = '$this->geonameId' LIMIT 1");
            $row = $query->row() ;

            $this->city = $row->name ;
            $this->region = $this->getRegionName($row->admin1_code) ;
            $this->cityAnsi = $row->ansiname ;
        }

        
    }
    
    function getRegionName($regionId) {
        
        $query = $this->db->query("SELECT name FROM geonamesRegions WHERE id = '$regionId' LIMIT 1");
        
        $row = $query->row() ;
        
        return $row->name ;
        
    }

    function updateDb() {
        
        $letterArray = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "å", "ä", "ö") ;
        
        foreach ($letterArray as $letter) {

            $apiCall = "http://ws.geonames.org/search?country=SE&featureClass=P&maxRows=1&name_startsWith=$letter&style=short";        

            $xmlLink = $this->loadSimpleXml($apiCall, $letter."_initCity", "geonames/", 720) ;
            
            echo "Hämtar hur många städer det finns på bokstaven $letter <br />" ;
            
            $status = $xmlLink->xpath("status") ;

            if(!empty($status)) {
                $value = $status[0]->xpath("@value") ;
                if ($value[0] == 22) {
                    echo "Tyvärr är geonames för närvarande överbelastat<br />" ;
                } else {
                    echo "Något gick fel vid hämtandet av data<br />" ;
                }
                
                $filename = "files/cache/geonames/".$letter."_initCity.xml" ;

                if (unlink($filename)) {
                    echo "Filen är borttagen.<br />" ;
                }

            } else {

                $nmb = $xmlLink->xpath("//totalResultsCount") ;

                $hits = 100 ;
                $i = 0 ;
                while ($i <= $nmb[0]) {

                    $apiCall = "http://ws.geonames.org/search?country=SE&maxRows=$hits&startRow=$i&featureClass=P&name_startsWith=$letter&style=full";    

                    $xmlLink = $this->loadSimpleXml($apiCall,  $letter."_city".$i, "geonames/", 720) ;
                    
                    $status = $xmlLink->xpath("status") ;
                    
                    $x = $i+$hits ;
                    
                    echo "Hämtar städer som börjar med $letter, från $i till $x.<br />" ;
                    
                    if(!empty($status)) {
                        $value = $status[0]->xpath("@value") ;
                        if ($value[0] == 22) {
                            echo "Tyvärr är geonames för närvarande överbelastat<br />" ;
                        } else {
                            echo "Något gick fel vid hämtandet av data<br />" ;
                        }
                        
                        $filename = "files/cache/geonames/".$letter."_city".$i.".xml" ;
                        
                        if (unlink($filename)) {
                            echo "Filen är borttagen.<br />" ;
                        }

                    } else {
                    
                        foreach ($xmlLink->xpath("//geoname") as $geoname) {
                            $geonameId = $geoname->xpath("geonameId") ;
                            $name = $geoname->xpath("name") ;
                            $lat = $geoname->xpath("lat");
                            $long = $geoname->xpath("lng") ;
                            $featureClass = $geoname->xpath("fcode") ;
                            $adminCode = $geoname->xpath("adminCode1") ;

                            // Kikar först om det finns baserat på geonameId

                            $query = $this->db->query("SELECT * FROM geonames WHERE geonameid = '$geonameId[0]'");
                            
                            if($query->num_rows() == 0){
                                // Lägger till den nybildade staden!
                                $query = "INSERT INTO geonames 
                                        (geonameId, name, latitude, longitude, feature_class, admin1_code, country_code, feature_class) 
                                        VALUES 
                                        (".$this->db->escape($geonameId[0]).",
                                            ".$this->db->escape($name[0]).",
                                            ".$this->db->escape($lat[0]).", 
                                            ".$this->db->escape($long[0]).", 
                                            ".$this->db->escape($featureClass[0]).", 
                                            ".$this->db->escape($adminCode[0]).", 
                                            'SE', 
                                            'P'";
                                
                                $this->db->query($query) ;

                            } else {
                                // Uppdaterar städer där namnet är lika med namnet i databasen, så att inte Landskrona t ex ska döpas om...
                                $query = "UPDATE geonames SET 
                                        geonameId = ".$this->db->escape($geonameId[0]).",
                                        name = ".$this->db->escape($name[0]).",
                                        latitude = ".$this->db->escape($$lat[0]).",
                                        longitude = ".$this->db->escape($long[0]).",
                                        feature_class = ".$this->db->escape($featureClass[0]).",
                                        admin1_code = ".$this->db->escape($adminCode[0]).",
                                        country_code = 'SE',
                                        feature_class = 'P'
                                        WHERE name = ".$this->db->escape($name[0])."";
                                
                                $this->db->query($query) ;

                            }

                        }
                    }
                    
                    $i = $i + $hits ;


                } 
            }
                  
        }
        
    }

}
?>