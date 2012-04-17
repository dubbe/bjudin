<?php
/**
 * Funktionen som laddas som index, Har finns egentligen bara lite kod som plockar fram de nästkommande eventen och laddar in vyerna
 */
class Start extends Controller {
        function Start() {
            parent::Controller();
        }
	function index()
	{

            $today = date('Y-m-d') ;
            
            $query = $this->db->query("SELECT * FROM events WHERE private = 0 AND date >= '$today' ORDER BY date, time LIMIT 5") ;
            // 
            $data["query"] = $query->result_array() ;
            $data["place"] = array() ;
            
            $this->load->model("Geonamemodel") ;
            
            foreach ($query->result_array() as $row)
            {   
                $this->Geonamemodel->setGeonameId($row['place']) ;
                array_push($data['place'], $this->Geonamemodel->getCity().", ".$this->Geonamemodel->getRegion()) ;
            
            }
            
            
            $this->load->view("header");
            $this->load->view("firstPage", $data) ;
            $this->load->view("footer") ;
           
            


	}
}
?>
