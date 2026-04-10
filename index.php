<?php
require_once("includes/includables.php");

#ho "Welcome to USSD";
$load = file_get_contents('php://input');

$log = "User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
	     "Payload: " . $load . PHP_EOL .
		   "-------------------------" . PHP_EOL;


file_put_contents('ussd.log', $log, FILE_APPEND);

$token = TOKEN;

$parse = json_decode($load, true);

$ussd_string = $parse['ussdString'] ?? null;
$msisdn = $parse['msisdn'] ?? null;
$sessionId = $parse['sessionId'] ?? null;
$serviceCode = $parse['serviceCode'] ?? null;
$ussd = "";

$message_type = 2;
$type = "false";
$response = "Something went wrong. Please try again";
$options = "";
$checker = null;

if (!$ussd_string || !$msisdn || !$sessionId || !$serviceCode || $serviceCode != "8022"){
  append_response($msisdn, $sessionId, $ussd_string, $response);
  echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
  exit;
}


if ($ussd_string == "8022"){
    $ussd = "8022";
    if (!create_session($msisdn, $sessionId, $ussd)){
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
}else if ($ussd_string == "8022*1"){
    $ussd = "80221";
    if (!create_session($msisdn, $sessionId, $ussd)){
        append_response($msisdn, $sessionId, $ussd_string, $response);
        echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
        exit;
    }
    
    list($response, $type, $message_type) = handle_predictor(["checker" => $ussd, "session_id" => $sessionId, "msisdn" => $msisdn, "ussd_string" => ""], $ussd);

    append_response($msisdn, $sessionId, $ussd_string, $response);
    echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);
    exit;
}else if ($ussd_string == "8022*2"){
    $ussd = "80222";
    if (!create_session($msisdn, $sessionId, $ussd)){
        append_response($msisdn, $sessionId, $ussd_string, $response);
        echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
        exit;
    }

    list($response, $type, $message_type) = handle_soka(["checker" => $ussd, "session_id" => $sessionId, "msisdn" => $msisdn, "ussd_string" => ""], $ussd);

    echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);
    exit;
}else if ($ussd_string == "8022*3"){
    $ussd = "80223";
    if (!create_session($msisdn, $sessionId, $ussd)){
        append_response($msisdn, $sessionId, $ussd_string, $response);
        echo ussd_formatter($msisdn, $response, $serviceCode, $type, $sessionId, $message_type);
        exit;
    }

    list($response, $type, $message_type) = handle_others(["checker" => $ussd, "session_id" => $sessionId, "msisdn" => $msisdn, "ussd_string" => ""], $ussd);

    append_response($msisdn, $sessionId, $ussd_string, $response);
    echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);
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

if (strpos($checker, '80221') !== false){
    list($response, $type, $message_type) = handle_predictor(["checker" => $checker, "session_id" => $sessionId, "msisdn" => $msisdn, "ussd_string" => $ussd_string], "80221");
}else if (strpos($checker, '80222') !== false){
    list($response, $type, $message_type) = handle_soka(["checker" => $checker, "session_id" => $sessionId, "msisdn" => $msisdn, "ussd_string" => $ussd_string], "80222");
}else if (strpos($checker, '80223') !== false){
    list($response, $type, $message_type) = handle_others(["checker" => $checker, "session_id" => $sessionId, "msisdn" => $msisdn, "ussd_string" => $ussd_string], "80223");
}

append_response($msisdn, $sessionId, $ussd_string, $response);
echo ussd_formatter($msisdn,$response,$serviceCode,$type,$sessionId,$message_type);