<?php

$conn=mysqli_connect("localhost","new_web","K33pfit#","mtnng"); 
$num=0;

//echo generate_ticket($conn, $num);


function generate_ticket($conn,$num){
$query="select * from ussd_manager where msisdn ='2348034910941' order by id desc limit 1"; 
$run=mysqli_query($conn,$query);
$pick=mysqli_fetch_array($run);
$id="Your Receipt #".$pick['id'];
 $fixtures=$pick['fixture'];
$counter= substr_count($fixtures,'Match ');
$parse=explode('Match ',$fixtures);
$final="";

foreach ($parse as $fruit) {
 echo  $data="M" . trim($fruit); // trim() removes any leading/trailing whitespace
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



$token='eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwYXlsb2FkIjp7InZhc3AiOiIzNiJ9LCJpYXQiOjE3NTIwNjYwOTksImV4cCI6MTc2NzYxODA5OX0.tRfTAudEKxQnVZkUtTsEpdF9G2PTHDiOgpRePN077PQ';
 $msisdn="2348034910941";
$pisisid="283";
echo $message=generate_ticket($conn,$num);
$new_message=str_replace(["\n", "\r"], '', $message);
//$message="Your Receipt #169: : M1 : Getafe Win M2 Draw M3 Draw M4 Rayo Vallecano was successfully entered on Sunday, October 26th, 2025 11:25:18 AM"; 
//$message="#169:Match 1 : Getafe vs Real Madrid Home Win Match 2 :Las Palmas vs Eibar Draw Match 3 : Nantes vs Lille Draw Match 4 :Levante vs Rayo Vallecano Away Win Away Win";  
//$message=select_winner_display($ticket);

send_sms($token,$msisdn,$new_message,$pisisid); 



function send_sms($token,$msisdn,$message,$pisisid){
    
    //echo $token.'<br>'.$msisdn.'<br>'.$message.'<br>'.$psisid; 
    
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
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"pisisid\": \"".$pisisid."\",\n    \"msisdn\": \"$msisdn\",\n    \"message\": \"$message\",\n    \"trxid\": \"$trans_sms\"\n}");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

echo $response = curl_exec($ch);

curl_close($ch);
    
    
}