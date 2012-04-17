<?php
class Twitter extends Controller
{
 
     function Twitter()
     {
        parent::Controller();
        $this->load->model("Twittermodel") ;
     }

     function index()
     {

     }
     function login() 
     {
         $this->Twittermodel->login() ;
     }
     
     function logout() 
     {
         $this->Twittermodel->logout() ;
     }
     
     function getOauth() {
        
        $this->Twittermodel->getOauth() ;
     }
     
     function tweet() {
         
         $this->Twittermodel->postTweet($_POST['tweet'], $_POST['hashtag']) ;
         
         redirect('/invitation/show/'.$_POST['eventId'], 'refresh') ;
     }
     
     function getTweets($hash) {
         
         $this->Twittermodel->setHashtag($hash) ;
         
         print_r(json_encode($this->Twittermodel->getItems())) ;
 
     }
}
?>