#!/usr/bin/php -q
<?php

//Start session
$freedly = curl_init();
$token = "Authorization: OAuth <Your token>";

// set URL- Category Music
curl_setopt($freedly, CURLOPT_URL, "https://cloud.feedly.com/v3/streams/contents?unreadOnly=true&streamId=user/558d1cdc-5e1a-4e3e-9a7f-66b03b7b4ac6/category/a3c7a9aa-f8b4-46ad-8e3b-e1025f3096f4&count=10");

// set Token
curl_setopt($freedly, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $token));
curl_setopt($freedly, CURLOPT_RETURNTRANSFER, 1);

//execution code in conection
$result = curl_exec($freedly);

//closing
curl_close($freedly);

$obg = json_decode($result);
$titulos = $obg->items;

//MAIN PROGRAM
set_time_limit(0);
require('phpagi.php');

$agi = new AGI();

$i = 1;

//When API don't have news unread
if(empty($titulos)){
    $agi->verbose("Sem noticias");
    $agi->stream_file('feedly/nonews');
    $agi->hangup();
}

// Menu news Music
foreach ($titulos as $a){
    $link = $a->alternate;
    $agi->stream_file('feedly/title');
    $agi->verbose($a->title);
    $agi->text2wav($a->title);
    $agi->stream_file('feedly/degite');
    $agi->say_number($i);

    //Get link
    $result = $agi->get_data('beep', 3000, 1);

    if($result['result'] == $i){
        foreach ( $link as $d ){
            $agi->verbose($d->href);

            $html = file_get_contents($d->href);
            //load page web
            $doc = new DOMDocument();
            @$doc->loadHTML($html);

            //Scraping
            $xpath = new DOMXPath($doc);
            $article = $xpath->query("//div[@class=\"l-article-content\"]");
            $paragrafo = $xpath->query(".//p", $article->item(0));

            foreach($paragrafo as $node) {
                $agi->verbose($node->nodeValue);
                //AGI reading News
                $agi->text2wav($node->nodeValue);
            }
        }
    }

    $i++;
}

$agi->hangup();

?>
