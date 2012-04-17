<?php
/*
 * Invitation kontrollern, här styr vi allting som har med eventen att göra.
 */

class Invitation extends Controller {
    
    function Invitation() {
        parent::Controller();
        $this->load->view("header");
    }
    function index() {

        $this->load->view("footer");
    }
    
    function create() {
        /*
         * Vi laddar vyn som visar formuläret för att skapa ett event.
         */
        $this->load->view("createInvitation"); 
        $this->load->view("footer");
    
    }
    
    function save() {
     
        /* 
         * Formuläret skickar post-datan hit så jag får ta hand om den
         * Vi börjar med att ta bort lite tecken som vi inte vill ha med. (Se _stripText-funktionen)
         */
        
        $data['title'] = $this->_stripText($_POST['title']) ;
        $data['info'] = $this->_stripText($_POST['info']) ;
        $data['place'] = $this->_stripText($_POST['place']) ;
        $data['date'] = $this->_stripText($_POST['date']) ;
        $data['time'] = $this->_stripText($_POST['time']) ;
        $data['twitter'] = $this->_stripText($_POST['twitter']) ;
        $data['twitterHash'] = $this->_stripText(isset($_POST['twitterHash'])?$_POST['twitterHash']:"") ;
        $data['flickr'] = $this->_stripText($_POST['flickr']) ;
        $data['flickrSearch'] = $this->_stripText(isset($_POST['flickrSearch'])?$_POST['flickrSearch']:"") ;
        $data['private'] = $this->_stripText($_POST['private']) ;
        $data['password'] = $this->_stripText(isset($_POST['password'])?$_POST['password']:"") ;  
        $data['participants'] = $this->_stripText($_POST['participants']) ;

        if($this->session->userdata('username') === FALSE) {
            $data['user'] = $this->_stripText(isset($_POST['email'])?$_POST['email']:"NULL") ;
        } else {
            $data['user'] = $this->session->userdata('username') ;
        }
        
        
        /* 
         * Så kör vi lite tester ifall javascript skulle vara avstängt eller om nåt gått fel i client-side kollen.
         * Blir save false, sparas inte formuläret utan laddas bara om.
         */
        
        $data['error'] = array() ;
        $save = true ;
        
        $data['geonameId'] = $this->_getGeonameId($_POST['place']) ;
        
        if(!$this->_reqText($_POST['title'])) {
            array_push($data['error'], "Du måste ange en titel!") ;
            $save = false ;
        }
        if(!$this->_reqText($_POST['info'])) {
            array_push($data['error'], "Du måste ange en beskrivning!") ;
            $save = false ;
        }
        if(!$this->_reqText($_POST['place'])) {
            array_push($data['error'], "Du måste ange en plats!") ;
            $save = false ;
        } elseif ($data['geonameId'] === FALSE) {
            array_push($data['error'], $data['place']." finns inte. Var god ange en korrekt ort.") ;
            $save = false ;
        }
        
        if($this->session->userdata('username') === FALSE && !$this->_validEmail($_POST['email'])) {
            array_push($data['error'], "Du måste din epostadress!") ;
        }
        
        
        if(!empty($data['twitterHash'])) {
            $data['twitterHash'] = $this->_removeFirst($data['twitterHash'], "#") ;
        }
        
        if($save) {  
          
            /* 
            * Och om allt går igenom så sparar vi i databasen
            */   
           
            $sql = "INSERT INTO events (title, info, place, date, time, twitter, twitterHash, flickr, flickrSearch, private, password, user)
            VALUES (".$this->db->escape($data['title']).",
                ".$this->db->escape($data['info']).",
                ".$this->db->escape($data['geonameId']).",
                ".$this->db->escape($data['date']).",
                ".$this->db->escape($data['time']).",
                ".$this->db->escape($data['twitter']).",
                ".$this->db->escape($data['twitterHash']).",
                ".$this->db->escape($data['flickr']).",
                ".$this->db->escape($data['flickrSearch']).",
                ".$this->db->escape($data['private']).",
                ".$this->db->escape($data['password']).",
                ".$this->db->escape($data['user'])."
                )";

            $this->db->query($sql);
            
            /*
             * Vi laddar in bitlymodellen så vi kan förkorta url'et
             */
            
            $this->load->model('Bitlymodel') ;
            
            $shortLink = $this->Bitlymodel->shorten("http://bjudin.dubbe.se/invitation/show/".mysql_insert_id()) ;
            
            /*
             * Så skickar vi iväg inbjudningarna, skulle vi få nått felmeddelande där så sparas det i arrayen error
             */
            
            $error = $this->_sendInvitations($_POST['participants'], $data['title'], $shortLink, $data['password']) ;
            
            /*
             * Vi skapar en flash-session med eventuella felmeddelanden
             */
            if (!empty($error)) {
                $this->session->set_flashdata('error', $error);
            }
            
            /*
             * Och så skickar vi iväg dem till det nyskapade eventet
             */
            
            redirect('/invitation/show/'.mysql_insert_id(), 'refresh');
        
            
        } else {

            /* 
            * Om det blir fel i formuläret skriver vi ut felen och laddar om formuläret
            */
            
            $this->load->view("error", $data);
            $this->load->view("createInvitation", $data);
            $this->load->view("footer");
        }
        
    }
    
    function show($eventId) {
        
        /*
         * Vi börjar med att kika på om det finns några fel som ska presenteras.
         */
        
        if($this->session->flashdata('error') !== FALSE) {
            
            $errArr['error'] = $this->session->flashdata('error') ;
            $this->load->view('error', $errArr) ;
        }
        
        /*
         * Så plockar vi fram eventinformationen från databasen
         */
        
        $query = $this->db->query("SELECT * FROM events WHERE id = ".$this->db->escape($eventId)." LIMIT 1") ;
        
        $row = $query->row() ;
        
        
        
        /* 
         * Så börjar vi att kika på om det kanske behövs ett lösenord? 
         * Och om det behövs och lösenordet är medskickat kontrollerar vi om det är korrekt
         */
        
        if ($row->private == 1) {
            if(isset($_POST['password']))
                if ($row->password == $_POST['password']) {
                    $this->_show($eventId, $row) ;
                } else {
                    $data['error'] = array("Du har angvit ett felakitigt lösenord.") ;

                    $this->load->view("error", $data);   
                    $this->_password($eventId) ;
                }
            else {
                $this->_password($eventId) ;
            } 
        } else {
            $this->_show($eventId, $row) ;
        }
        
        

    }
    
    function _password($eventId) {
        
        /*
         * Om det krävs lösenord presenterar vi lösenords-vyn
         */
        
        $data['id'] = $eventId ;
        
        $this->load->view("password", $data);
        
        $this->load->view("footer");
    }
    
    function _show($eventId, $row) {
        
        /*
         * Här ritar vi upp informationen, först lägger vi till variablar till arrayen $data som vi skickar till vyn
         */
        
        $data['id'] = $row->id ;
        $data['title'] = $row->title ;
        $data['info'] = $row->info ;
        $data['date'] = $row->date ;
        $data['time'] = $row->time ;
        $data['user'] = $row->user ;
        
        // Så laddar vi de modeller vi behöver
        
        $this->load->model("Twittermodel") ;
        $this->load->model("Geonamemodel") ;
        $this->load->model("Yrmodel") ;
        $this->load->model("Flickrmodel") ;
        
        // Här sätter vi en twitter hashtag
        
        $this->Twittermodel->setHashtag($row->twitterHash) ;
        
        // Här sätter geoplaceid till geoplacemodel 
        
        $this->Geonamemodel->setGeonameId($row->place) ;
        
        // Och så sätter vi place till stad, region 
        
        $data['place'] = $this->Geonamemodel->getCity().", ".$this->Geonamemodel->getRegion() ;
        
        // Vi ger yr den information de behöver 
        
        $this->Yrmodel->setPlace($this->Geonamemodel->getCity(), $this->Geonamemodel->getRegion()) ;
        
        $data['yr'] = $this->Yrmodel->getForecastDay($row->date, $row->time) ;
        
        // Om twitter är satt skickar vi med twitter-data
        
        if ($row->twitter == 1) {
            $data['twitter'] = $this->Twittermodel->getItems() ;
        }

        // Om flickr är satt skickar vi med flickr-data
        
        if ($row->flickr == 1) {
            $data['flickr'] = $this->Flickrmodel->getPhotoCluster($row->flickrSearch) ;
        }
        
        // Vi ska kika lite på vilka som anmält sig till eventet också */
        
        $query = $this->db->query("SELECT * FROM participants WHERE eventId = '$eventId'");
        
        // Vi skickar med vilka som anmält sig till eventet i $data['participants'] 
        $data['participants'] = array() ;
        
        if ($query->num_rows() > 0)
        {
            foreach ($query->result_array() as $row) {
                array_push($data['participants'], $row['user']) ;
            }
        }
        
        // Antalet anmälda
        $data['numPartic'] = $query->num_rows();
        
        // Så kollar vi om den inloggade användaren är anmäld eller inte
        if($this->session->userdata('username') !== FALSE) {
            $username = $this->session->userdata('username') ;
               
            $query = mysql_query("SELECT * FROM participants WHERE eventId = '$eventId' AND user = '$username'");
            $result = mysql_fetch_array($query);

            if(empty($result)) {
                $data['participate'] = true ;
            } else {
                $data['participate'] = false ;
            }
        }
        
        // Laddar vyerna som behövs
        $this->load->view("viewInvitation", $data);
        
        $this->load->view("footer");
    }
    
    function participateGet($username, $eventId) {
        
        /* 
        * När inloggad anmäler sig till ett event 
        */

        // Vi måste kontrollera så att användaren verkligen är den inloggade användaren
        if($this->session->userdata('username') !== FALSE && $this->session->userdata('username') == $username) {
            $this->_participate($username, $eventId) ;
        } else {
            
            $error = array("Du måste vara inloggad med ditt twitterkonto.") ;

            $this->session->set_flashdata('error', $error);
            
        }
        
        redirect('/invitation/show/'.$eventId, 'refresh');
    }
    
    function participatePost() {
        
        /* 
        * När ej-inloggad anmäler sig till ett event 
        */
        
        if($this->_validEmail($_POST['username'])) {
            
            $this->_participate($_POST['username'], $_POST['eventId']) ;
        } else {

            $error = array("Du måste ange en korrekt epost-adress.") ;
            
            $this->session->set_flashdata('error', $error);

        }
        
        redirect('/invitation/show/'.$_POST['eventId'], 'refresh');
    }
    function _participate($username, $eventId) {
        
        // Om checkerna ovan går igenom kikar vi först så att användaren inte redan tackat ja till eventet
        $query =  $this->db->query("SELECT * FROM participants WHERE user = '$username' AND eventId = $eventId");

        
        if ($query->num_rows() == 0){
            
            // Om han inte finns sedan tidigare lägger vi till honom
            $sql = "INSERT INTO participants (user, eventId) VALUES (".$this->db->escape($username).", ".$this->db->escape($eventId).")" ;
            $this->db->query($sql);
            
        } else {
            
            // Om han finns skickar vi ett felmeddelande och laddar om sidan
            $error = array("Du har redan tackat ja till det här eventet.") ;

            $this->session->set_flashdata('error', $error);
        }
    }
    
    function _stripText($string) {
        
        // Kör igenom lite php säkerhet innan vi skriver till databasen
        
        $string = strip_tags($string) ;
        $string = htmlspecialchars($string) ;
        $string = nl2br($string) ;

        return $string ;
    }
    
    function _reqText($string) {
        
        // Kollar så att det är inskrivet någon text
        
        if ($string == NULL) {
            return false ;
        } else {
            return true ;
        }
    }
    function _getGeonameId($string) {
        
        // Här kollar vi så att stad och område finns med i geonames-databasen
       
        $string = split(",", $string) ;
        
        if($string[0] != "") {
            $query = $this->db->query("SELECT geonameid, admin1_code FROM geonames WHERE name = '$string[0]'") ;

            if($query->num_rows() >= 1) {
                foreach ($query->result() as $row)
                {
                    $query2 = $this->db->query("SELECT * FROM geonamesRegions WHERE id = '$row->admin1_code'") ;
                    if($query2->num_rows() >= 1) {
                        return $row->geonameid ;
                        echo $row->geonameid ;
                    }
                }
            } else {
                $return = false ;
            }
        
        } else {
            $return = false ;
        }
        
       if(@trim($string[1] != "")) {
            $area = trim($string[1]) ;
            $query = $this->db->query("SELECT * FROM geonamesRegions WHERE name = '$area'") ;

            if($query->num_rows() >= 1) {

            } else {
                $return = false ;
            }
        
        } else {
            $return = false ;
        }

        return $return ;
    }
    
    
    function _validEmail($email)
    {
       // Funktion för att validera epostadressen
       $isValid = true;
       $atIndex = strrpos($email, "@");
       if (is_bool($atIndex) && !$atIndex)
       {
          $isValid = false;
       }
       else
       {
          $domain = substr($email, $atIndex+1);
          $local = substr($email, 0, $atIndex);
          $localLen = strlen($local);
          $domainLen = strlen($domain);
          if ($localLen < 1 || $localLen > 64)
          {
             // local part length exceeded
             $isValid = false;
          }
          else if ($domainLen < 1 || $domainLen > 255)
          {
             // domain part length exceeded
             $isValid = false;
          }
          else if ($local[0] == '.' || $local[$localLen-1] == '.')
          {
             // local part starts or ends with '.'
             $isValid = false;
          }
          else if (preg_match('/\\.\\./', $local))
          {
             // local part has two consecutive dots
             $isValid = false;
          }
          else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
          {
             // character not valid in domain part
             $isValid = false;
          }
          else if (preg_match('/\\.\\./', $domain))
          {
             // domain part has two consecutive dots
             $isValid = false;
          }
          else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
          {
             // character not valid in local part unless 
             // local part is quoted
             if (!preg_match('/^"(\\\\"|[^"])+"$/',
                 str_replace("\\\\","",$local)))
             {
                $isValid = false;
             }
          }
          if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
          {
             // domain not found in DNS
             $isValid = false;
          }
       }
       return $isValid;
    }

    
    function _sendInvitations($part, $header, $link, $password) {

        // Skickar inbjudningar till de som ska ha. Om det är något felaktigt så kräver vi inte det på dem igen utan skriver ut ett informativt fel och går vidare.
        
        $participants = explode(",", $part) ;
        
        $error = array() ;
        
        foreach($participants as $part) {
            $part = trim($part) ;
            
            
            if($this->_validEmail($part)) {
               
                array_push($error, $this->_sendEmail($part, $header, $link, $password)) ;
                
            } elseif (preg_match('/^@?[A-Za-z0-9_]+$/', $part)) {
                
                $part = $this->_removeFirst($part, "@") ;
                
                if($this->session->userdata('username')!== FALSE){ 
                    $msg = "Du har blivit inbjuden till $header. $link" ;
                    
                    

                    $this->load->model('Twittermodel') ;
                    if ($this->Twittermodel->checkFollower($part)) {
                        
                        if(!empty($password)) {
                            $msg .= " Lösen: $password" ;
                        } 
                        
                        $this->Twittermodel->postDirectMessage($msg, $part) ;
                    } else {
                        
                        array_push($error, "Du kan inte skicka twitter-meddelande till $part eftersom $part inte följer dig.") ;
                        
                    }
                } else {
                    
                    array_push($error, "Du kan inte skicka twitter-meddelande till $part eftersom du inte är inloggad till twitter.") ;
                    
                }
                
            } elseif ($part == "") 
            {
            }
            else 
            { 
                array_push($error, "Kan inte skicka inbjudan till $part eftersom det varken är en godkänd epostadress eller twitter-alias.") ;      
            }
            
            
              
        }
        
        return $error ;
        
    }
    
    function _sendEmail($to, $header, $link, $password) {
        
        // Skickar mail med smtp.dubbe.se till de som ska ha det
        
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'smtp.dubbe.se' ;
        $config['smtp_port'] = 26 ;
        $config['smtp_user'] = "bjudin@dubbe.se" ;
        $config['smtp_pass'] = "safety01" ;

        
        $this->load->library('email', $config);

        $this->email->from('bjudin@dubbe.se', 'bjudin.nu');
        $this->email->to($to); 

        $this->email->subject('Du har fått en inbjudan');
        if(!empty($password)) {
            $this->email->message('Du har fått en inbjudan till '.$header.'. Lösenordet för eventet är: '.$password.'. Besök inbjudan på länken '.$link.' Det här mailet går inte svara på.');
        } else {
            $this->email->message('Du har fått en inbjudan till '.$header.'.Besök inbjudan på länken '.$link.' Det här mailet går inte svara på.');
        }

        $this->email->send();



        
    }
    
    function _removeFirst($string, $sign) {
        
        // För att ta bort # och @ i början på twitter-hash och twitter-användarnamn.
        
        if($string[0] == $sign) {
            return substr($string, 1) ;
        } else {
            return $string ;
        }
    }
}