<!doctype html> 
<head>
<title>Scrape the Radio</title>
<link rel="stylesheet" href="./StyleSheets/basic.css" />
<script LANGUAGE="JavaScript">

function scrape (form) {

    var id = form.station_id.value;
    var name = form.station_name.value;
    var url = "scraper.php?name=" + name + "&id=" + id;
    var html = "ID = " + id + " Name = " + name;
    
    document.getElementById('list').innerHTML = "<i>Fetching...</i>";
    document.getElementById('list').removeAttribute("style",0);
        
    MakeRequest(url);
}

function newScrape(stationID) {
    var url = "scraper2.php?name=" + stationID;
    document.getElementById('list').innerHTML = "<i>Fetching...</i>";
    document.getElementById('list').removeAttribute("style",0);
    MakeRequest(url);
}

function forceScrape(buttonID) {
    var form = document.getElementById('selection');
    var type = 1;
    var type_station = 0;
    
    if (buttonID === 'theWolf') {
        form.station_id.value = "5485";
        form.station_name.value = "CFWF";
    } else if (buttonID === 'z99') {
        form.station_id.value = "5484";
        form.station_name.value = "CIZL";
    } else if (buttonID === 'lite92') {
        type = 2;
        type_station = 1;
    } else if (buttonID === 'CKRM') {
        form.station_id.value = "5481";
        form.station_name.value = "CKRM";
    } else if (buttonID === 'C95') {
        form.station_id.value = "5492";
        form.station_name.value = "CFMC";
    } else if (buttonID === 'magic') {
        form.station_id.value = "6216";
        form.station_name.value = "CJMK";
    } else if (buttonID === 'cjww') {
        form.station_id.value = "5491";
        form.station_name.value = "CJWW";
    } else if (buttonID === 'bull') {
        form.station_id.value = "5490";
        form.station_name.value = "CFQC";
    } else if (buttonID == 'lite957') {
        type = 2;
        type_station = 2;
    } else if (buttonID == 'x929') {
        type = 2;
        type_station = 3;
    }
    else {
        form.station_id.value="43211234";
        form.station_name.value="FAKE";
    }

    if (type === 1) {
        scrape(form);
    } else {
        newScrape(type_station);
    }
}

function ajaxUpdate(html) {
    document.getElementById('list').innerHTML = html;    
}

function getXMLHttp() {
  var xmlHttp

  try {
    //Firefox, Opera 8.0+, Safari
    xmlHttp = new XMLHttpRequest();
  } catch(e) {
    //Internet Explorer
    try {
      xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch(e) {
      try {
        xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
      } catch(e) {
        alert("Your browser does not support AJAX!")
        return false;
      }
    }
  }
  return xmlHttp;
}

function MakeRequest(url) {
  var xmlHttp = getXMLHttp();

  xmlHttp.onreadystatechange = function()  {
    if(xmlHttp.readyState == 4)
    {
      ajaxUpdate(xmlHttp.responseText);
    }
  }

  xmlHttp.open("GET", url, true); 
  xmlHttp.send(null);
}

</script>
</head>
<body>
<div class="title_box">
    <h2>The Radio Sucks</h2>
    <p><i>And I have proof!</i></p>
    <hr />
  <form name="Selection" action="" method="GET" id="selection">
    <input type="hidden" name="station_id" value="" />
    <input type="hidden" name="station_name" value="" />        
    <p>Stations to pick from</p>
    <p><b>Regina</b></p>
    <input type="button" name="WOLF" value="The Wolf" onClick="forceScrape('theWolf')" />
    <input type="button" name="Z99" value="Z99" onClick="forceScrape('z99')" />
    <input type="button" name="lite92" value="Lite 92.1" onClick="forceScrape('lite92')" />
    <input type="button" name="CKRM" value="620 CKRM" onClick="forceScrape('CKRM')" />
    <p><b>Saskatoon</b></p>
    <input type="button" name="C95" value="C95" onClick="forceScrape('C95')" />
    <input type="button" name="Magic983" value="Magic 98.3" onClick="forceScrape('magic')" />
    <input type="button" name="cjww" value="CJWW 98.3" onClick="forceScrape('cjww')" />
    <input type="button" name="bull" value="The Bull 92.9" onClick="forceScrape('bull')" />
    <p><b>Edmonton</b></p>
    <input type="button" name="lite957" value="Lite 95.7" onClick="forceScrape('lite957')" />
    <p><b>Calgary</b></p>
    <input type="button" name="x929" value="X92.9" onClick="forceScrape('x929')" />    
  </form>
  <hr />
  <div>
  <p style="font-size: 10px;"> 
  This is a scrape of radio song data made available by the host sites who use the <a href="bdsrealtime.com"><i>Nielsen BDS</i></a> service. 
  Song lists are typically a compilation of the last 12 hours. Any songs that aren't correct are usually the radio station reporting weird segments
  </p></div>
</div>

<div id="list" class="results" style="visibility: hidden;">
</div>
</body>
</html>