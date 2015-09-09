<?php

$id = 0;
$url = "";

if (isset($_GET['name']) && !empty($_GET['name'])) {
    $id = $_GET['name'];
}

if ($id == "1") {
    $url = "https://samweb.spacialaudio.com/webapi/station/69408/history?token=3e6d3effbe031d1d6324b7c2775e22d08c5e37cb&top=1000&format=json";
} else {
    echo "BAD CODE PROVIDED<br />\n";
    return;
}

$user_timezone = 'Canada/Saskatchewan';
date_default_timezone_set('Canada/Saskatchewan');  

$raw = get_data($url);
$json = json_decode($raw);
$data = parse($json);

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
    $artists = array();
    
    $songs = 0;
    $uniqueSongs = 0;
    $timeHold = "";
   
    $now = strtotime("now");   

    $totalSongs = count($data);
    $artist = "";
    
    foreach($data as $val) {
        $artist = $val->Artist;
        
        if ($artist === 'QCIndie.com') continue;
        
        $songs++;
        $uniqueSongs++;
        $timeHold = "";
        
        $song = $val->Title;
        $artist_song = $artist . " - " . $song;
        
        if (array_key_exists($artist,$artists)) {
            $artists[$artist]['count'] = $artists[$artist]['count'] + 1;
        } else {
            $artists[$artist]['count'] = 1;
        }
        
        $artists[$artist]['lastplayed'] = parseJsonDate($val->DatePlayed)->format('g:iA');
        
        if (array_key_exists($artist_song,$retArray)) {
            $retArray[$artist_song]['count'] = $retArray[$artist_song]['count'] + 1;
            $uniqueSongs--;
        } else {
            $retArray[$artist_song]['count'] = 1;
            $retArray[$artist_song]['lastplayed'] = parseJsonDate($val->DatePlayed)->format('g:iA'); //TODO: Fix this - Json Date to PHP Date
            $retArray[$artist_song]['timeago'] = time_since($now - getTime($retArray[$artist_song]['lastplayed']));
        }
        
    }
    
    arsort($artists);
    
    echo "Total Songs: " . $songs . "<br />";
    echo "Total unique songs: " . $uniqueSongs . " (" . sprintf("%d",$uniqueSongs / $songs * 100) . "%)<br />";
    echo "Total unique artists: " . sizeof($artists) . "<br />";
    
    $artistOverplay = array_keys($artists);  
    $timePlayed = getTime($artists[$artistOverplay[0]]['lastplayed']);
    $difference = $now - $timePlayed;
    $timeArray = time_since($difference);
    
    if ($artists[$artistOverplay[0]]['count'] === 1) {
        echo "Yay! This station doesn't repeat its music too much!";
    } else {
        arsort($retArray);
        echo "Most overplayed artist: <span class=\"unique\">" . $artistOverplay[0] . "</span> with <span class=\"unique\">" . $artists[$artistOverplay[0]]['count'] . "</span> plays.<br />";
        echo "\n<span class=\"unique\">" . $artistOverplay[0] . "</span> was last heard at: ". $artists[$artistOverplay[0]]["lastplayed"] . ", (" . $timeArray . " ago)<br />";
    }
         
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

function parseJsonDate($date) {
    $string = (string)$date;
    
    $pattern = '/^\/Date\((\d{10})(\d{3})([+-]\d{4})\)\/$/';
    $format  = 'U.u.O';
    $mask    = '%2$s.%3$s.%4$s';

    $r = preg_match($pattern, $string, $matches);
    if (!$r) {
        throw new UnexpectedValueException('Preg Regex Pattern failed.');
    }
    $buffer = vsprintf($mask, $matches);
    $result = DateTime::createFromFormat($format, $buffer);
  
    if (!$result) {
        throw new UnexpectedValueException(sprintf('Failed To Create from Format "%s" for "%s".', $format, $buffer));
    }
    
    date_timezone_set($result,timezone_open('Canada/Saskatchewan'));
    
    return $result;
}


?>
