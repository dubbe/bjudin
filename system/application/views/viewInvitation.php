<script>
    $(function() {
        
        $("a.single_image").fancybox({
            titlePosition: 'inside'
        });
        
        $("a#inline").fancybox();

        $(".weather").tooltip({
            position: "center right",
            offset: [-2, 10],
            effect: "fade"

        }); 
        
        $(".participants").tooltip({
            position: "center right",
            offset: [-2, 10],
            effect: "fade"

        });
        
        $(".single_image").tooltip({
            position: "center right",
            offset: [-2, 10],
            effect: "fade"

        });
        
        $("#submitBtn").click(function() {
            
            if ($("#tweet").val() == "") {
                loadTweets() ;
                return false ;
            }
            else {
                $.ajax({  
                    type: "POST",  
                    url: "http://bjudin.dubbe.se/twitter/tweet/",  
                    data: "tweet="+$("#tweet").val()+"&hashtag="+$("#hashtag").val(),
                    success: function(){
                        $("#tweet").val("") ;
                        loadTweets() ;
                        setTimeout("loadTweets()",60000);
                        
                    }
                });  
            return false ;
            }
            
        }); 
        
        <?if (isset($twitter['tweets'])) :?>
            loadTweets() ;
        <?endif;?>
        
        $('.charCount').each(function() {
            var input = '#' + this.id;
            var count = input + '_count';
            $(count).show();
            charCount(input, count);
            $(this).keyup(function() { charCount(input, count) });
        });



    });
    <?if (isset($twitter['tweets'])) :?>
    function loadTweets() {
        $.getJSON('/twitter/getTweets/<?=$twitter['hashtag'];?>', function(data) {

            $("#tweets").empty() ;

            $.each(data.tweets, function(i,tweet){
                var img = $("<img>").attr({
                    src: tweet.img
                }).addClass("tweetImg")
                var span = $("<span>").addClass("twitterTime").html(tweet.timeAgo+"<br />") ;

                $("<p>").appendTo("#tweets").append(img).append(span).append(tweet.author+": "+tweet.title) ;
            });


        }) ;
        
        setTimeout("loadTweets()",300000);
    }
    <?endif;?>
    
    function charCount(field, count) {

        var number = 140 - $("#hashtag").val().length - 2;

        number = number - $(field).val().length ;
        
        $(count).text(number + " tecken kvar");

    }

        
</script>
<div id="mainHeader">
    <div id="weather">
        <? if(isset($yr['symbolNr'])): ?>
            <img src="http://dubbe.se/studier/LNU/1DV410/Projekt/files/images/weather/<?=$yr['symbolNr'];?>.png" class="weatherIcon weather" />
            <p class="tooltip">
                Vind: <?=$yr['windSpeed']?>mps <?=$yr['windCode']?><br />
                Nederbörd: <?=$yr['precip']?>mm <br />
                <br />
                Prognosen gäller:<br /> <?=$yr['from']?> - <?=$yr['to']?><br />
                <a href="<?=$yr['link']?>">Forecast from yr.no</a>
            </p>
            <span class="weatherTemperature"><?=$yr['temperature']?>&#176;C</span>
        <? else :?>
            <img src="http://dubbe.se/studier/LNU/1DV410/Projekt/files/images/weather/na.png" class="weatherIcon" />
        <? endif; ?>
    </div>
    <h1><?=$title?></h1>
    <p class="headerInfo"><?=$date?> <?=$time?></p>
</div>

<div id="mainContent">
    <div id="mainContentLeft">
        <p><?=$info?></p>
    </div>
    <div id="mainContentRight">
        <p><b>Plats</b> <br />
        <?=$place?></p>
        <p><b>Arrangör</b> <br />
        <?=$user?></p>
        <p><b>Deltagare</b> <br />
        <a href="#" class="participants"><?=$numPartic;?> kommer</a>
            <p class="tooltip">
                <?foreach ($participants as $partic):?>
                    <?=$partic;?><br />
                <?endforeach;?>
            </p>
        </p>
        <br />
        <?if($this->session->userdata('username')!==FALSE):?>
            <?if($participate):?>
                <a href="/invitation/participateGet/<?=$this->session->userdata['username'];?>/<?=$id;?>" class="participate">Jag kommer!</a>
            <?endif;?>
        <?else:?>
            <a id="inline" href="#data" class="participate">Jag kommer!</a>

            <div style="display:none">
                <div id="data">
                    <form action="/invitation/participatePost" method="post">
                        Ange din epostadress:<br />
                        <input type="text" name="username" id="username" /><br />
                        <input type="hidden" value="<?=$id;?>" name="eventId" id="eventId" />
                        <input type="submit" name="submit" class="submit" value="Skicka" />  <br />
                    </form>
                    
                    
                </div>

            </div>
        <?endif;?>
    </div>
    <div class="clr"></div>
</div>

<div id="mainSocial">
    <?if (isset($twitter['tweets'])) :?>
    <div id="mainTwitter">
        
        <h2>Twitter</h2>
        
        <?if($this->session->userdata('username')!==FALSE):?>
            
        <div id="tweetDiv">
            <form method="post" name="tweet" action="http://bjudin.dubbe.se/twitter/tweet/">

                <textarea name="tweet" id="tweet" class="sendTweet charCount"></textarea><br />
                <input type="hidden" value="<?=$twitter['hashtag'];?>" name="hashtag" id="hashtag" />
                <input type="hidden" value="<?=$id;?>" name="eventId" id="eventId" /><br />
                <input type="submit" name="submit" class="submit submitTweet" id="submitBtn" value="Skicka" />  
                <span class="twitterInfo">Hashtag: <?=$twitter['hashtag'];?><br />
                    <div id="tweet_count" style="display:none"></div></span>
            </form>
            <div class="clr"></div>
        </div>
        
        <?endif;?>
        
        
        
        <div id="tweets">
            <?if(!empty($twitter['tweets'])):?>

                <?php foreach($twitter['tweets'] as $tweet):?>      
                    <p>
                        <img src="<?=$tweet['img'];?>" class="tweetImg" />
                        <span class="twitterTime"><?=$tweet['timeAgo'];?></span><br />
                        
                        <?=$tweet['author'];?> - <?=$tweet['title'];?>
                    </p>    
                <?php endforeach;?>
            <?else:?>
                Det finns inga tweets ännu.
            <?endif;?>
        </div>
    </div>
    <?endif;?>
    <?if (isset($flickr['flickr'])) :?>
    <div id="mainFlickr">
        <h2>Flickr</h2>
        <?php foreach($flickr['flickr'] as $photo):?> 

               <a class="single_image" href="<?=$photo['link']?>" title="<?=$photo['title']?> <br /> Fotograf: <?=$photo['username']?><br />Bilderna kommer från flickr"><img src="<?=$photo['linkThumb']?>" class="thumb"  /></a>

        <?php endforeach;?>
    </div>
    <?endif;?>
    
    <div class="clr"></div>
</div>




