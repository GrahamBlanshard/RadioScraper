<?php
$urlBase = "http://websvc.bdsrealtime.com/nbds.consumer.tempo/npgenlog.aspx";
$stationNameBase = "?uid=Service";
$stationIDBase = "&stnid=";

$id = $_GET['id'];
$name = $_GET['name'];
$songList = array();

if ( !is_numeric( $id ) ) {
    return array("FAILED" => "INVALID ID");
}

if ( strlen($name) > 4 ) {
    return array("FAILED" => "INVALID NAME");
}

$stationName = $stationNameBase . $name;
$stationID = $stationIDBase . $id;

$url = $urlBase . $stationName . $stationID;

echo "<table id=\"resultstbl\">\n";
echo " <colgroup><col class=\"name\" /><col class=\"count\" /><col class=\"last_played\" /><col class=\"time_since\" /></colgroup>\n";
echo "<tr>\n<th>Song/Artist</th><th>Played Count</th><th>Last Played</th><th>Time Since</th></tr>\n";

$songList = get_data($url);

foreach ($songList as $key => $row)  {
    echo "<tr>\n";
    echo "<td>" . $key . "</td><td>" . $row["count"] . "</td><td>" . $row["lastplayed"] . "</td><td>" . $row['timeago'] . "</td>\n";
    echo "</tr>\n";
}

//echo var_dump($songList);
//Lite92.1 List: http://my921.ca/data/bds-list.html == <site>/data/bds-list.html

echo "</table>\n";


function get_data($url) {  
  
  $ch = curl_init();
  echo "HELLO";
  $timeout = 5;
  echo "HELLO";
  curl_setopt($ch, CURLOPT_URL, $url);
  echo "HELLO";
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  echo "HELLO";
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  echo "HELLO";
  $data = curl_exec($ch);
  echo "HELLO";
  curl_close($ch);
  
  return parse($data);
}


function parse($data) {    
    $retArray = array();
    $isArtist = 0;
    $songs = 0;
    $uniqueSongs = 0;
    
    date_default_timezone_set('Canada/Saskatchewan');     
    $now = strtotime("now");   
    
    
    $data = substr($data, strpos($data,"<table "), strpos($data,"</table>") - strpos($data,"<table") );
    $data = substr($data, strpos($data,"<tr>")+4);
    $data = substr($data, strpos($data,"<tr>")+4);
    
    $totalSongs = ( substr_count($data, "<tr>") - 1 );    
    $artist = "";
    $artistHold = "";
    
    $artists = array();
    
    for ($i = 0; $i < $totalSongs; $i++) {
        if ($isArtist < 2) {
            $tmp = "";
            preg_match('/\<span.*\>?\<\/span\>/',$data, $tmp);
            $tmp[0] = substr($tmp[0],strpos($tmp[0],">")+1,strpos($tmp[0],"</"));
            if ($isArtist == 0) {
                $artist = $tmp[0]; 
            } else {
                preg_match('/\<span.*\>?\<\/span\>/',$data, $tmp,0,100);
                $tmp[0] = substr($tmp[0],strpos($tmp[0],">")+1,strpos($tmp[0],"</"));
                $artist = $tmp[0] . " - " . $artist;
                
                //echo var_dump($retArray) . "<br /><br />";
                
                if (array_key_exists($tmp[0],$artists)) {                
                    $val = $artists[$tmp[0]]['count'];
                    $val = $val + 1;
                    $artists[$tmp[0]]['count'] = $val;
                } else {
                    $artists[$tmp[0]]['count'] = 1;
                    $artistHold = $tmp[0];
                }
                
            }
            
            $isArtist++;            
        } else if ($isArtist == 2) {
            $tmp = "";
            preg_match('/\<span.*\>?\<\/span\>/',$data, $tmp,0,100);
            $tmp[0] = substr($tmp[0],strpos($tmp[0],">")+1,strpos($tmp[0],"</"));
            
            $songs++;
            $uniqueSongs++;
           if (array_key_exists($artist,$retArray)) {               
               $val = $retArray[$artist]["count"];
               $val = $val + 1;
               $retArray[$artist]["count"] = $val;
               $uniqueSongs--;
           } else {
               $retArray[$artist]["count"] = 1;
               $retArray[$artist]['lastplayed'] = $tmp[0];
               $retArray[$artist]['timeago'] = time_since($now - getTime($tmp[0]));
               $artists[$artistHold]['lastplayed'] = $tmp[0];
           }
           
           
           $artist="";        
           $isArtist = 0;
           
        } else {
            $data = substr($data, strpos($data,"<tr>")+4);            
        }
        $data = substr($data, strpos($data,"<tr>")+4);            
    }
    
    arsort($retArray);
    arsort($artists);
    
    echo "Total Songs: " . $songs . "<br />";
    echo "Total unique songs: " . $uniqueSongs . "<br />";
    echo "Total unique artists: " . count($artists) . "<br />";
    
    $artistOverplay = array_keys($artists);  
    /*
    $modified = str_replace("A"," A",$retArray[$artistOverplay[0]]["lastplayed"]);
    $modified = trim(str_replace("P"," P",$retArray[$artistOverplay[0]]["lastplayed"]));
    $modified = preg_replace("/[^AMP0-9:]/","",$modified);
    */
    $timePlayed = getTime($artists[$artistOverplay[0]]['lastplayed']);
    $difference = $now - $timePlayed;
    $timeArray = time_since($difference);
    
    
    echo "Most overplayed artist: <span class=\"unique\">" . $artistOverplay[0] . "</span> with <span class=\"unique\">" . $artists[$artistOverplay[0]]['count'] . "</span> plays.<br />" .
         "\n<span class=\"unique\">" . $artistOverplay[0] . "</span> was last heard at: ". $artists[$artistOverplay[0]]["lastplayed"] . ", (" . $timeArray . " ago)<br />";
    //arsort($retArray);
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