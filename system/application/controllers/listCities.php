<?php
/**
 * listCities används i createInvitation för att få städerna i ajax-autocomplete.
 * update kallar dessutom updateDb som uppdaterar databasen från geonames
 */
class listCities extends Controller {
    
    function index() {

        $query = $this->db->query("SELECT * FROM geonames WHERE feature_class = 'P' AND name like '".$_GET['name']."%' ORDER BY name LIMIT ".$_GET['maxResult']);
        
        $data['name'] = array() ;
        $data['region'] = array() ;
        $data['geonamesId'] = array() ;
        
        foreach ($query->result_array() as $row)
        {
            array_push($data['name'], $row['name']) ;
            array_push($data['region'], $row['admin1_code']) ;
            array_push($data['geonamesId'], $row['geonameid']) ;

        }
        $this->load->view("listCities", $data);
        
    }
    
    function update() {
        $this->load->model("Geonamemodel") ;
        
        $this->Geonamemodel->updateDb() ;
    }
}
?>
