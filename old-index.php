if (strpos($ussd_string, '8022*') === false) {
    $fixture="";
    $response="";
    session_appender($msisdn, $conn, $fixture, $response, $ussd_string);
}
    
if ($ussd_string == '8022'){
    $sub_checker = 1;
  
if($sub_checker == 2){
    $options="";
    $response="Hello you are not subscribed to this service. kindly dial 12345 to subscribe. Thank you.";
    $type="false";
    $fixture="";
    $message_type = 2;
    //clear_ussd_queue($conn,$msisdn);
}
else if($sub_checker == 1){
    //$checker = session_checker($msisdn,$conn);
    $options="";
    $response="Win Millions of Cash Daily!\n
    1. Predict and Win N1M Everyday 
    2. Soka Games
    3. More Games";
    $type="true";
    $fixture="";
    $message_type = 1; 
 
    //session_updater($msisdn,$conn,$fixture,$response,$ussd_string);
} 

    clear_ussd_queue($conn,$msisdn);
    echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);  
} 


if (strpos($ussd_string, '8022') !== false) {
    $fixture="";
    $response="";
    clear_ussd_queue($conn,$msisdn);
    session_updater($msisdn,$conn,$fixture,$response,$ussd_string);
    $checker=session_checker($msisdn,$conn); 

    if ($ussd_string=='8022*1'){
      //$checker=session_checker($msisdn,$conn);
      $options="\n1.press 1 to Place a bet";
      $response="Y’ello! Welcome to SOKA 4. Predict outcome of 4 matches to Win 1,000,000 daily! Subscription of 100/day \n\n".$options;
      $type="true";
      $message_type=1;
      $fixture="";
      session_updater($msisdn,$conn,$fixture,$response,$ussd_string);
      echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);    
    }

    if ($ussd_string=='8022*2'){
      //$checker=session_checker($msisdn,$conn);
      $options="\n1.press 1 to Place a bet";
      $response="Y’ello! Welcome to SOKA 6. Predict outcome of 6 matches to Win 1,000,000 daily! Subscription 100/day \n\n".$options;
      $message_type=1;
      $type="true";
      $fixture="";
      // session_updater($msisdn,$conn,$fixture,$response,$ussd_string);
      echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);  
    }

    if ($ussd_string=='8022*3'){
      //$checker=session_checker($msisdn,$conn);
      $options="\n1.press 1 to Place a bet";
      $response="Y’ello! Welcome to SOKA 8. Predict outcome of 8 matches to Win 1,000,000 daily! Subscription 100/day \n\n".$options;
      $type="true";
      $message_type=1;
      $fixture="";
      // session_updater($msisdn,$conn,$fixture,$response,$ussd_string);
      echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);   
}

if ($ussd_string=='8022*4'){
      //$checker=session_checker($msisdn,$conn);
      $options="\n1.press 1 to Place a bet";
      $response="Y’ello! Welcome to SOKA Half predictor. Predict 1st & 2nd half result of 4 matches to Win 1,000,000 daily! Subscription 100/day \n\n".$options;
      $type="true";
      $message_type=1;
      $fixture="";
      // session_updater($msisdn,$conn,$fixture,$response,$ussd_string);
      echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);   
}

if ($ussd_string=='8022*5'){
      //$checker=session_checker($msisdn,$conn);
      $options="\n1.press 1 to Place a bet";
      $response="Y’ello! Welcome to Corners Prediction. Predict total corners in 6 matches to Win 1,000,000 daily! Subscription 100/day \n\n".$options;
      $type="true";
      $fixture="";
      $message_type=1;
      // session_updater($msisdn,$conn,$fixture,$response,$ussd_string);
      echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);   
}
}

$checker = session_checker($msisdn,$conn);


//For *8022 ussd transactions
if($ussd_string==1 && $checker== '80221') { 
      $response="Choose Game:
      1. Predictor - N1M 
      2. Total Goals - N1M
      3. Correct Score - N1M";
      $type="true";
      $message_type = 1;
      echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);
 } 
 
  if( $ussd_string==1 && $checker=='802211'){
      $response="Welcome to Predictor\n
      Pick match outcomes for 6 matches to win N1,000,000 \n\n Select 1";
      $type="false";
      $message_type=1;
      $psipid='5706';
      echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);
      //subscription($token,$msisdn,$psipid);
  }
  
  if( (str_contains($checker, '8022111'))){
      if(counter($checker)>6  && $ussd_string<4){ 
      //$matches = array("Match 1: Chelsea vs Man City ", "Match 2: Manchester Utd vs Liverpool", "Match 3: Arsenal vs Stoke City","Your bet has been placed successfully.");
      $matches=select_fixtures_pdo();
      $pick=strlen($checker)-7;
      $match_no=$pick+1;
      $response="Match $match_no :\n" .$matches[$pick].' ';
      $type="true";
      $message_type=1;
      $fixture_select='Selected '.$ussd_string;
  
      fixture_appender($msisdn,$conn,$response,$checker);
      fixture__select_appender($msisdn,$conn,$fixture_select,$checker);
  
      if(counter($checker)==12  && $ussd_string<4){
        fixture__select_appender($msisdn,$conn,$fixture_select,$checker);
        $response="";
        //$psisid="282";
        $options="\n  Bet placed succesfully. You will receive your bet ticket shortly via SMS.";
        $type="false";
        $message_type=2;
        $psisid="282";
        $ticket_sms=generate_ticket($conn,$msisdn);
            
        $new_message=str_replace(["\n", "\r"], '', $ticket_sms);
        send_sms($token,$msisdn,$new_message,$psisid);
  }
  else
  {
        $options="\n1. Home Win \n2. Draw \n3. Away Win";
  }
 }
  if(counter($checker)>6  && $ussd_string>4){ 

 $response="Invalid option selected ";
 $options="Please try again later.";
 $type="true";
    $message_type=1;
  
 }
 
 
 

 //$type="false";
 
 
 echo ussd_formatter($msisdn,$response.$options,$serviceCode,$type,$sessionId,$message_type);
  }
  
  
  
  
  /// For Total Goals///
  
  
  if( $ussd_string==2 && $checker=='802212'){
    $response="Welcome to Total
Goals Enter total goals scored in each match to win N1,000,000 \n\n Select 1";
    $type="false";
    $message_type=1;
    $psipid='5706';
 echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);
   //subscription($token,$msisdn,$psipid);
  }
    if( (str_contains($checker, '8022121'))){
        
        

 if(counter($checker)>6  && $ussd_string<4){ 
     
  //  $matches = array("Match 1: Chelsea vs Man City ", "Match 2: Manchester Utd vs Liverpool", "Match 3: Arsenal vs Stoke City","Your bet has been placed successfully.");
  $matches=select_fixtures_pdo();
     $pick=strlen($checker)-7;
     $match_no=$pick+1;
  $response="Match $match_no :\n" .$matches[$pick].' ';
  $type="true";
    $message_type=1;
    $fixture_select='Selected '.$ussd_string;
  
  fixture_appender($msisdn,$conn,$response,$checker);
  fixture__select_appender($msisdn,$conn,$fixture_select,$checker);
  
  
  if(counter($checker)==12  && $ussd_string<4){
    fixture__select_appender($msisdn,$conn,$fixture_select,$checker);
   $response="";
   //$psisid="282";
   $options="\n  Bet placed succesfully. You will receive your bet ticket shortly via SMS.";
   $type="false";
   $message_type=2;
   $psisid="282";
            $ticket_sms=generate_ticket($conn,$msisdn);
            
    $new_message=str_replace(["\n", "\r"], '', $ticket_sms);
    send_sms($token,$msisdn,$new_message,$psisid);

  }
  
  else
  {
      //$options="\n1. Home Win \n2. Draw \n3. Away Win";
      $options="";
  }
 }
  if(counter($checker)>6  && $ussd_string>4){ 

 $response="Invalid option selected ";
 $options="Please try again later.";
 $type="true";
    $message_type=1;
  
 }
 
 
 

 //$type="false";
 
 
 echo ussd_formatter($msisdn,$response.$options,$serviceCode,$type,$sessionId,$message_type);
  }

  if ($ussd_string == 1 && $checker == '80222'){
      $response = "Choose Soka Game:
      1. Soka 4 - N2M 
      2. Soka 6 - N3M
      3. Soka 10 - N10M";

      $type = "true";

      $message_type = 1;

      echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);
  }

  if (str_contains($checker, '802221')){
    //Handle Soka 4
    handle_soka([
      "checker" => $checker,
      "ussd_string" => $ussd_string,
      "name" => "Soka 4",
      "prize" => "2 Million",
      "prize_short" => "2M",
    ], "802221", 4);
  }else if (str_contains($checker, '802222')){
    //Handle Soka 6
    handle_soka([
      "checker" => $checker,
      "ussd_string" => $ussd_string,
      "name" => "Soka 6",
      "prize" => "3 Million",
      "prize_short" => "3M",
    ], "802222", 6);
  }
  else if (str_contains($checker, '802223')){
    //Handle Soka 8
    handle_soka([
      "checker" => $checker,
      "ussd_string" => $ussd_string,
      "name" => "Soka 8",
      "prize" => "10 Million",
      "prize_short" => "10M",
    ], "802223", 8);
  }
  
  
  
  
  
  
  
  ///End Total Goals//
  
  else{
      
      
  }

  
  ///For *8022 2nd ussd transactions

 if($ussd_string==2 && $checker== '80222') { 
     
        $response="Y’ello FC – Soka 6
Predict 6 matches & win N500,000 Daily!
Sub fee: N100/day. 
\n1 Subscribe
\n2 Back";
    $type="true";
    $message_type=1;
 echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);

 }
 
  if( $ussd_string==1 && $checker=='802221'){
    $response="Thank you for Subscribing to Yello FC Soka 6.Please respond to the USSD popup to accept subscription. Dial *8022*2# to play.";
    $type="false";
    $message_type=2;
    $psipid='5707';
 echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);
   subscription($token,$msisdn,$psipid);
  }else{
      
      
  }


  
  ///For *8022 3rd ussd transactions

 if($ussd_string==3 && $checker== '80223') { 
     
        $response="Y’ello FC – Soka 8
Predict 8 matches & win N500,000 Daily!
Sub fee: N100/day. 
\n1 Subscribe
\n2 Back";
    $type="true";
    $message_type=1;
 echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);

 }
 
  if( $ussd_string==1 && $checker=='802231'){
    $response="Thank you for Subscribing to Yello FC Soka 8.Please respond to the USSD popup to accept subscription. Dial *8022*3# to play.";
    $type="false";
    $message_type=2;
    $psipid='5708';
 echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);
   subscription($token,$msisdn,$psipid);
  }else{
      
      
  }


    
  ///For *8022 4th ussd transactions

 if($ussd_string==4 && $checker== '80224') { 
     
        $response="Y’ello FC – Soka Half
Predict 8 matches & win N500,000 Daily!
Sub fee: N100/day. 
\n1 Subscribe
\n2 Back";
    $type="true";
    $message_type=1;
 echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);

 }
 
  if( $ussd_string==1 && $checker=='802241'){
    $response="Thank you for Subscribing to Yello FC Soka Half.Please respond to the USSD popup to accept subscription. Dial *8022*4# to play.";
    $type="false";
    $message_type=2;
    $psipid='5708';
 echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);
   subscription($token,$msisdn,$psipid);
  }else{
      
      
  }
  
  


//for *8022*1 ussd transactions
  
 if(str_contains($checker, '8022*11')) {
 
     //$checker=session_checker($msisdn,$conn);
    
# echo $response=$matches[0];
     //echo strlen($checker);
 if(counter($checker)>6  && $ussd_string<5){ 
     
  //  $matches = array("Match 1: Chelsea vs Man City ", "Match 2: Manchester Utd vs Liverpool", "Match 3: Arsenal vs Stoke City","Your bet has been placed successfully.");
  $matches=select_fixtures_pdo();
     $pick=strlen($checker)-7;
     $match_no=$pick+1;
  $response="Match $match_no :\n" .$matches[$pick].' ';
  $type="true";
    $message_type=1;
    $fixture_select='Selected '.$ussd_string;
  
  fixture_appender($msisdn,$conn,$response,$checker);
  fixture__select_appender($msisdn,$conn,$fixture_select,$checker);
  
  
  if(counter($checker)==10  && $ussd_string<4){
    fixture__select_appender($msisdn,$conn,$fixture_select,$checker);
   $response="";
   //$psisid="282";
   $options="\n  Bet placed succesfully. You will receive your bet ticket shortly via SMS.";
   $type="false";
   $message_type=2;
   $psisid="282";
            $ticket_sms=generate_ticket($conn,$msisdn);
            
    $new_message=str_replace(["\n", "\r"], '', $ticket_sms);
    send_sms($token,$msisdn,$new_message,$psisid);

  }
  
  else
  {
      $options="\n1. Home Win \n2. Draw \n3. Away Win";
  }
 }
  if(counter($checker)>6  && $ussd_string>4){ 

 $response="Invalid option selected ";
 $options="Please try again later.";
 $type="true";
    $message_type=1;
  
 }
 
 
 

 //$type="false";
 
 
 echo ussd_formatter($msisdn,$response.$options,$serviceCode,$type,$sessionId,$message_type);
     
 }
 
 //for *8022*2 ussd transactions
 
  if(str_contains($checker, '8022*21')) {
 
     //$checker=session_checker($msisdn,$conn);
    
# echo $response=$matches[0];
     //echo strlen($checker);
 if(counter($checker)>6  && $ussd_string<7){ 
     
    //$matches = array("Match 1: Chelsea vs Man City ", "Match 2: Manchester Utd vs Liverpool", "Match 3: Arsenal vs Stoke City","Your bet has been placed successfully.");
$matches=select_fixtures_pdo();
     $pick=strlen($checker)-7;
     $match_no=$pick+1;
  $response="Match $match_no :\n" .$matches[$pick];
  $type="true";
    $message_type=1;
    $fixture_select='Selected '.$ussd_string;
  fixture_appender($msisdn,$conn,$response,$checker);
  fixture__select_appender($msisdn,$conn,$fixture_select,$checker);
  if(counter($checker)==13  && $ussd_string<7){
      $response="";
      $psipid='283';
   $options="\n  Bet placed succesfully.  You will receive your bet ticket shortly via SMS. Kindly accept the USSD popup that appears";
  $type="false";
    $message_type=2;
    
       $psisid="283";
       $ticket_sms=generate_ticket($conn,$msisdn);
    $new_message=str_replace(["\n", "\r"], '', $ticket_sms);
    send_sms($token,$msisdn,$new_message,$psisid);
   //Call subscription API 
   //subscription($token,$msisdn);
  }
  
  else
  {
      $options="\n1. Home Win \n2. Draw \n3. Away Win";
  }
 }
  if(counter($checker)>6  && $ussd_string>3){ 

 $response="Invalid option selected ";
 $options="Please try again later.";
  $type="false";
    $message_type=2;
 }
 
 
 

 //$type="false";
 echo ussd_formatter($msisdn,$response.$options,$serviceCode,$type,$sessionId,$message_type);    
     
 }
 
 //for *8022*3 ussd transactions
 
 if(str_contains($checker, '8022*31')) {
 
     //$checker=session_checker($msisdn,$conn);
    
# echo $response=$matches[0];
     //echo strlen($checker);
 if(counter($checker)>6  && $ussd_string<9){ 
     
    //$matches = array("Match 1: Chelsea vs Man City ", "Match 2: Manchester Utd vs Liverpool", "Match 3: Arsenal vs Stoke City","Your bet has been placed successfully.");
$matches=select_fixtures_pdo();
     $pick=strlen($checker)-7;
     $match_no=$pick+1;
  $response="Match $match_no :\n" .$matches[$pick];
   $type="true";
    $message_type=1;
  fixture_appender($msisdn,$conn,$response,$checker);
  
  if(counter($checker)==15  && $ussd_string<9){
      $response="";
$psipid='284';
      $type="true";
    $message_type=1;
   $options="\n  Bet placed succesfully.  You will receive your bet ticket shortly via SMS. Kindly accept the USSD popup that appears";
   //Call subscription API
   subscription($token,$msisdn);
  }
  
  else
  {
      $options="\n1. Home Win \n2. Draw \n3. Away Win";
  }
 }
  if(counter($checker)>8  && $ussd_string>3){ 

 $response="Invalid option selected ";
 $options="Please try again later.";
  $type="false";
    $message_type=2;
  
 }
 
 
 echo ussd_formatter($msisdn,$response.$options,$serviceCode,$type,$sessionId,$message_type);
     
 }
 //for *8022*4 ussd transactions
 
 if(str_contains($checker, '8022*41')) {
 
     //$checker=session_checker($msisdn,$conn);
    
# echo $response=$matches[0];
     //echo strlen($checker);
 if(counter($checker)>6  && $ussd_string<9){ 
     
    //$matches = array("Match 1: Chelsea vs Man City ", "Match 2: Manchester Utd vs Liverpool", "Match 3: Arsenal vs Stoke City","Your bet has been placed successfully.");
$matches=select_fixtures_pdo();
     $pick=strlen($checker)-7;
     $match_no=$pick+1;
  $response="Match $match_no :1H \n" .$matches[$pick];
   $type="true";
    $message_type=1;
  fixture_appender($msisdn,$conn,$response,$checker);
  
  if(counter($checker)==15  && $ussd_string<9){
      $response="";
       $type="true";
    $message_type=1;
   $options="\n  Bet placed succesfully.  You will receive your bet ticket shortly via SMS. Kindly accept the USSD popup that appears";
   //Call subscription API
   subscription($token,$msisdn);
  }
  
  else
  {
      $options="\n1. Home Win \n2. Draw \n3. Away Win";
  }
 }
  if(counter($checker)>8  && $ussd_string>3){ 

 $response="Invalid option selected ";
 $options="Please try again later.";
   $type="true";
    $message_type=1;
 }
 
 
 

 
  echo ussd_formatter($msisdn,$response.$options,$serviceCode,$type,$sessionId,$message_type);    
     
 }
 
 
  if(str_contains($checker, '8022*51')) {
 
     //$checker=session_checker($msisdn,$conn);
    
# echo $response=$matches[0];
     //echo strlen($checker);
 if(counter($checker)>6  && $ussd_string<7){ 
     
    //$matches = array("Match 1: Chelsea vs Man City ", "Match 2: Manchester Utd vs Liverpool", "Match 3: Arsenal vs Stoke City","Your bet has been placed successfully.");
$matches=select_fixtures_pdo();
     $pick=strlen($checker)-7;
     $match_no=$pick+1;
  $response="Match $match_no :\n" .$matches[$pick];
     $type="true";
    $message_type=1;
  fixture_appender($msisdn,$conn,$response,$checker);
  
  if(counter($checker)==13  && $ussd_string<7){
      $response="";
   $options="\n  Bet placed succesfully.  You will receive your bet ticket shortly via SMS. Kindly accept the USSD popup that appears";
       $type="false";
    $message_type=2;
//Call subscription API
   //subscription($token,$msisdn);
  }
  
  else
  {
      $options="\n1. 0-7 corners  
\n2. 8 corners  
\n3. 9 corners  
\n4. 10 corners  
\n5. 11+ corners  
\nEnter option (1-5):";
  }
 }
  if(counter($checker)>6  && $ussd_string>3){ 

 $response="Invalid option selected ";
 $options="Please try again later.";
    $type="false";
    $message_type=2;
  
 }
 
 
 

 
 echo  ussd_formatter($msisdn,$response.$options,$serviceCode,$type,$sessionId,$message_type);    
     
 } 


############new 8022#########################
 
<?php
function counter($string){
    return strlen($string);    
}

function ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type){
    $jsonData = [
        "response" => (string) $response,
        "userInputRequired" => (string) $type,
        "serviceCode" => (string) $serviceCode,
        "messageType" => (string) $message_type,
        "msisdn" => (string) $msisdn,
        "sessionId" => (string) $sessionId
    ];
    
    return json_encode($jsonData);
}

function session_checker($msisdn, $conn){
    $query = "SELECT * FROM ussd_manager where msisdn = '$msisdn' ORDER BY id DESC LIMIT 1";
    
    $pick = mysqli_query($conn,$query);
    $run = mysqli_fetch_array($pick);
    $session = $run['session'];
    
    return $session; 
}


function session_updater($msisdn, $conn, $fixture, $response, $ussd_string){
    $query="INSERT INTO ussd_manager (msisdn, t_date, session, fixture, response) values ('$msisdn', now(), '$ussd_string', '$fixture', '$response')";
    $pick=mysqli_query($conn,$query);
    
    return null;
}



function session_appender($msisdn,$conn,$fixture,$response,$ussd_string){
    $query="update ussd_manager set session=concat(session,'$ussd_string') where msisdn='$msisdn' order by id desc limit 1";
$pick=mysqli_query($conn,$query);
    
    return null;
}


function fixture_appender($msisdn,$conn,$fixture,$sess){
    
 $query="update ussd_manager set fixture=concat(fixture,'$fixture') where msisdn='$msisdn' and session='$sess' order by id desc limit 1";
$pick=mysqli_query($conn,$query);
    
    return null;
}


function fixture__select_appender($msisdn,$conn,$fixture_select,$sess){
    
 $query="update ussd_manager set fixture=concat(fixture,'$fixture_select') where msisdn='$msisdn' and session='$sess' order by id desc limit 1";
$pick=mysqli_query($conn,$query);
    
    return null;
}


function clear_ussd_queue($conn,$msisdn){
    
$query="update ussd_manager set session=concat('closed',session) where msisdn='$msisdn' and session not like '%closed%' order by id desc limit 1";
$pick=mysqli_query($conn,$query);
    
}

//header('Content-Type: application/json');


function subscription($token,$msisdn,$psipid){
$curl = curl_init();
$uniq_id=uniqid();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.pisimobile.net/v1/subscription/outbound/create',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{ 
    "pisipid":"'.$psipid.'", 
    "msisdn":"'.$msisdn.'",     
    "channel":"USSD",
    "trxid":"'.$uniq_id.'"
}',
  CURLOPT_HTTPHEADER => array(
    'vaspid:36',
    'pisi-authorization-token: Bearer '.$token.' ',
    'Content-Type:application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
return "Kindly accept the DOI USSD popup to complete your subscription.";
//echo "Tester here";

}


//$conn=mysqli_connect("localhost","new_web","K33pfit#","mtnng");   

//echo select_fixtures($conn);
function select_fixtures($conn){
    $result="";
$query="select teamaname,teambname,roundid from mtncorrectscore_teams  order by id desc limit 8";
$i=0;
$pick=mysqli_query($conn,$query);
    
    while($run=mysqli_fetch_array($pick)){
    $i++; 
    $roundid=$run['roundid'];
    $teamaname=$run['teamaname'];
    $teambname=$run['teambname'];
    
    $fixture=" Match $i: $teamaname vs $teambname ,";
    
    $result.=$fixture;
    
    
}
 $temp=substr($result, 0, -1);
    return array($temp);
    
}



function select_fixtures_pdo(){
    $conn=mysqli_connect("localhost","new_web","K33pfit#","mtnng");
      $pdo = new PDO("mysql:host=localhost;dbname=mtnng", "new_web", "K33pfit#");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = $pdo->query("select concat(teamaname,' vs ',teambname) as data from mtncorrectscore_teams where date(fixmdate)=date(now()) order by id desc limit 8")->fetchAll(PDO::FETCH_COLUMN);

    
    return $data;

}

/*function generate_ticket($conn,$num){
$query="select * from ussd_manager where msisdn ='2348034910941' and id=169  order by id desc limit 1"; 
$run=mysqli_query($conn,$query);
$pick=mysqli_fetch_array($run);
$id="Your Receipt #".$pick['id'].": ";
$fixtures=$pick['fixture'];


$search = ["Selected 1", "Selected 2", "Selected 3","\n"];
$replace = ["Home Win ", "Draw ", "Away Win ",""];
$corrected_fixtures=str_replace($search, $replace, $fixtures);


return $id.$corrected_fixtures;
    
    
}*/


function generate_ticket($conn,$num){
$query="select * from ussd_manager where msisdn ='$num' order by id desc limit 1"; 
$run=mysqli_query($conn,$query);
$pick=mysqli_fetch_array($run);
$id="Your Receipt #".$pick['id'];
 $fixtures=$pick['fixture'];
$counter= substr_count($fixtures,'Match ');
$parse=explode('Match ',$fixtures);
$final="";

foreach ($parse as $fruit) {
   $data="M" . trim($fruit); // trim() removes any leading/trailing whitespace
   //  "M" . trim($fruit).'<br>';
     $final.= select_winner_display($data);
}


$formattedDateTime = date('l, F jS, Y H:i:s A');
//echo $formattedDateTime; // Output: Sunday, October 26th, 2025 04:10:00 AM (or current date/time)

$later='was successfully entered on '.$formattedDateTime;
$final_ticket=$id.' '.$final.' '.$later;

//$search = ["Selected 1", "Selected 2", "Selected 3","\n"];
//$replace = ["Home Win ", "Draw ", "Away Win ",""];
//$corrected_fixtures=str_replace($search, $replace, $fixtures);



return $final_ticket;
    
    
}


function select_winner_display($message){
    
      $select=substr($message,-10);
    $parse=explode('vs',$message);

    
    if($select=='Selected 1'){
        $result=$parse[0].' Win ';    
        
    }
     elseif($select=='Selected 3'){
         
         //echo $message;
        // echo $parse[1];
         $match=explode(':',$parse[0])[0];
        $predict=str_replace('Selected 3','',$parse[1]);
        $result=$match.''.$predict.' ';
        
    }
     elseif($select=='Selected 2'){
      $match=explode(':',$parse[0])[0];
      $result=$match.' Draw ';
        
    }
    else{
        
        $result="";
    }
    return $result; 
    
}

function send_sms($token,$msisdn,$message,$psisid){
    
   
    
$trans_sms='sms'. uniqid();
    
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.pisimobile.net/v1/sms/outbound/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'pisi-authorization-token: Bearer '.$token.'',
    'vaspid: 36',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"pisisid\": \"".$psisid."\",\n    \"msisdn\": \"$msisdn\",\n    \"message\": \"$message\",\n    \"trxid\": \"$trans_sms\"\n}");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);

curl_close($ch);
    
    
}