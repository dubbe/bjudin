<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>bjudin.nu</title>
        
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.7/jquery-ui.min.js"></script>
    <script type="text/javascript" src="http://cdn.jquerytools.org/1.2.5/all/jquery.tools.min.js"></script>
    <script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.min.js"></script>
    <script type="text/javascript" src="http://dubbe.se/studier/LNU/1DV410/Projekt/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script> 

    <link rel="stylesheet" type="text/css" href="http://dubbe.se/studier/LNU/1DV410/Projekt/styles/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="http://dubbe.se/studier/LNU/1DV410/Projekt/styles/styles.css" /> 
    <link rel="stylesheet" type="text/css" href="http://dubbe.se/studier/LNU/1DV410/Projekt/js/fancybox/jquery.fancybox-1.3.4.css" />
    
</head>
<body> 
    <div id="header">
        <div class="container">
            <div id="meny">
                <a href="/invitation/create/" class="meny">Skapa inbjudan</a> 
                <?if($this->session->userdata('username') !==FALSE):?>
                    
                    | Du är inloggad som <?=$this->session->userdata('username');?> <a href="/twitter/logout/" class="meny">Logga ut</a><br />
                <?else:?>  
                    
                    | <a href="/twitter/login/" class="meny">Logga in med Twitter</a>
                <?endif;?>
            </div>  
            <div id="logo"><a href="http://bjudin.dubbe.se"><img src="/files/images/bjudin-logo.png" /></a></div>
                    
             
            <div class="clr"></div>
        </div>
    </div>
    <div id="content">
        <div class="container">        

