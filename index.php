
<html>
    <head>
        <title>Search Places</title>
        <!-- CSS only -->
        <link rel="stylesheet" href="./assets/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>

    </head>
    <body >
    <div id="loader">
      <img width="100" src="./assets/search.svg" alt="laoder">
    </div>

    
    <form id="myForm" class="form-wrapper text-center" action=""  method="post">
        <h4>Search Google Places</h4>
        <input type="text" id="search" name="search" placeholder="Search for..." required>
        <input  type="submit" value="Search" id="submit" name="submit">
</form>
<div class="container form-wrapper">
  
  <input type="submit" name="export"  value="Export to Excel" id="exportExcel" class="p-2" onclick="ExportToExcel('xlsx')">
  

<table id="tbl_exporttable_to_xls" class="table table-hover tblExport">
  <p class="m-2 d-inline">You Search for: <h5  class="searchValue d-inline text-capitalize"><?php echo (isset($_POST['submit'])?$_POST['search']:"");?></h5></p>
  <thead class="thead-dark">
    <tr>
    <th scope="col">Sr #</th>
      <th scope="col">Place Id</th>
      <th scope="col">Name</th>
      <th scope="col">Address</th>
      <th scope="col">Lat</th>
      <th scope="col">Long</th>
      <th scope="col">image_main</th>
      <!-- <th scope="col">zipcode</th> -->
      <th scope="col">Country</th>
      
    </tr>
  </thead>
  <tbody>
  <?php
      if(isset($_POST['submit'])){
      $data;
    function getAllReferences($query, $maxResults= 1000, $nextToken = false) {
    $references = array();
    $nextStr = "";
    if ($nextToken)
        $nextStr = "pagetoken=$nextToken";
    $placeSearchURL = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=$query&key=AIzaSyBkUHO1bsn4wDC93ZRcSFgQ58anbl09uSw&$nextStr";
    $placeSearchJSON = file_get_contents($placeSearchURL);
    // echo '<script>';
    //     echo 'console.log('.$placeSearchJSON.')';
    //     echo '</script>';
    $dataArray = json_decode($placeSearchJSON);
    if (isset($dataArray->status) &&$dataArray->status == "OK") {
        foreach( $dataArray->results as $details) {
            array_push($references, $details);
        }
        if (!empty($dataArray->next_page_token) && count($references) < $maxResults ) {
            sleep(2);
            $nextArray = getAllReferences($query, $maxResults-20 , $dataArray->next_page_token);
            $references = array_merge($references, $nextArray);

        }
        // echo '<script>';
        // echo 'console.log('. json_encode( $references ) .')';
        // echo '</script>';
        return $references;
    }
   
}

function getPhoto($photo)
{
    // get Photo
    if($photo){
      $getImage = file_get_contents("https://maps.googleapis.com/maps/api/place/photo?maxwidth=130&photoreference=$photo&key=AIzaSyBkUHO1bsn4wDC93ZRcSFgQ58anbl09uSw");
      return $image = base64_encode($getImage);  
    }
}

function getZipcode($address)
{
    // get geocode
    $geocode = file_get_contents("https://maps.google.com/maps/api/geocode/json?address=$address&key=AIzaSyBkUHO1bsn4wDC93ZRcSFgQ58anbl09uSw");
    $json = json_decode($geocode);
   
    $latitude = $json->results[0]->geometry->location->lat;
    $longitude = $json->results[0]->geometry->location->lng;
    
    // get zipcode
    $geocode = file_get_contents("https://maps.google.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=AIzaSyBkUHO1bsn4wDC93ZRcSFgQ58anbl09uSw");
    $json = json_decode($geocode);
    
    foreach($json->results[0]->address_components as $adr_node) {
        if($adr_node->types[0] == 'postal_code') {
            return $adr_node->long_name;
        }
    }
    return false;
}

function extract_zipcode($address, $remove_statecode = false) {
  $zipcode = preg_match("/\b[A-Z]{2}\s+\d{5}(-\d{4})?\b/", $address, $matches);
  return $remove_statecode ? preg_replace("/[^\d\-]/", "", extract_zipcode($matches[0])) : $matches[0];
}

         
          $data=getAllReferences(urlencode($_POST['search']));
          $counter=0;
          if(is_array($data) || is_object($data)){
          foreach ($data as $key=>$value){
            // $zip=getZipcode(urlencode($value->formatted_address));
            $img=getPhoto(urlencode((isset($value->photos)?$value->photos[0]->photo_reference:'')));

          $last_word_start = explode(',', (isset($value->formatted_address)?$value->formatted_address:''));
          $last_word = end($last_word_start);
            $counter++;
              echo '<tr>';
              echo '<td>'. $counter.'</td>';
              echo '<td>'. (isset($value->place_id)?$value->place_id:'') .'</td>';
              echo '<td>'. (isset($value->name)?$value->name:'') .'</td>';
              echo '<td>'. (isset($value->formatted_address)?$value->formatted_address:'') .'</td>';
              echo '<td>'. (isset($value->geometry->location->lat)?$value->geometry->location->lat:'') .'</td>';
              echo '<td>'. (isset($value->geometry->location->lng)?$value->geometry->location->lng:'') .'</td>';
              // echo '<td><img src="'. (isset($value->icon)?$value->icon:'') .'" alt="icon"/><a class="d-none" href="#">'. (isset($value->icon)?$value->icon:'') .'</a></td>';

              echo '<td><img src="data:image/png;base64,'. (isset($img)?$img:'') .'" alt="icon"/><a class="d-none" href="#">data:image/png;base64,'. (isset($img)?$img:'') .'</a></td>';              // echo '<td>'. (isset($value->photos)?$value->photos[0]->html_attributions[0]:'') .'</td>';
              // echo '<td>'. (isset($value->formatted_address)?$zip:'') .'</td>';
              // echo '<td>'. (isset($value->plus_code->compound_code)?substr(strstr($value->plus_code->compound_code," "), 1):'') .'</td>';
              echo '<td>'. (isset($value->plus_code->compound_code)?$last_word:'') .'</td>';
            echo '</tr>'; 
          };   
          
        };    
  
    
 
}  
    
?>


<script>

$(window).ready(function() {      //Do the code in the {}s when the window has loaded 
  $("#loader").fadeOut();  //Fade out the #loader div
});
var serch = document.querySelector(".searchValue").innerHTML

if(serch){function ExportToExcel(type, fn, dl) {
  var currentdate = new Date(); 
  var serch = document.querySelector(".searchValue").innerHTML
  var datetime = "Last Sync: " + currentdate.getDate() + "/"
                + (currentdate.getMonth()+1)  + "/" 
                + currentdate.getFullYear() + " @ "  
                + currentdate.getHours() + ":"  
                + currentdate.getMinutes() + ":" 
                + currentdate.getSeconds();
       var elt = document.getElementById('tbl_exporttable_to_xls');
       var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });
       return dl ?
         XLSX.write(wb, { bookType: type, type: 'base64' }):
         XLSX.writeFile(wb, fn || (`${serch.replaceAll(' ','-')}-${datetime}.` + (type || 'xlsx')));
    }
    
  }
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


  </tbody>
 
</table>
    <div>

   
    </body>
</html>