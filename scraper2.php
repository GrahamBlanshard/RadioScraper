<?php

$id = 0;
$url = "";

if (isset($_GET['name']) && !empty($_GET['name'])) {
    $id = $_GET['name'];
}

if ($id == "1") {
    $url = "http://my921.ca/data/bds-list.html";
} else if ($id == "2") {
    $url = "http://www.lite957.ca/data/bds-list.html";
} else if ($id == "3") {
    $url = "http://www.x929.ca/data/bds-list.html";
}
else {
    echo "BAD CODE PROVIDED<br />\n";
    return;
}

$html = get_data($url);
$data = parse($html);

echo "<table id=\"resultstbl\">\n";
echo " <colgroup><col class=\"name\" /><col class=\"count\" /><col class=\"last_played\" /><col class=\"time_since\" /></colgroup>\n";
echo "<tr>\n<th>Song/Artist</th><th>Played Count</th><th>Last Played</th><th>Time Since</th></tr>\n";
foreach ($data as $key => $row)  {
    echo "<tr>\n";
    echo "<td>" . $key . "</td><td>" . $row["count"] . "</td><td>" . $row["lastplayed"] . "</td><td>" . $row['timeago'] . "</td>\n";
    echo "</tr>\n";
}
echo "</table>";


function get_data($url) {  
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  
  return $data;
}

function parse($data) {    
    $retArray = array();
    $songs = 0;
    $uniqueSongs = 0;
    $timeHold = "";
    
    date_default_timezone_set('Canada/Saskatchewan');     
    $now = strtotime("now");   

    $totalSongs = ( substr_count($data, "<ul>") - 1 );    
    $artist = "";
    
    $artists = array();
    
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $data) as $line){
        if (strstr($line,"<ul>")) {
            $songs++;
            $uniqueSongs++;
            $timeHold = "";
        } else if (strstr($line,"list-time")) {
            preg_match("'<li.*>(.*?)</li>'si",$line, $tmp);
            $timeHold = $tmp[1];
        } else if (strstr($line,"list-title")) {
            //tmp[1] = song. tmp[2] = artist
            preg_match("'<li.*>(.*?)\-(.*?)</li>'si",$line, $tmp);
            
            if (empty($tmp[2])) {
                $songs--;
                $uniqueSongs--;
                continue;
            }
            
            $artist_song = $tmp[2] . " - " . $tmp[1];
            
            //Insert Artist
            if (array_key_exists($tmp[2],$artists)) {                
                $val = $artists[$tmp[2]]['count'];
                $val = $val + 1;
                $artists[$tmp[2]]['count'] = $val;
            } else {
                $artists[$tmp[2]]['count'] = 1;
            }
            
            //Insert return array
            if (array_key_exists($tmp[2],$retArray)) {               
               $val = $retArray[$artist_song]["count"];
               $val = $val + 1;
               $retArray[$artist_song]["count"] = $val;
               $uniqueSongs--;
           } else {
               $retArray[$artist_song]["count"] = 1;
               $retArray[$artist_song]['lastplayed'] = $timeHold;
               $retArray[$artist_song]['timeago'] = time_since($now - getTime($timeHold));
               $artists[$tmp[2]]['lastplayed'] = $timeHold;
           }
        }
    } 
    
    arsort($retArray);
    arsort($artists);
    
    echo "Total Songs: " . $songs . "<br />";
    echo "Total unique songs: " . $uniqueSongs . "<br />";
    echo "Total unique artists: " . count($artists) . "<br />";
    
    $artistOverplay = array_keys($artists);  
    $timePlayed = getTime($artists[$artistOverplay[0]]['lastplayed']);
    $difference = $now - $timePlayed;
    $timeArray = time_since($difference);
    
    //echo var_dump($artists);
    
    echo "Most overplayed artist: <span class=\"unique\">" . $artistOverplay[0] . "</span> with <span class=\"unique\">" . $artists[$artistOverplay[0]]['count'] . "</span> plays.<br />" .
         "\n<span class=\"unique\">" . $artistOverplay[0] . "</span> was last heard at: ". $artists[$artistOverplay[0]]["lastplayed"] . ", (" . $timeArray . " ago)<br />";
         
    return $retArray;
}

function time_since($since) {
    
    if ($since < 0) {
        return "Yesterday";
    }
    
    $chunks = array(
        array(60 * 60 * 24 * 365 , 'year'),
        array(60 * 60 * 24 * 30 , 'month'),
        array(60 * 60 * 24 * 7, 'week'),
        array(60 * 60 * 24 , 'day'),
        array(60 * 60 , 'hour'),
        array(60 , 'minute'),
        array(1 , 'second')
    );

    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];
        if (($count = floor($since / $seconds)) != 0) {
            break;
        }
    }

    $print = ($count == 1) ? '1 '.$name : "$count {$name}s";
    return $print;
}

function sortFunction($a,$b) {
    if ($a["count"] == $b["count"]) {
        return 0;
    }
    return ($a["count"] < $b["count"]) ? -1 : 1;
   
}

function getTime($value) {
    $modified = str_replace("A"," A",$value);
    $modified = trim(str_replace("P"," P",$value));
    $modified = preg_replace("/[^AMP0-9:]/","",$modified);
    
    return strtotime($modified);
}


?>