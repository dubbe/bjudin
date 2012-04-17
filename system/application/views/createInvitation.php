<script>
    $(function() {
        
        // Vi använder jquery-ui och dess datepicker
        $("#datepicker").datepicker({
            dateFormat: "yy-mm-dd"
        });
        
        // Vi kör med tooltips för att visa information i formuläret
        $(".form1, .form2, .form3").tooltip({
            position: "center right",
            offset: [-2, 10],
            effect: "fade"
        }); 

        // Vi har en autocomplete som plockar informationen med ajax och presenterar den 
        $("#placeInput").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "/index.php/listCities/",
                    datatype: "json",
                    data: {
                        maxResult: 20,
                        name: request.term
                    },
                    success: function(data) {
                        response( $.map( data.city, function( item ) {
                            return {
                                geonameId: item.geonameId,
                                label: item.name + ", " + item.region
                            }
                        }));
                    } 
                })
            },
            select: function(event, ui) {
                $( "#placeInput" ).val( ui.item.label );
                $( "#hiddenGeonameId" ).val( ui.item.geonameId ) ;
                return false ;
                
            }            
        })
        .data( "autocomplete" )._renderItem = function( ul, item ) {
            return $( "<li></li>" )
                .data( "item.autocomplete", item )
                .append("<a>" + item.label + "</a>" )
                .appendTo( ul );
        };

        /* Vi börjar med att disabla de fälten som inte ska gå skriva i ännu */
        $("#form2, #form3").addClass("inactive") ;
        $("#cont1").removeClass("hidden") ;
        $("#submit, #twitterDl, #flickrDl, #passwordDl").addClass("hidden") ;
        
        $("#twitterHash, #flickrSearch, #password, .form2, .form3").attr("disabled", "disabled") ;

        /* Så tar vi och startar med lite validering för fält 1 */
        
        var v = $("#inputForm").validate({
            rules: {
                title: "required",
                info: "required",
                place: "required",
                date: "required",
                time: "required"
            }, 
            messages: {
                title: "Du måste ange en titel",
                info: "Du måste skriva in en kort informationstext",
                place: "Du måste ange en plats",
                date: "Du måste ange ett datum",
                time: "Du måste ange en tid"  
            },
            errorClass: "formError",
            errorPlacement: function(error, element) {
                if(element.next().children(".error").first().length == 0) {
                    element.next().append("<div class='error'>"+error.text()+"</div>") ;
                } else {
                    element.next().children(".error").first().text(error.text()) ;
                }
            },
            success: function(element) {
                element.next().children(".error").remove() ;
            }
            
        });
        
        $("#cont1").click(function() {
	  if (v.form()) {           
	    $(".form2").removeAttr("disabled") ;
            $(".form1").attr("disabled", "disabled") ; 
            $("#form2").removeClass("inactive") ;    
            $("#cont2").removeClass("hidden") ;           
            $("#cont1").addClass("hidden") ;
            $("#form1").addClass("inactive") ;
          }
	});
        
        $("#cont2").click(function() {
	  if (v.form()) {
	    $(".form3").removeAttr("disabled") ;
            $(".form2").attr("disabled", "disabled") ; 
            $("#form3").removeClass("inactive") ;
            $("#submit").removeClass("hidden") ;
            $("#cont2").addClass("hidden") ;
            $("#form2").addClass("inactive") ;
	  }
	});
        
        
        $("#twitterYes").click(function() {    
           $("#twitterHash").removeAttr("disabled") ;
           $("#twitterDl").removeClass("hidden") ;
        });
        $("#twitterNo").click(function() {
            $("#twitterHash").attr("disabled", "disabled") ;
            $("#twitterDl").addClass("hidden") ;
        });
        
        $("#flickrYes").click(function() {    
           $("#flickrSearch").removeAttr("disabled") ;
           $("#flickrDl").removeClass("hidden") ;
        });
        $("#flickrNo").click(function() {
            $("#flickrSearch").attr("disabled", "disabled") ;
            $("#flickrDl").addClass("hidden") ;
        });
        
        $("#privateYes").click(function() {    
           $("#password").removeAttr("disabled") ;
           $("#passwordDl").removeClass("hidden") ;
        });
        $("#privateNo").click(function() {
            $("#password").attr("disabled", "disabled") ;
            $("#passwordDl").addClass("hidden") ;
        });
        

        $("#inputForm").submit(function(e) {
            $(".form1, .form2, .form3").removeAttr("disabled") ;
        })

        // Här fuskar vi lite med php i javascript-koden för att kunna sätta checked om den kommer tillbaka med fel

        <?if(isset($twitter) && $twitter == 1):?>

        $("[name=twitter]").filter("[value=1]").attr("checked","checked");
        $("#twitterHash").removeAttr("disabled") ;
        $("#twitterDl").removeClass("hidden") ;
        <?endif;?>
            
        <?if(isset($flickr) && $flickr == 1):?>

        $("[name=flickr]").filter("[value=1]").attr("checked","checked");
        $("#flickrSearch").removeAttr("disabled") ;
        $("#flickrDl").removeClass("hidden") ;
        <?endif;?>
            
        <?if(isset($private) && $private == 1):?>

        $("[name=private]").filter("[value=1]").attr("checked","checked");
        $("#password").removeAttr("disabled") ;
        $("#passwordDl").removeClass("hidden") ;
        <?endif;?>
    });
</script>
<form name="input" action="/invitation/save/" method="post" class="myForm" id="inputForm">
    <div id="form1" class="formField">
        <div class="formHeader"><h1>1.</h1></div>
        <div class="formHeader2"><h3>Ange basinformation.</h3></div>
        <div class="formMain"> 
            <dl>
                <dt><label for="title">Titel: </label></dt>
                <dd><input type="text" name="title" id="title" class="form1" value="<?=(isset($title))?$title:""?>" /><p class="tooltip">Namnge ditt event</p></dd>
            </dl>
            <dl>
        	<dt><label for="info">Beskrivning:</label></dt>
                <dd><textarea name="info" id="info" rows="5" class="form1"><?=(isset($info))?$info:""?></textarea><p class="tooltip">Skriv en kort beskrivning</p></dd>
            </dl>   
            <dl>
                <dt><label for="place">Plats: </label></dt>
                <dd><input type="text" name="place" id="placeInput" class="form1" value="<?=(isset($place))?$place:""?>" /><p class="tooltip">Välj en plats ur listan som kommmer av sökningen</p></dd>
            </dl>
            <dl>
                <dt><label for="date">Datum: </label></dt>
                <dd><input type="text" name="date" id="datepicker" class="form1" value="<?=(isset($date))?$date:""?>" /><p class="tooltip">Välj ett datum</p></dd>
            </dl>
            <dl>
                <dt><label for="time">Tid: </label></dt>
                <dd><input type="text" name="time" id="time"class="form1" value="<?=(isset($time))?$time:""?>"/><p class="tooltip">Skriv in tiden då eventet börjar</p></dd>
            </dl>
        </div>
        <div class="formFooter">
            <input type="button" name="cont1" id="cont1" class="form1 submit hidden" value="Fortsätt"/>
    </div>
    </div>
    
    <div id="form2" class="formField">
        <div class="formHeader"><h1>2.</h1></div>
        <div class="formHeader2"><h3>Koppla eventet till twitter och flickr.</h3></div>
        <div class="formMain"> 
        
            
            <dl>
        	<dt><label for="twitter">Twitter:</label></dt>
                <dd><input type="radio" id="twitterYes" name="twitter" value="1"  class="form2 radio"/> Ja<p class="tooltip">Visar tweets med den valda hashtaggen och skapar en enkel kommentarssystem</p> 
                    <input type="radio" id="twitterNo" name="twitter" value="0" checked class="form2 radio" /> Nej<p class="tooltip">Utan twitter går det inte kommentera</p></dd>
            </dl>  
            <dl id="twitterDl">
                <dt><label for="twitterHash">Twitter hashtag: </label></dt>
                <dd><input type="text" name="twitterHash" id="twitterHash" class="required" value="<?=(isset($twitterHash))?$twitterHash:""?>" /><p class="tooltip">Välj en unik hashnyckel till twitter</p></dd>
            </dl>
            <dl>
        	<dt><label for="flickr">Flickr:</label></dt>
                <dd><input type="radio" id="flickrYes" name="flickr" value="1" class="form2 radio" /> Ja <p class="tooltip">Visar bilder från flickr</p>
                    <input type="radio" id="flickrNo" name="flickr" value="0" class="form2 radio" checked/> Nej<p class="tooltip">Visar inga bilder</p></dd>
            </dl>  
            <dl id="flickrDl">
                <dt><label for="flickrHash">Flickr  sökning: </label></dt>
                <dd><input type="text" name="flickrSearch" id="flickrSearch" class="required" value="<?=(isset($flickrSearch))?$flickrSearch:""?>"/><p class="tooltip">Skriv in den tagg som flickr ska söka på</p></dd>
            </dl>
            

        </div>  
        <div class="formFooter">
            <input type="button" name="cont2" class="form2 submit hidden" id="cont2" value="Fortsätt"/>
        </div>
    </div>
    <div id="form3" class="formField">
        <div class="formHeader"><h1>3.</h1></div>
        <div class="formHeader2"><h3>Ange eventets åtkomstinställningar samt bjud in deltagare.</h3></div>
        <div class="formMain"> 
            <dl>
        	<dt><label for="private">Privat:</label></dt>
                <dd><input type="radio" id="privateYes" name="private" value="1" class="form3 radio"/> Ja <p class="tooltip">Gör eventet privat.</p>
                    <input type="radio" id="privateNo" name="private" value="0" checked class="form3 radio" /> Nej<p class="tooltip">Låter vem om helst komma åt eventet</p></dd>
            </dl> 
            <dl id="passwordDl">
                <dt><label for="password">Lösenord: </label></dt>
                <dd><input type="text" name="password" id="password" class="password required" value="<?=(isset($password))?$password:""?>" /><p class="tooltip">Ange ett lösenord som de inbjudna behöver ange</p></dd>
            </dl>
            <dl>
        	<dt><label for="participants">Deltagare:</label></dt>
                <dd><textarea name="participants" id="participants" rows="5" class="form3"><?=(isset($participants))?$participants:""?></textarea><p class="tooltip">Du kan ange epostadresser <?if($this->session->userdata('username')!==FALSE):?>eller twitter-användarnamn <?endif?>till de vill bjuda in.<br/>Separera med komma.</p></dd>
            </dl>   
            
            <?if($this->session->userdata('username') == FALSE):?>
            <dl>
        	<dt><label for="email">Din epostadress:</label></dt>
                <dd><input type="text" name="email" id="email" class="form3 email required" value="<?=(isset($user))?$user:""?>" /></dd><p class="tooltip">Du måste ange din epostadress för att kunna skapa ett event.</p>
            </dl>  
            <?endif?>

        </div> 
        <div class="formFooter">
            <input type="submit" name="submit" class="form3 submit" id="submit" value="Skicka" />
        </div>
    </div>
      
</form>

<div class="clr"></div>
