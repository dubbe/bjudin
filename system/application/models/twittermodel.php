<?PHP

class Twittermodel extends My_Model {
   
        
    
    var $hashtag = '' ;
    private $consumerKey =  'pbTnd0KAUyMZcJV13wffA';
    private $consumerSecret = 'PZzikXcm5uj1iGA4hz3wHtQaWtFn8fuxanbesrs' ;
    
    private $twitteroauth ;

      
    function Twittermodel()
    {
        // Call the Model constructor
        parent::My_Model();
        
        require_once(APPPATH . 'libraries/twitter/twitteroauth.php');
        
    }
    
    function login() {
        
        // Skapa en twitter-instance 
        $this->twitteroauth = new TwitterOAuth($this->consumerKey, $this->consumerSecret);  
        // Skapar en token och redirektar till getOauth funktionen
        $request_token = $this->twitteroauth->getRequestToken('http://bjudin.dubbe.se/index.php/twitter/getOauth');  
  
        // Sparar token till session
        $this->session->set_userdata('oauth_token', $request_token['oauth_token']);
        $this->session->set_userdata('oauth_token_secret', $request_token['oauth_token_secret']); 

        // Och så om allt går fint!
        if($this->twitteroauth->http_code==200){  
            // Skapar vi ett url som vi hoppar till
            $url = $this->twitteroauth->getAuthorizeURL($request_token['oauth_token']); 
            header('Location: '. $url); 
        } else { 
            // Och annars dödar vi med ett felmeddelande. 
            die('Nu blev nåt trasigt fel.');  
        }  
    }
    
    function getOauth() {
      
         if(!empty($_GET['oauth_verifier']) && $this->session->userdata('oauth_token') !='' && $this->session->userdata('oauth_token_secret') !=''){
             // Ny instans med två nya parametrar
            $this->twitteroauth = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->session->userdata('oauth_token'), $this->session->userdata('oauth_token_secret')) ;
            // Så får vi en access-token
            $access_token = $this->twitteroauth->getAccessToken($_GET['oauth_verifier']);
            // Sparar den i en session
            $this->session->set_userdata('access_token', $access_token);
            // Och så plockar vi hem användarinformationen
            $user_info = $this->twitteroauth->get('account/verify_credentials');
            
            if(isset($user_info->error)){
                // Nåt gick fel, så jag borde nog skriva ett felmeddelande eller så här.
            } else {
                // Annars ser vi om han redan finns i min databas
                
                $query = mysql_query("SELECT * FROM users WHERE oauth_provider = 'twitter' AND oauth_uid = ". $user_info->id);
                $result = mysql_fetch_array($query);

                // Och om inte så lägger vi till honom
                if(empty($result)){
                    $query = mysql_query("INSERT INTO users (oauth_provider, oauth_uid, username, oauth_token, oauth_secret) VALUES ('twitter', {$user_info->id}, '{$user_info->screen_name}', '{$access_token['oauth_token']}', '{$access_token['oauth_token_secret']}')");
                    $query = mysql_query("SELECT * FROM users WHERE id = " . mysql_insert_id());
                    $result = mysql_fetch_array($query);
                } else {
                    // Uppdaterar tokenet
                    $query = mysql_query("UPDATE users SET oauth_token = '{$access_token['oauth_token']}', oauth_secret = '{$access_token['oauth_token_secret']}' WHERE oauth_provider = 'twitter' AND oauth_uid = {$user_info->id}");
                }

                // Och sparar en massa sessions.
                
                $session = array(
                'id' => $result['id'],
                'username' => $result['username'],
                'oauth_uid' => $result['oauth_uid'],
                'oauth_provider' => $result['oauth_provider'],
                'oauth_token' => $result['oauth_token'],
                'oauth_secret' => $result['oauth_secret']
                );
                
                $this->session->set_userdata($session);

                // Så skickar vi till startsidan
                header('Location: http://bjudin.dubbe.se');
            }

        } else {
            // Här har också nåt blivit fel, borde skicka felmeddelande här också
        } 
    }
    
    function logout() {
        $this->session->sess_destroy();
        header('Location: http://bjudin.dubbe.se');
    }

    function setHashtag($newHashtag) {
        $this->hashtag = $newHashtag ;
    }

    function getItems()
    {

        $simpleXml = $this->loadSimpleXml('http://search.twitter.com/search.atom?q=%23'.$this->hashtag, $this->hashtag, "twitter/hashtag/", 1);
        
        $xmlArray['hashtag'] = $this->hashtag ;
        $xmlArray['tweets'] = array() ;
        
        /* Kollar så att det finns ett entry i xml-filen */
        
        if(@$simpleXml->entry){
            foreach($simpleXml->entry as $entry) {
                $tmpArray = array( "title" => $this->linkTags((string)$entry->title), "author" => $this->authorTag((string)$entry->author->name), "timeAgo" => $this->timeAgo((string)$entry->published), "img" => (string)$entry->link[1]->attributes()->href) ;
                array_push($xmlArray['tweets'], $tmpArray) ;
            }
        }

        return $xmlArray ;


    }
    function postTweet($tweet, $hashtag) {
        
        if($this->session->userdata('username') !== FALSE){  

            $this->twitteroauth = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->session->userdata('oauth_token') ,$this->session->userdata('oauth_secret'));  
            $this->twitteroauth->post('statuses/update', array('status' => $tweet." #".$hashtag)); 
        } else {
            echo "Du kan inte skicka till twitter utan att vara inloggad!" ;
        }
         
        
    }
    
    function postDirectMessage($tweet, $user) {
        if($this->session->userdata('username')!== FALSE){ 
            $this->twitteroauth = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->session->userdata('oauth_token'), $this->session->userdata('oauth_secret'));
            $this->twitteroauth->post('direct_messages/new', array('text' => $tweet, 'screen_name' => $user));
        } else {
            echo "Du kan inte skicka till twitter utan att vara inloggad!" ;
        }
    }
    function checkFollower($user) {
        
        $return = false ;
        
        if($user == $this->session->userdata('username')) {
            $return = true ;
        }
        elseif($this->session->userdata('username')!== FALSE){ 
           $this->twitteroauth = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->session->userdata('oauth_token'), $this->session->userdata('oauth_secret'));
            $return = $this->twitteroauth->post('friendships/exists', array('user_b'=> $this->session->userdata('username'),  'user_a' => $user));
        } else {
        }
        
        return $return ;
    }
    
    function linkTags($tweet) {
        // ordnar så att användarnmn och hashtaggar blir länkade
        
        $tweet = preg_replace('/(^|\s|\?|\.|\!|,)@(\w+)/',
        '\1<a href="http://www.twitter.com/\2">@\2</a>',
        $tweet);
        
        return preg_replace('/(^|\s|\?|\.|\!|,)#([a-zA-z0-9åäöÅÄÖ]+)/',
        '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>',
        $tweet); ;
    }
    
    function authorTag($author) {
        
        $author = split("[(]", $author) ;
        
        return "<a href='http://twitter.com/".$author[0]."'>@".$author[0]."</a>" ;
        
    }
    
    function timeAgo($timeStamp) {
       
        // En enkel funktion för att skriva hur länge sedan twitter-meddelandet är skickat
       $time = strtotime($timeStamp) ; 
        
       $periods = array("sekunder", "minuter", "timmar", "dagar", "veckor", "månader", "år", "decennium");
       $lengths = array("60","60","24","7","4.35","12","10");

       $now = time();

       $difference = $now - $time;

       for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
           $difference /= $lengths[$j];
       }

       $difference = round($difference);

       if($difference != 1) {
           $periods[$j].= "";
       }

       return "$difference $periods[$j] sedan ";
    }

}
?>