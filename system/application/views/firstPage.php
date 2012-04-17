<div id="main">
    <h1>Välkommen</h1>
    <p>Bjudin.nu är skriven av Thomas Dahlberg som projektarbete i en XML-kurs.</p>
    <p>Det är en event-applikation för twitter där man ska kunna samlas och diskutera kring ett evenemang eller händelse. I dagsläget är bjudin.nu sammankopplad med twitter, geonames, yr.no, flickr samt bitly.</p>
    <p>Planen var att det skulle fungera lite liknande facebooks event, fast utan facebook. Det går att skicka ut inbjudningar till epost eller twitter, kommentera på event går enbart göra om man är inloggad med sitt twitter konto (om man inte har något kan man skaffa ett <a href="https://twitter.com/signup">här</a>).</p> 
</div>

<div id="latestEvent">
    <div id="latestEventHeader">
        <h3>Kommande event</h3>
    </div>
    
    <div id="latestEventMain">
        <?$i = 0 ;?>
        <?foreach($query as $event):?> 
            <div class="event">
                <a href="invitation/show/<?=$event['id'];?>"><h4><?=$event['title'];?></h4></a>
                <span class="headerInfo"><?=$place[$i];?> | <?=$event['date'];?> <?=$event['time'];?> </span>
            </div>
        <?$i++;?>
        <?endforeach;?>
        
    </div>
</div>


<div class="clr"></div>

