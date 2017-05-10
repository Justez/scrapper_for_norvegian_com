<html>
  <head>
    <meta charset="UTF-8">
    <title>getEffectiveFinalDetails</title>
  </head>
  <body>

  <?php
    $dep='OSL';$arr='RIX'; 
    $day=1; $month=5; $adults=1; $return=false;
    $direct=true;
    //$direct=false; - gets all flights    
    $month=monthyear($month);
    include('simple_html_dom.php');//http://simplehtmldom.sourceforge.net/manual.htm
    $html = new simple_html_dom();
    //connect to db
    $servername = "localhost";
    $username = "smart_think";
    $password = "think.smarter.lt";
    $dbname = "smarter_lt_wordpress_e";
    // Create connection
    $conn = new mysqli($servername, $username, $password,$dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    //echo "Connected successfully";
    //$conn->close();
    //table created:
    //$sql="CREATE TABLE flights (
    //id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    //departure_airport VARCHAR(5) NOT NULL,
    //arrival_airport VARCHAR(5) NOT NULL,
    //connection_airport VARCHAR(15) DEFAULT NULL,
    //departure_time VARCHAR(15) NOT NULL,
    //arrival_time VARCHAR(15) NOT NULL,
    //cheapest_price FLOAT(10) NOT NULL,
    //taxes FLOAT(10) DEFAULT NULL,
    //date date DEFAULT NULL,
    //reg_date TIMESTAMP)";
    //if ($conn->query($sql) === TRUE) {
    //    echo "Table created successfully";
    //} else {
    //    echo "Error creating table: " . $conn->error;
    //}    
    $d=cal_days_in_month(CAL_GREGORIAN,$month%100,($month-$month%100)/100);
    for($i=1;$i<=$d; $i++){ 
        echo '<br/>day '.$i.'<br/><br/>';
        //function getUrlWithResults($day) that autofill form with on the site 'https://www.norwegian.com/en/booking/flight-tickets/select-flight/', a small start is in 6.php
        //should get an URL from the function but the URL is generated using a workaround
        if ($direct){//can generate URL for flights with stops too
            $page='https://www.norwegian.com/en/booking/flight-tickets/select-flight/'.'?D_City='.$dep.'&A_City='.$arr.'&TripType=1&D_Day='.dayFix($i).'&D_Month='.$month.'&D_SelectedDay='.dayFix($i).'&R_Day='.dayFix($i).'&R_Month='.$month.'&R_SelectedDay='.dayFix($i).'&IncludeTransit=false&AgreementCodeFK=-1&CurrencyCode=EUR';
            }else {
                $page='https://www.norwegian.com/en/booking/flight-tickets/select-flight/'.'?D_City='.$dep.'&A_City='.$arr.'&TripType=1&D_Day='.dayFix($i).'&D_Month='.$month.'&D_SelectedDay='.dayFix($i).'&R_Day='.dayFix($i).'&R_Month='.$month.'&R_SelectedDay='.dayFix($i).'&AgreementCodeFK=-1&CurrencyCode=EUR';}
        $html->load_file($page);        
        foreach($html->find('.rowinfo1') as $rowinfo1){
            $item['deptime']     = $rowinfo1->find('.depdest', 0)->plaintext;
            $item['arrtime']     = $rowinfo1->find('.arrdest', 0)->plaintext;
            //the first price is the cheapest one
            $item['price']         = $rowinfo1->find('.seatsokfare', 0)->plaintext;
            //should check the price to get taxes, the sidebar information is not received using GET method, after checking the box.
            $item['dirrect']     = $rowinfo1->find('.duration',0)->plaintext;
        $rowinfo1array[] = $item;}
        foreach($html->find('.rowinfo2') as $rowinfo2){
            $item2['depdest']     = $rowinfo2->find('.depdest', 0)->plaintext;
            $item2['arrdest']    = $rowinfo2->find('.arrdest', 0)->plaintext;
            $rowinfo2array[] = $item2;}
        //merge arrays
        for($j=0;$j<count($rowinfo1array); $j++){
            $rowinfo1array[$j]['depdest']=$rowinfo2array[$j]['depdest'];
            $rowinfo1array[$j]['arrdest']=$rowinfo2array[$j]['arrdest'];}
        //write array to table
        if (count($rowinfo1array)>0):    ?>
    <table>
     <tr>
       <td>Departure airport</td>
       <td>Arrival airport</td>
       <td>Connection airport</td>
       <td>Departure time</td>
       <td>Arrival time</td>
       <td>Cheapest price and taxes</td>
     </tr>
     <?
            foreach ($rowinfo1array as $row) : ?>
     <tr>
       <td><? echo $dep; ?></td>
       <td><? echo $arr; ?></td>
       <td><? echo $row['dirrect'];?></td>
       <td><? echo $row['deptime']; ?></td>       
       <td><? echo $row['arrtime']; ?></td>
       <td><? echo $row['price']; ?></td>
     </tr>
     <?     endforeach; ?>
   </table>
        <?
        //write array to DB 
        
            $datestring = strval(($month-$month%100)/100).'-'.strval(dayfix($month%100)).'-'.dayFix($i);
            foreach ($rowinfo1array as $row){
            $sql = "INSERT INTO flights (id ,departure_airport, arrival_airport, connection_airport, departure_time, arrival_time, cheapest_price, taxes, date, reg_date)
            VALUES (NULL, '{$dep}', '{$arr}', '{$row['dirrect']}','{$row['deptime']}','{$row['arrtime']}',{$row['price']},0,'{$datestring}',CURRENT_TIMESTAMP);";
            if ($conn->query($sql) === TRUE) {
                //echo "New record created successfully";
                } else {
                echo "Error: " . $sql . "<br>" . $conn->error;}} 
        endif; 
        $rowinfo1array=array();$rowinfo2array=array();$item=array();$item2=array();}
    $conn->close();
    //helping functions    
    function dayFix($day){ //adds '0' to numbers below 10, returns string
        if ($day<10) {
            return '0'.strval($day);
        } else {
        return strval($day);}    
    }
    function monthyear($month){ //returns int year+month
    if ($month<date(m)+1) {
        $month=$month+201800; } else {
        $month=$month+201700;}
    return $month;}
    ?>
  </body>
</html>
