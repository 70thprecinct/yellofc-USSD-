<?php
require_once("includes/includables.php");

#ho "Welcome to USSD";
$load = file_get_contents('php://input');

$log = "User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
	     "Payload: " . $load . PHP_EOL .
		   "-------------------------" . PHP_EOL;

file_put_contents('ussd.log', $log, FILE_APPEND);

$parse = json_decode($load, true);

$ussd_string = $parse['ussdString'] ?? null;
$msisdn = $parse['msisdn'] ?? null;
$sessionId = $parse['sessionId'] ?? null;
$serviceCode = $parse['serviceCode'] ?? null;
$pisi_token = get_token();
$ussd = "";

$message_type = 2;
$type = "false";
$response = "Something went wrong. Please try again";
$options = "";
$checker = null;
$debited = false;

if (!$ussd_string || !$msisdn || !$sessionId || !$serviceCode || $serviceCode != "8022" || !$pisi_token){
  append_response($msisdn, $sessionId, $ussd_string, $response);
  echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
  exit;
}

//To know if the user/msisdn/phone number has successfully a placed bet before or not

if ($ussd_string == "8022*1"){
    $ussd = "80221";
    $ussd_string = 1;
    if (!create_session($msisdn, $sessionId, $ussd, $debited)){
        append_response($msisdn, $sessionId, $ussd_string, $response);
        echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
        exit;
    }
}else if ($ussd_string == "8022*2"){
    $ussd = "80221";
    $ussd_string = 2;
    if (!create_session($msisdn, $sessionId, $ussd, $debited)){
        append_response($msisdn, $sessionId, $ussd_string, $response);
        echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
        exit;
    }
}else if ($ussd_string == "8022*3"){
    $ussd = "80221";

    $ussd_string = 3;
    if (!create_session($msisdn, $sessionId, $ussd, $debited)){
        append_response($msisdn, $sessionId, $ussd_string, $response);
        echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
        exit;
    }
}else if ($ussd_string == "8022*4"){
    $ussd = "80222";
    $ussd_string = 1;
    if (!create_session($msisdn, $sessionId, $ussd, $debited)){
        append_response($msisdn, $sessionId, $ussd_string, $response);
        echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
        exit;
    }
}else if ($ussd_string == "8022*5"){
    $ussd = "80222";
    $ussd_string = 2;
    if (!create_session($msisdn, $sessionId, $ussd, $debited)){
        append_response($msisdn, $sessionId, $ussd_string, $response);
        echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
        exit;
    }
}else if ($ussd_string == "8022*6"){
    $ussd = "80222";

    $ussd_string = 3;
    if (!create_session($msisdn, $sessionId, $ussd, $debited)){
        append_response($msisdn, $sessionId, $ussd_string, $response);
        echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
        exit;
    }
}


if ($ussd_string == "8022"){
    $ussd = "8022";
    if (!create_session($msisdn, $sessionId, $ussd, $debited)){
        append_response($msisdn, $sessionId, $ussd_string, $response);
        echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
        exit;
    }

    $options = "1. Predict and Win N1M Everyday\n2. Soka Games\n3. More Games";
    $response = "Win Millions of Cash Daily!\n".$options;
    $type = "true";
    $message_type = 1;

    append_response($msisdn, $sessionId, $ussd_string, $response);
    echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
    exit;
}else if (strpos($ussd_string, '8022') === false && ((is_numeric($ussd_string)) || is_sport_score($ussd_string))) {
    $checker = get_ussd($msisdn, $sessionId);

    if ($checker){
      $checker .= $ussd_string;
    }
}

if (!$checker){
    $response = "Invalid option. Please go back and try again";
    $type = "false";
    $message_type = 2;
    append_response($msisdn, $sessionId, $ussd_string, $response);
    echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);
    exit;
}

$array = [
    "checker" => $checker, 
    "session_id" => $sessionId, 
    "msisdn" => $msisdn, 
    "ussd_string" => $ussd_string,
    "debited" => $debited,
    "pisi_token" => $pisi_token,
];

if (strpos($checker, '80221') !== false){
    list($response, $type, $message_type) = handle_predictor($array, "80221");
}else if (strpos($checker, '80222') !== false){
    list($response, $type, $message_type) = handle_soka($array, "80222");
}else if (strpos($checker, '80223') !== false){
    list($response, $type, $message_type) = handle_others($array, "80223");
}

append_response($msisdn, $sessionId, $ussd_string, $response);
echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);