<?php
function get_promotional_message($game)
{
    $messages = [
        [
            "game" => "Total Goals",
            "message" => "Predict The Total Goals of 6 matches & WIN ₦1M DAILY!\nFirst day FREE! Dial *8022*2# now to start winning!"
        ],
        [
            "game" => "Correct Score",
            "message" => "Predict CorrectScores & WIN ₦1M DAILY!\nFirst day FREE! Dial *8022*3# now",
        ],
        [
            "game" => "Soka 4",
            "message" => "Play Soka 4 & WIN ₦2M DAILY! Dial *8022*4#\nEasy entry, BIG rewards!",
        ],
        [
            "game" => "Total Corners",
            "message" => "Predict the Total Corners & WIN ₦2M DAILY!\nDial *8022*8# now!",
        ],
        [
            "game" => "Predictor",
            "message" => "Predict & WIN ₦1M DAILY!\nFirst day FREE! Dial *8022*1#",
        ],
        [
            "game" => "Soka 6",
            "message" => "Ready for ₦3M? Play Soka 6 DAILY!\nDial *8022*5#",
        ],
        [
            "game" => "Soka Half",
            "message" => "Predict Halftime & Fulltime Score to  WIN ₦2M! Dial *8022*7# now",
        ],
        [
            "game" => "Soka 8",
            "message" => "₦10M could be yours TODAY! Play DAILY!\nPlay Soka 8 daily to win BIG. Dial *8022*7#",
        ]
    ];

    $game = strtolower(trim($game));

    $filtered = array_values(array_filter($messages, function ($item) use ($game) {
        return strtolower($item['game']) !== $game;
    }));

    if (empty($filtered)) {
        return null;
    }

    $random = $filtered[array_rand($filtered)];

    return $random['message'];
}

function get_psipid($game = null){
    $array = [
        "Total Goals" => "5676",
        "Predictor" => "5677",
        "Correct Score" => "5678",
        "Soka 4" => "5706",
        "Soka 6" => "5707",
        "Soka 8" => "5708",
        "Soka Corners" => "5709",
        "Soka Half" => "5710",
    ];

    return $game? ($array[$game] ?? null) : $array;
}

function get_sms_psipid($game = null){
    $array = [
        "Total Goals" => "268",
        "Predictor" => "269",
        "Correct Score" => "270",
        "Soka 4" => "282",
        "Soka 6" => "283",
        "Soka 8" => "284",
        "Soka Corners" => "285",
        "Soka Half" => "286",
    ];

    return $game? ($array[$game] ?? null) : $array;
}

function get_token() {
    $file = "pisi.token.json";
    $token = null;
    $json = file_exists($file) ? file_get_contents($file) : null;

    if ($json) {
        $array = json_decode($json, true);
        if (isset($array['token']) && isset($array['expiry'])) {
            if (time() < $array['expiry']) {
                $token = $array['token'];
            }
        }
    }

    if (!$token) {
        $res = generate_pisi_token(); 

        if ($res){
            $token = $res['token'];
            $expires = $res['expiry'];
        
            $array = [
                "token" => $token,
                "expiry" => $expires
            ];

            file_put_contents($file, json_encode($array), LOCK_EX);
        }
    }

    return $token;
}

function generate_pisi_token() {
    $url = PISI_BASE_URL . "/v1/authentication/create";
    
    $ch = curl_init($url);

    $payload = json_encode([
        "vaspid" => VASPID 
    ]);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'vaspid: '.VASPID,
            'pisi-authorization-token: Bearer Token'
        ],
    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);

        if (isset($data['success']) && $data['success'] == true) {
            return [
                "token"  => $data['pisi-authorization-token'],
                "expiry" => strtotime($data['expiration'])
            ];
        }
    }
    return false;
}

function handle_predictor_type($data, $base_sublim, $max){
    $response = "";
    $type = "false";
    $message_type = 2;
    $options = "";
    $append = false;
    $close = false;
    $is_valid_option = false;
    $custom = false;
    $game = "Predictor";

    if (has_bet_today($data['msisdn'], $game)){
        $response .= "Welcome to {$game}\nWhere you pick match outcomes for\n{$max} matches to win N".number_format($data['winnings'], 0)."\n";
        $options = "You have placed a bet today... Let's win more tomorrow or dial *8022# to choose another game!";
        $type = "false";
        $message_type = 2;
        return [$response.$options, $type, $message_type];
    }

    $debited = set_debited($data['msisdn'], $game);

    if ($debited){
        $data['debited'] = $debited[0]? 1 : 0;
        $data['debited_id'] = $debited[1];
    }
    $data['is_initial'] = set_initial($data['msisdn'], $game);

    if ($data['sublim'] == $base_sublim){
        $response .= "Welcome to {$game}\nPick match outcomes for\n{$max} matches to win N".number_format($data['winnings'], 0)."\n";
        $options = "Press 1 to continue...";
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);

        $type = "true";
        $message_type = 1;
        return [$response.$options, $type, $message_type];
    }

    if (strlen($data['ussd_string']) > 1){
        $data['sublim'] = preg_replace('/'.preg_quote($data['ussd_string'], '/').'$/', "0", $data['sublim']);
    }

    $confirm = substr($data['sublim'], 1, 1);

    if ($confirm != 1){
        $response = "Thank you. Bye!";
        $custom = true;
        $append = false;
        $close = true;
    }

    $data['sublim'] = substr_replace($data['sublim'], '', 1, 1);

    $count = strlen($data['sublim']) - 1;
    $user_input = $data['ussd_string'];
    $current_option = $count + 1;
    $type_options = get_default_type_options("predictor");
    $matches_array = get_matches("predictor", $max);

    if ($count <= $max && $custom != true){
        if (in_array($user_input, [1, 2, 3])){
            if ($data['sublim'] != $base_sublim){
                switch ($user_input){
                    case 1: $user_input = "Home"; break;
                    case 2: $user_input = "Draw"; break;
                    case 3: $user_input = "Away"; break;
                }

                handle_user_pick($data['msisdn'], $data['session_id'], $user_input, $data['ussd_string'], ($count - 1), "", $matches_array);
                $append = true;
            }
            $append = true;
        }else{
            $is_valid_option = true;
            $response = "Invalid option";
            $current_option = $current_option - 1;
            $count = $count - 1;
        }
    }

    if ($count < $max){
        if (!$custom){
            $matches = array_column($matches_array, "name");

            if (empty($matches) || count($matches) < $max){
                $response = "No matches available yet.";
                $type = "false";
                $message_type = 2;
                $close = true;
            }else{
                $current_match = $matches[$current_option - 1] ?? [];
                if (!empty($current_match)){
                    $response .= "{$current_match}\n";
                    $options = implode("\n", array_map(function($key, $v){ 
                        return ($key + 1).". {$v}"; 
                    }, array_keys($type_options), $type_options));
                    $type = "true";
                    $message_type = 1;
                }else{
                    $response = "Match not available";
                    $type = "false";
                    $message_type = 2;
                    $close = true;
                }
            }
        }
    }else{
        $data['game'] = $game;
        $data['psipid'] = get_psipid($data['game']);
        $data['sms_psipid'] = get_sms_psipid($data['game']);
        list($response, $close, $append) = handleSubmit($data, $user_input, $max, $count, $data['sublim'], $append, "predictor");

        if ($close != true){
            $type = "true";
            $message_type = 1;
        }
    }

    $response .= $options;

    if (strlen($data['ussd_string']) > 1){
        $data['ussd_string'] = 1;
    }

    if (!empty($response) && $append){
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);
    }

    if ($close == true){
        close_session($data['msisdn'], $data['session_id']);
    }

    return [$response, $type, $message_type];
}

function handle_total_goals_type($data, $base_sublim, $max){
    $response = "";
    $type = "false";
    $message_type = 2;
    $options = "";
    $append = false;
    $close = false;
    $is_valid_option = false;
    $custom = false;
    $game = "Total Goals";

    if (has_bet_today($data['msisdn'], $game)){
        $response .= "Welcome to {$game}\nWhere you enter total goals scored\nin {$max} matches to win N".number_format($data['winnings'], 0)."\n";
        $options = "You have placed a bet today... Let's win more tomorrow or dial *8022# to choose another game!";
        $type = "false";
        $message_type = 2;
        return [$response.$options, $type, $message_type];
    }

    $debited = set_debited($data['msisdn'], $game);
    if ($debited){
        $data['debited'] = $debited[0]? 1 : 0;
        $data['debited_id'] = $debited[1];
    }
    $data['is_initial'] = set_initial($data['msisdn'], $game);

    if ($data['sublim'] == $base_sublim){
        $response .= "Welcome to {$game}\nEnter total goals scored\nin each match to win N".number_format($data['winnings'], 0)."\n";
        $options = "Press 1 to continue...";
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);

        $type = "true";
        $message_type = 1;
        return [$response.$options, $type, $message_type];
    }

    if (strlen($data['ussd_string']) > 1){
        $data['sublim'] = preg_replace('/'.preg_quote($data['ussd_string'], '/').'$/', "0", $data['sublim']);
    }

    $confirm = substr($data['sublim'], 1, 1);

    if ($confirm != 1){
        $response = "Thank you. Bye!";
        $custom = true;
        $append = false;
        $close = true;
    }

    $data['sublim'] = substr_replace($data['sublim'], '', 1, 1);

    $count = strlen($data['sublim']) - 1;
    $user_input = $data['ussd_string'];
    $current_option = $count + 1;
    $type_options = get_default_type_options("total_goals");
    $matches_array = get_matches("total_goals", $max);

    if ($count <= $max && $custom != true){
        if ($data['sublim'] != $base_sublim){
            if (is_numeric($user_input)){
                handle_user_pick($data['msisdn'], $data['session_id'], $user_input, $data['ussd_string'], ($count - 1), "Total Goals", $matches_array);
                $append = true;
            }else{
                $is_valid_option = true;
                $response = "Invalid option";
                $current_option = $current_option - 1;
                $count = $count - 1;
            }
        }else{
            $append = true;
        }
    }

    if ($count < $max){
        if (!$custom){
            $matches = array_column($matches_array, "name");

            if (empty($matches) || count($matches) < $max){
                $response = "No matches available yet.";
                $type = "false";
                $message_type = 2;
                $close = true;
            }else{
                $current_match = $matches[$current_option - 1] ?? [];
                if (!empty($current_match)){
                    $response .= "{$current_match}\nEnter total goals:\n";
                    $options = implode("\n", array_map(function($key, $v){ 
                        return ($key + 1).". {$v}"; 
                    }, array_keys($type_options), $type_options));

                    $type = "true";
                    $message_type = 1;
                }else{
                    $response = "Match not available";
                    $type = "false";
                    $message_type = 2;
                    $close = true;
                }
            }
        }
    }else{
        $data['game'] = $game;
        $data['psipid'] = get_psipid($data['game']);
        $data['sms_psipid'] = get_sms_psipid($data['game']);
        list($response, $close, $append) = handleSubmit($data, $user_input, $max, $count, $data['sublim'], $append, "total_goals");

        if ($close != true){
            $type = "true";
            $message_type = 1;
        }
    }

    $response .= $options;

    if (strlen($data['ussd_string']) > 1){
        $data['ussd_string'] = 1;
    }

    if (!empty($response) && $append){
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);
    }

    if ($close == true){
        close_session($data['msisdn'], $data['session_id']);
    }

    return [$response, $type, $message_type];
}

function handle_correct_score_type($data, $base_sublim, $max){
    $response = "";
    $type = "false";
    $message_type = 2;
    $options = "";
    $append = false;
    $close = false;
    $is_valid_option = false;
    $custom = false;
    $game = "Correct Score";

    if (has_bet_today($data['msisdn'], $game)){
        $response .= "Welcome to {$game}\nWhere you enter final scores for {$max} matches\n(e.g 2-1, 0-0)\nTo win N".number_format($data['winnings'], 0)."\n";
        $options = "You have placed a bet today... Let's win more tomorrow or dial *8022# to choose another game!";
        $type = "false";
        $message_type = 2;
        return [$response.$options, $type, $message_type];
    }

    $debited = set_debited($data['msisdn'], $game);
    if ($debited){
        $data['debited'] = $debited[0]? 1 : 0;
        $data['debited_id'] = $debited[1];
    }
    $data['is_initial'] = set_initial($data['msisdn'], $game);

    if ($data['sublim'] == $base_sublim){
        $response .= "Welcome to {$game}\nEnter final scores for each match\n(e.g 2-1, 0-0)\nWin N".number_format($data['winnings'], 0)."\n";
        $options = "Press 1 to continue...";
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);

        $type = "true";
        $message_type = 1;
        return [$response.$options, $type, $message_type];
    }

    if (is_sport_score($data['ussd_string'])){
        $data['sublim'] = str_replace($data['ussd_string'], "0", $data['sublim']);
    }

    if (strlen($data['ussd_string']) > 1){
        $data['sublim'] = preg_replace('/'.preg_quote($data['ussd_string'], '/').'$/', "0", $data['sublim']);
    }

    $confirm = substr($data['sublim'], 1, 1);

    if ($confirm != 1){
        $response = "Thank you. Bye!";
        $custom = true;
        $append = false;
        $close = true;
    }

    $data['sublim'] = substr_replace($data['sublim'], '', 1, 1);

    $count = strlen($data['sublim']) - 1;
    $user_input = $data['ussd_string'];
    $current_option = $count + 1;
    $type_options = get_default_type_options("correct_score");
    $matches_array = get_matches("correct_score", $max);

    if ($count <= $max && $custom != true){
        if ($data['sublim'] != $base_sublim){
            if (is_sport_score($user_input)){
                $user_input = str_replace(" ", "", $user_input);
                handle_user_pick($data['msisdn'], $data['session_id'], $user_input, $data['ussd_string'], ($count - 1), "Correct Score", $matches_array);
                $data['ussd_string'] = $count;
                $append = true;
            }else{
                $is_valid_option = true;
                $response = "Invalid option";
                $current_option = $current_option - 1;
                $count = $count - 1;
            }
        }else{
            $append = true;
        }
    }

    if ($count < $max){
        if (!$custom){
            $matches = array_column($matches_array, "name");

            if (empty($matches) || count($matches) < $max){
                $response = "No matches available yet.";
                $type = "false";
                $message_type = 2;
                $close = true;
            }else{
                $current_match = $matches[$current_option - 1] ?? [];
                if (!empty($current_match)){
                    $response .= "{$current_match}\nEnter score (Home-Away):\n";
                    $options = implode("\n", array_map(function($key, $v){ 
                        return ($key + 1).". {$v}"; 
                    }, array_keys($type_options), $type_options));
                    $type = "true";
                    $message_type = 1;
                }else{
                    $response = "Match not available";
                    $type = "false";
                    $message_type = 2;
                    $close = true;
                }
            }
        }
    }else{
        $data['game'] = $game;
        $data['psipid'] = get_psipid($data['game']);
        $data['sms_psipid'] = get_sms_psipid($data['game']);
        list($response, $close, $append) = handleSubmit($data, $user_input, $max, $count, $data['sublim'], $append, "correct_score");

        if ($close != true){
            $type = "true";
            $message_type = 1;
        }
    }

    $response .= $options;

    if (strlen($data['ussd_string']) > 1){
        $data['ussd_string'] = 1;
    }

    if (!empty($response) && $append){
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);
    }

    if ($close == true){
        close_session($data['msisdn'], $data['session_id']);
    }

    return [$response, $type, $message_type];
}

function handle_soka_type($data, $base_sublim, $max){
    $response = "";
    $type = "false";
    $message_type = 2;
    $options = "";
    $append = false;
    $close = false;
    $is_valid_option = false;
    $custom = false;
    $game = "Soka {$max}";

    if (has_bet_today($data['msisdn'], $game)){
        $response .= "Welcome to {$game}\nWhere you pick the scores for\n{$max} matches to win N".number_format($data['winnings'], 0)."\n";
        $options = "You have placed a bet today... Let's win more tomorrow or dial *8022# to choose another game!";
        $type = "false";
        $message_type = 2;
        return [$response.$options, $type, $message_type];
    }

    $debited = set_debited($data['msisdn'], $game);
    if ($debited){
        $data['debited'] = $debited[0]? 1 : 0;
        $data['debited_id'] = $debited[1];
    }
    $data['is_initial'] = set_initial($data['msisdn'], $game);

    if ($data['sublim'] == $base_sublim){
        $response .= "Welcome to {$game}\nPick the scores for\n{$max} matches to win N".number_format($data['winnings'], 0)."\n";
        $options = "Press 1 to continue...";
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);

        $type = "true";
        $message_type = 1;
        return [$response.$options, $type, $message_type];
    }

    if (is_sport_score($data['ussd_string'])){
        $data['sublim'] = str_replace($data['ussd_string'], "0", $data['sublim']);
    }

    if (strlen($data['ussd_string']) > 1){
        $data['sublim'] = preg_replace('/'.preg_quote($data['ussd_string'], '/').'$/', "0", $data['sublim']);
    }

    $confirm = substr($data['sublim'], 1, 1);

    if ($confirm != 1){
        $response = "Thank you. Bye!";
        $custom = true;
        $append = false;
        $close = true;
    }

    $data['sublim'] = substr_replace($data['sublim'], '', 1, 1);

    $count = strlen($data['sublim']) - 1;
    $user_input = $data['ussd_string'];
    $current_option = $count + 1;
    $type_options = get_default_type_options("soka");
    $matches_array = get_matches("soka", $max);

    if ($count <= $max && $custom != true){
        if ($data['sublim'] != $base_sublim){
            if ($user_input == 7){
                $response = "Please enter custom score (e.g 0-2, 3-9):\n";
                $custom = true;
                $type = "true";
                $message_type = 1;
                $count = $count - 1;
            }else{
                $user_option = is_sport_score($user_input)? str_replace(" ", "", $user_input) : $type_options[$user_input - 1];
                $data['ussd_string'] = is_sport_score($user_input)? 7 : $data['ussd_string'];
                handle_user_pick($data['msisdn'], $data['session_id'], $user_option, $data['ussd_string'], ($count - 1), "", $matches_array);
                $append = true;
            }
        }else{
            $append = true;
        }
    }

    if ($count < $max){
        if (!$custom){
            $matches = array_column($matches_array, "name");

            if (empty($matches) || count($matches) < $max){
                $response = "No matches available yet.";
                $type = "false";
                $message_type = 2;
                $close = true;
            }else{
                $current_match = $matches[$current_option - 1] ?? [];
                if (!empty($current_match)){
                    $response .= "{$current_match}\nPick Any team score:\n";
                    $options = implode("\n", array_map(function($key, $v){ 
                        return ($key + 1).". {$v}"; 
                    }, array_keys($type_options), $type_options));
                    $type = "true";
                    $message_type = 1;
                }else{
                    $response = "Match not available";
                    $type = "false";
                    $message_type = 2;
                    $close = true;
                }
            }
        }
    }else{
        $data['game'] = $game;
        $data['psipid'] = get_psipid($data['game']);
        $data['sms_psipid'] = get_sms_psipid($data['game']);
        list($response, $close, $append) = handleSubmit($data, $user_input, $max, $count, $data['sublim'], $append, "soka");

        if ($close != true){
            $type = "true";
            $message_type = 1;
        }
    }

    $response .= $options;

    if (strlen($data['ussd_string']) > 1){
        $data['ussd_string'] = 1;
    }

    if (!empty($response) && $append){
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);
    }

    if ($close == true){
        close_session($data['msisdn'], $data['session_id']);
    }

    return [$response, $type, $message_type];
}

function handle_soka_type_1($data, $base_sublim, $max){
    $response = "";
    $type = "false";
    $message_type = 2;
    $options = "";
    $append = false;
    $close = false;
    $is_valid_option = false;
    $custom = false;
    $game = "Soka Half";

    if (has_bet_today($data['msisdn'], $game)){
        $response .= "Welcome to {$game}\nWhere you predict match results for\n1st and 2nd half ({$max} picks)\nTo win N".number_format($data['winnings'], 0)."\n";
        $options = "You have placed a bet today... Let's win more tomorrow or dial *8022# to choose another game!";
        $type = "false";
        $message_type = 2;
        return [$response.$options, $type, $message_type];
    }

    $debited = set_debited($data['msisdn'], $game);
    if ($debited){
        $data['debited'] = $debited[0]? 1 : 0;
        $data['debited_id'] = $debited[1];
    }

    $data['is_initial'] = set_initial($data['msisdn'], $game);

    if ($data['sublim'] == $base_sublim){
        $response .= "Welcome to {$game}\nPredict match results for\n1st and 2nd half ({$max} picks)\nWin N".number_format($data['winnings'], 0)."\n";
        $options = "Press 1 to continue...";
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);

        $type = "true";
        $message_type = 1;
        return [$response.$options, $type, $message_type];
    }

    if (strlen($data['ussd_string']) > 1){
        $data['sublim'] = preg_replace('/'.preg_quote($data['ussd_string'], '/').'$/', "0", $data['sublim']);
    }

    $confirm = substr($data['sublim'], 1, 1);

    if ($confirm != 1){
        $response = "Thank you. Bye!";
        $custom = true;
        $append = false;
        $close = true;
    }

    $data['sublim'] = substr_replace($data['sublim'], '', 1, 1);

    $count = strlen($data['sublim']) - 1;
    $user_input = $data['ussd_string'];
    $current_option = $count + 1;
    $type_options = get_default_type_options("soka half");
    $matches_array = get_matches("soka", ($max/2));

    if ($count <= $max && $custom != true){
        if (in_array($user_input, [1, 2, 3])){
            if ($data['sublim'] != $base_sublim){
                $half = ($current_option  < 6? "(1H)" : "(2H)");
                switch ($user_input){
                    case 1: $user_input = "Home {$half}"; break;
                    case 2: $user_input = "Draw {$half}"; break;
                    case 3: $user_input = "Away {$half}"; break;
                }

                $v = $count - 1;
                handle_user_pick($data['msisdn'], $data['session_id'], $user_input, $data['ussd_string'], ($v < ($max/2)? $v : ($v - ($max/2))), "", $matches_array);
                $append = true;
            }
            $append = true;
        }else{
            $is_valid_option = true;
            $response = "Invalid option";
            $current_option = $current_option - 1;
            $count = $count - 1;
        }
    }

    if ($count < $max){
        if (!$custom){
            $matches = array_column($matches_array, "name");

            if (empty($matches) || count($matches) < $max){
                $response = "No matches available yet.";
                $type = "false";
                $message_type = 2;
                $close = true;
            }else{
                $v = $current_option - 1;
                $current_match = $matches[$v < ($max/2)? $v : ($v - ($max/2))] ?? [];
                $half = ($current_option  < 5? "(1st Half)" : "(2nd Half)");
                if (!empty($current_match)){
                    $response .= "{$current_match} - {$half}\n";
                    $options = implode("\n", array_map(function($key, $v){ 
                        return ($key + 1).". {$v}"; 
                    }, array_keys($type_options), $type_options));
                    $type = "true";
                    $message_type = 1;
                }else{
                    $response = "Match not available";
                    $type = "false";
                    $message_type = 2;
                    $close = true;
                }
            }
        }
    }else{
        $data['game'] = $game;
        $data['psipid'] = get_psipid($data['game']);
        $data['sms_psipid'] = get_sms_psipid($data['game']);
        list($response, $close, $append) = handleSubmit($data, $user_input, $max, $count, $data['sublim'], $append, "soka");

        if ($close != true){
            $type = "true";
            $message_type = 1;
        }
    }

    $response .= $options;

    if (strlen($data['ussd_string']) > 1){
        $data['ussd_string'] = 1;
    }

    if (!empty($response) && $append){
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);
    }

    if ($close == true){
        close_session($data['msisdn'], $data['session_id']);
    }

    return [$response, $type, $message_type];
}

function handle_soka_type_2($data, $base_sublim, $max){
    $response = "";
    $type = "false";
    $message_type = 2;
    $options = "";
    $append = false;
    $close = false;
    $is_valid_option = false;
    $custom = false;
    $game = "Soka Corners";

    if (has_bet_today($data['msisdn'], $game)){
        $response .= "Welcome to {$game}\nWhere you predict total corners for\n{$max} matches to win N".number_format($data['winnings'], 0)."\n";
        $options = "You have placed a bet today... Let's win more tomorrow or dial *8022# to choose another game!";
        $type = "false";
        $message_type = 2;
        return [$response.$options, $type, $message_type];
    }

    $debited = set_debited($data['msisdn'], $game);
    if ($debited){
        $data['debited'] = $debited[0]? 1 : 0;
        $data['debited_id'] = $debited[1];
    }
    $data['is_initial'] = set_initial($data['msisdn'], $game);

    if ($data['sublim'] == $base_sublim){
        $response .= "Welcome to {$game}\nPredict total corners for\n{$max} matches to win N".number_format($data['winnings'], 0)."\n";
        $options = "Press 1 to continue...";
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);

        $type = "true";
        $message_type = 1;
        return [$response.$options, $type, $message_type];
    }

    if (is_sport_score($data['ussd_string'])){
        $data['sublim'] = str_replace($data['ussd_string'], "0", $data['sublim']);
    }

    if (strlen($data['ussd_string']) > 1){
        $data['sublim'] = preg_replace('/'.preg_quote($data['ussd_string'], '/').'$/', "0", $data['sublim']);
    }

    $confirm = substr($data['sublim'], 1, 1);

    if ($confirm != 1){
        $response = "Thank you. Bye!";
        $custom = true;
        $append = false;
        $close = true;
    }

    $data['sublim'] = substr_replace($data['sublim'], '', 1, 1);

    $count = strlen($data['sublim']) - 1;
    $user_input = $data['ussd_string'];
    $current_option = $count + 1;
    $type_options = get_default_type_options("soka corners");
    $matches_array = get_matches("soka", $max);

    if ($count <= $max && $custom != true){
        if ($data['sublim'] != $base_sublim){
            if ($user_input == 7){
                $response = "Please enter custom total corners (e.g 0-2, 3-9):\n";
                $custom = true;
                $type = "true";
                $message_type = 1;
                $count = $count - 1;
            }else{
                $user_option = is_sport_score($user_input)? str_replace(" ", "", $user_input) : $type_options[$user_input - 1];
                $data['ussd_string'] = is_sport_score($user_input)? 7 : $data['ussd_string'];
                handle_user_pick($data['msisdn'], $data['session_id'], $user_option, $data['ussd_string'], ($count - 1), "Total Corners", $matches_array);
                $append = true;
            }
        }else{
            $append = true;
        }
    }

    if ($count < $max){
        if (!$custom){
            $matches = array_column($matches_array, "name");

            if (empty($matches) || count($matches) < $max){
                $response = "No matches available yet.";
                $type = "false";
                $message_type = 2;
                $close = true;
            }else{
                $current_match = $matches[$current_option - 1] ?? [];
                if (!empty($current_match)){
                    $response .= "{$current_match}\nPick total corners:\n";
                    $options = implode("\n", array_map(function($key, $v){ 
                        return ($key + 1).". {$v}"; 
                    }, array_keys($type_options), $type_options));
                    $type = "true";
                    $message_type = 1;
                }else{
                    $response = "Match not available";
                    $type = "false";
                    $message_type = 2;
                    $close = true;
                }
            }
        }
    }else{
        $data['game'] = $game;
        $data['psipid'] = get_psipid($data['game']);
        $data['sms_psipid'] = get_sms_psipid($data['game']);
        list($response, $close, $append) = handleSubmit($data, $user_input, $max, $count, $data['sublim'], $append, "soka");

        if ($close != true){
            $type = "true";
            $message_type = 1;
        }
    }

    $response .= $options;

    if (strlen($data['ussd_string']) > 1){
        $data['ussd_string'] = 1;
    }

    if (!empty($response) && $append){
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);
    }

    if ($close == true){
        close_session($data['msisdn'], $data['session_id']);
    }

    return [$response, $type, $message_type];
}

function get_games_tables($game = null){
    $array = [
        "predictor" => [
            "round" => null,
            "teams" => "mtncorrectscore_teams",
            "picks" => "mtnoutcomepredictor_picks",
            "selections" => "mtnoutcomepredictor_picks_selections",
        ],
        "correct_score" => [
            "round" => "mtncorrectscore_rounds",
            "teams" => "mtncorrectscore_teams",
            "picks" => "mtncorrectscore_picks",
            "selections" => null,
        ],
        "total_goals" => [
            "round" => null,
            "teams" => "mtncorrectscore_teams",
            "picks" => "mtncorrectgoals_picks",
            "selections" => "mtncorrectgoals_picks_selections",
        ],
        "soka" => [
            "round" => null,
            "teams" => "mtncorrectscore_teams",
            "picks" => "soka_picks",
            "selections" => "soka_number_selections",
        ]
    ];

    return $game? ($array[$game] ?? null) : $array;
}

function is_sport_score($value){
    $pattern = '/^\d+\s*-\s*\d+$/';
    return preg_match($pattern, trim($value));
}

function get_matches($game, $limit) {
    global $conn;

    $tables = get_games_tables($game);

    if (!$tables){
        return [];
    }

    $table = $tables['teams'];

    if (!$table){
        return [];
    }

    $sql = "SELECT id, CONCAT(teamaname, ' vs ', teambname) AS name FROM {$table} WHERE DATE(fixmdate) = DATE(NOW()) ORDER BY id DESC LIMIT {$limit}";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(1);

    if (!$rows || empty($rows)) {
        return [];
    }
    return $rows;
}

function handle_user_pick($msisdn, $session_id, $user_option, $ussd_string, $key, $type, $matches_array){
    global $conn;

    if (empty($matches_array)) return false;
    
    $match = $matches_array[$key];

    if (empty($match)) return false;

    $id = $match['id'] ?? null;
    $name = $match['name'] ?? null;
    if (!$id) return false;

    $stmt = $conn->prepare("SELECT id, fixtures FROM ussd_manager WHERE msisdn = ? AND session_id = ? AND status = 'open' ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ss", $msisdn, $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows < 1){
        return false;
    }else{
        $row = $result->fetch_assoc();
        $array = json_decode(($row['fixtures'] ?? "[]"), true);
        $array[] = [
            "id" => $id,
            "name" => $name,
            "type" => $type,
            "value" => $user_option,
            "ussd" => $ussd_string,
        ];

        $json = json_encode($array);

        $stmt = $conn->prepare("UPDATE ussd_manager SET fixtures = ? WHERE id = ?");
        $stmt->bind_param("si", $json, $row['id']);
        if ($stmt->execute()){
            return true;
        }
    }
}

function append_response($msisdn, $session_id, $ussd, $response){
    global $conn;
    $stmt = $conn->prepare("SELECT id, responses FROM ussd_manager WHERE msisdn = ? AND session_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ss", $msisdn, $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows < 1){
        return false;
    }else{
        $row = $result->fetch_assoc();
        $array = json_decode(($row['responses'] ?? "[]"), true);
        $array[] = [
            "ussd_string" => $ussd,
            "response" => $response,
            "time" => time(),
        ];

        $json = json_encode($array);

        $stmt = $conn->prepare("UPDATE ussd_manager SET responses = ? WHERE id = ?");
        $stmt->bind_param("si", $json, $row['id']);
        if ($stmt->execute()){
            return true;
        }
    }
}

function get_default_type_options($type){
    $array = [
        "soka" => ["1-0", "2-0", "3-0", "2-1", "3-1", "3-2", "Any Other Score", "0-0", "Draw"],
        "predictor" => ["Home", "Draw", "Away"],
        "soka half" => ["Home", "Draw", "Away"],
        "soka corners" => ["0-7", "8", "9", "10", "11+"],
    ];

    return $array[strtolower($type)] ?? [];
}

function handleSubmit($data, $user_input, $max, $count, $checker, $append, $type){
    $response = "";
    $options = "";
    $close = false;
    $sublim = substr($checker, ($max + 1));

    if ($max == $count){
        $response = "Submit your entry?\n";
        $options = "1. Yes\n2. Cancel";
    }else {
        if (in_array($user_input, [1, 2])){
            if ($user_input == 1){
                $sublim .= $data['debited'] == true || $data['is_initial'] == false? "1" : "";
                if (strlen($sublim) == 1){
                    if (in_array($type, ['soka'])){
                        $response = "To submit your entry,\nsubscription is required.\nEntry fee: N100\n";
                    }else{
                        $response = "To submit your entry,\nsubscription is required.\nStart 1-day trial(N0)\nThen N100/day auto-renew\n";
                    }
                    $options = "1. Continue\n2.Cancel";
                    $append = true;
                }else if (strlen($sublim) == 2){
                    $msisdn = $data['msisdn'] ?? null;
                    $session_id = $data['session_id'] ?? null;
                    $game = $data['game'] ?? null;
                    $winnings = $data['winnings'] ?? 0;
                    $amount = $data['amount'] ?? 100;

                    if (!$msisdn || !$session_id || !$game){
                        $response = "Something went wrong with the session. Please try again.";
                    }else{
                        $fixtures = get_column("fixtures", "ussd_manager", ["msisdn" => $msisdn, "session_id" => $session_id]);

                        if (!$fixtures){
                            $response = "Something went wrong trying to retrieve fixtures. Please try again.";
                        }else{
                            $array = json_decode(($fixtures ?? '[]'), true);

                            if (!$array || empty($array)){
                                $response = "Something went wrong trying to process fixtures. Please try again.";
                            }else{
                                $ticketNo = generate_ticket_number($msisdn);

                                $ticket_data = [
                                    "origin" => "ussd",
                                    "game_type" => $game,
                                    "round" => date("Y-m-d"),
                                    "ticket_number" => $ticketNo,
                                    "msisdn" => $msisdn,
                                    "session_id" => $session_id,
                                    "amount" => $amount,
                                    "winnings" => $winnings,
                                    "status" => "pending",
                                    "accepted" => $data['debited']? 1 : 0,
                                    "fixtures" => json_decode($fixtures, true),
                                ];

                                list($status, $ticket_id) = create_ticket($ticket_data); 
                                

                                if (!$status){
                                    $response = "Something went wrong trying to create ticket. Please try again.";
                                }else{
                                    $msg = generate_ticket_message($ticket_data['ticket_number'], $ticket_data['fixtures'], $game);
                                    $token = $data['pisi_token'];

                                    if ($data['debited'] == true){
                                        $response = "Congratulations. Bet placed successfully. Your betslip will be sent via SMS shortly.";
                                        clear_debited($data['debited_id'] ?? null, $game);
                                        send_sms($token, $msisdn, $msg, $data['sms_psipid']);
                                        has_bet($data['msisdn'], $data['session_id'], true, $game, $ticketNo);
                                    }else{
                                        subscribe($token, $msisdn, $data['psipid']);
                                        if (in_array($type, ['soka'])){
                                            $response = "Processing request...\nYou will receive a prompt for {$game} shortly. Accept to validate bet. Thank you.";
                                        }else{
                                            $response = "You will receive a prompt for {$game} shortly. Accept to validate bet. Thank you.";
                                        }
                                        send_sms($token, $msisdn, $msg, $data['sms_psipid']);
                                        has_bet($data['msisdn'], $data['session_id'], true, $game, $ticketNo);
                                    }
                                }
                            }
                        }
                    }

                    $close = true;
                }
            }else if ($user_input == 2){
                $response = "Cancelled successfully";
                $close = true;
            }  
        }else{
            $response = "Invalid option.\nSubmit your entry?\n";
            $options = "1. Yes\n2.Cancel";
        }
    }

    $response .= $options;

    return [$response, $close, $append];
}

function create_ticket($data){
    global $conn;
    $fixtures = $data['fixtures'] ?? null;

    if (!$fixtures || empty($fixtures)) return false;

    unset($data['fixtures']);

    $conn->begin_transaction();
    $ticket_id = insert("mtn_tickets", $data);

    if (!$ticket_id){
        $conn->rollback();
        return [false, null];
    }

    if (!create_selections($ticket_id, $fixtures)){
        $conn->rollback();
        return [false, null];
    }

    $conn->commit();
    return [true, $ticket_id];
}

function create_selections($ticket_id, $fixtures){
    if (!$fixtures || empty($fixtures)) return false;

    $data = [];
    foreach ($fixtures as $fixture){
        $data[] = [
            "ticket_id" => $ticket_id,
            "fixture" => $fixture['name'],
            "value" => $fixture['value'],
        ];
    }

    if (!insert_multiple("mtn_ticket_selections", $data)) return false;

    return true;
}

function generate_ticket_message($ticket_id, $fixtures, $game){
    $count = 0;
    foreach ($fixtures as $row) {
        $count++;
        $fixture = $row['name'];
        $value   = $row['value'];

        $lines[] = "{$count}.".str_ireplace("vs.", "-", $fixture)." ({$value})";
    }

    $msg = get_promotional_message($game);

    return "Bet Placed!\nSlip No: #{$ticket_id}\nGame: {$game}\nDate: ".date("d-m-Y")."\nTime: ". date("h:i") ."\nSelections:\n". implode("\n", $lines).($msg?"\n\n{$msg}":"");
}

function generate_ticket_number($msisdn) {
    $msisdn = preg_replace('/\D/', '', $msisdn);

    $msisdn_extract = substr($msisdn, -4);

    $time_extract = microtime(true) * 10000;

    $random_extract = random_int(1000, 9999);

    $raw = "{$msisdn_extract}{$time_extract}{$random_extract}";

    $number = strtoupper(substr(hash('sha256', $raw), 0, 8));

    return $number;
}

function handle_predictor($data, $base_ussd){
    $response = "Something went wrong";
    $type = "false";
    $message_type = 2;

    if ($data['checker'] == $base_ussd){
        $options = "1. Predictor - N1M\n2. Total Goals - N1M\n3. Correct Score - N1M";
        $response = "Choose Game:\n".$options;
        $type = "true";
        $message_type = 1;
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);
        return [$response, $type, $message_type];
    }

    $sublim = (string) preg_replace('/^'.preg_quote($base_ussd, '/').'/', "", $data['checker']);

    if (strpos($sublim, "1") === 0){
        list($response, $type, $message_type) = handle_predictor_type(array_merge($data, ["sublim" => $sublim, "name" => "Predictor", "winnings" => 1000000]), 1, 6);
    }else if (strpos($sublim, "2") === 0){
        list($response, $type, $message_type) = handle_total_goals_type(array_merge($data, ["sublim" => $sublim, "name" => "Total Goals", "winnings" => 1000000]), 2, 6);
    }else if (strpos($sublim, "3") === 0){
        list($response, $type, $message_type) = handle_correct_score_type(array_merge($data, ["sublim" => $sublim, "name" => "Correct Score", "winnings" => 1000000]), 3, 6);
    }

    return [$response, $type, $message_type];
}

function handle_soka($data, $base_ussd, $max = null){
    $response = "Something went wrong";
    $type = "false";
    $message_type = 2;

    if ($data['checker'] == $base_ussd){
        $options = "1. Soka 4 - N2M\n2. Soka 6 - N3M\n3. Soka 8 - N10M\n4. Soka Half - N2M\n5. Soka Corners - N2M";
        $response = "Choose Soka Game:\n".$options;
        $type = "true";
        $message_type = 1;
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);
        return [$response, $type, $message_type];
    }

    $sublim = (string) preg_replace('/^'.preg_quote($base_ussd, '/').'/', "", $data['checker']);

    if (strpos($sublim, "1") === 0){
        list($response, $type, $message_type) = handle_soka_type(array_merge($data, ["sublim" => $sublim, "winnings" => 2000000]), 1, 4);
    }else if (strpos($sublim, "2") === 0){
        list($response, $type, $message_type) = handle_soka_type(array_merge($data, ["sublim" => $sublim, "winnings" => 3000000]), 2, 6);
    }else if (strpos($sublim, "3") === 0){
        list($response, $type, $message_type) = handle_soka_type(array_merge($data, ["sublim" => $sublim, "winnings" => 10000000]), 3, 8);
    }else if (strpos($sublim, "4") === 0){
        list($response, $type, $message_type) = handle_soka_type_1(array_merge($data, ["sublim" => $sublim, "winnings" => 2000000]), 4, 8);   
    }
    else if (strpos($sublim, "5") === 0){
        list($response, $type, $message_type) = handle_soka_type_2(array_merge($data, ["sublim" => $sublim, "winnings" => 2000000]), 5, 6);   
    }

    return [$response, $type, $message_type];
}

function handle_others($data, $base_ussd, $max = null){
    $response = "Something went wrong";
    $type = "false";
    $message_type = 2;

    if ($data['checker'] == $base_ussd){
        $options = "";
        $response = "Coming soon...";
        $type = "false";
        $message_type = 2;
        append_ussd($data['msisdn'], $data['session_id'], $data['ussd_string']);
        close_session($data['msisdn'], $data['session_id']);
        return [$response, $type, $message_type];
    }
    close_session($data['msisdn'], $data['session_id']);
    return [$response, $type, $message_type];
}

function insert_multiple($table, $rows) {
    global $conn;

    if (empty($rows)) {
        throw new Exception("Insert data cannot be empty");
    }

    $columns = array_keys($rows[0]);

    $row_placeholder = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';

    $placeholders = implode(',', array_fill(0, count($rows), $row_placeholder));

    $sql = "INSERT INTO {$table} (" . implode(',', $columns) . ") VALUES {$placeholders}";

    try{
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $types = '';
        $values = [];

        foreach ($rows as $row) {
            foreach ($columns as $col) {
                $value = $row[$col] ?? null;

                if (is_bool($value)) {
                    $value = (int)$value;
                }

                if (is_int($value)) {
                    $types .= 'i';
                } elseif (is_float($value)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }

                $values[] = $value;
            }
        }

        $stmt->bind_param($types, ...$values);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $stmt->affected_rows;
    }catch(Exception $e){
        return false;
    }
}

function insert($table, $data) {
    global $conn;

    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');

    $sql = "INSERT INTO {$table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";

    try{
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $types = '';
        $values = [];

        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } else if (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }

        $stmt->bind_param($types, ...$values);
 
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    
        return $stmt->insert_id;
    }
    catch(Exception $e){
        return false;
    }
}

function subscribe($token, $msisdn, $psipid){
    $curl = curl_init();
    $url = PISI_BASE_URL.'/v1/subscription/outbound/create';

    $trxid = bin2hex(random_bytes(8));

    $payload = [
        "pisipid" => $psipid,
        "msisdn"  => $msisdn,
        "channel" => "USSD",
        "trxid"   => $trxid
    ];

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'vaspid: '.VASPID,
            'pisi-authorization-token: Bearer ' . $token,
            'Content-Type: application/json'
        ],
    ]);

    $response = curl_exec($curl);

    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        return false;
    }

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $decoded = json_decode($response, true);

    if ($httpCode !== 200) {
        return false;
    }

    if (isset($decoded['status']) && $decoded['status'] === 'success') {
        return true;
    }

    return false;
}

function send_sms($token, $msisdn, $message, $psipid){
    $url = PISI_BASE_URL.'/v1/sms/outbound/send';
    $trans_sms = 'sms'. uniqid().$msisdn;

    $payload = [
        "pisisid" => $psipid,
        "msisdn" => $msisdn,
        "message" => $message,
        "trxid" => $trans_sms,
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'pisi-authorization-token: Bearer '.$token.'',
        'vaspid: '.VASPID,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);

        if (isset($data['success']) && $data['success'] == true) {
            return true;
        }
    }

    return false;  
}

function get_column($column, $table, $cond_array){
    global $conn;

    $sql = "SELECT {$column} FROM {$table} WHERE 1=1";

    $where = "";

    foreach ($cond_array ?? [] as $key => $datum){
        $where .= " AND {$key} = ?";
    }

    $sql .= "{$where} LIMIT 1";

    $types = '';
    $values = [];

    foreach ($cond_array ?? [] as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
        $values[] = $value;
    }

    $stmt = $conn->prepare($sql);
    if (!empty($values)) {
        $stmt->bind_param($types, ...$values);
    }
    $stmt->execute();

    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    if (!$row || !($row[$column] ?? null)){
        return null;
    }

    return $row[$column];
}

function close_session($msisdn, $session_id){
    global $conn;
    $stmt = $conn->prepare("UPDATE ussd_manager SET status = 'closed' WHERE msisdn = ? AND session_id = ?");
    $stmt->bind_param("si", $msisdn, $session_id);
    $stmt->execute();
}

function create_session($msisdn, $session_id, $ussd, $debited){
    global $conn;
    $debited = $debited? 1 : 0;
    $stmt = $conn->prepare("SELECT id, ussd FROM ussd_manager WHERE msisdn = ? AND session_id = ? AND status = 'open' ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ss", $msisdn, $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows < 1){
        $stmt = $conn->prepare("UPDATE ussd_manager SET status = 'closed' WHERE msisdn = ?");
        $stmt->bind_param("s", $msisdn);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO ussd_manager (msisdn, session_id, ussd, status, debited, datetime) VALUES (?, ?, ?, 'open', ?, NOW())");
        $stmt->bind_param("sssi", $msisdn, $session_id, $ussd, $debited);
        if ($stmt->execute()){
            return 1;
        }
    }else{
        $row = $result->fetch_assoc();
        if ($row['ussd'] != $ussd){
            $stmt = $conn->prepare("UPDATE ussd_manager SET ussd = ? WHERE id = ?");
            $stmt->bind_param("si", $ussd, $row['id']);
            if ($stmt->execute()){
                return 1;
            }
        }else{
            return 1;
        }
    }
    return 0;
}

function get_ussd($msisdn, $session_id){
    global $conn;
    $stmt = $conn->prepare("SELECT ussd FROM ussd_manager WHERE msisdn = ? AND session_id = ? AND status = 'open' ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ss", $msisdn, $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows < 1){
        return null;
    }else{
        $row = $result->fetch_assoc();
        return $row['ussd'];
    }
}

function has_bet($msisdn, $session_id, $has_bet = true, $game = null, $ticketNo = null){
    global $conn;

    $has_bet = $has_bet? 1 : 0;
    $stmt = $conn->prepare("UPDATE ussd_manager SET has_bet = ?, game = ?, ticket_number = ? WHERE msisdn = ? AND session_id = ?");
    $stmt->bind_param("issss", $has_bet, $game, $ticketNo, $msisdn, $session_id);
    if ($stmt->execute()){
        return true;
    }

    return false;
}

function has_bet_today($msisdn, $game) {
    global $conn;

    $today = date('Y-m-d');

    $sql = "SELECT id FROM ussd_manager WHERE msisdn = ? AND game = ? AND has_bet = 1 AND debited = 1 AND DATE(datetime) = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $msisdn, $game, $today);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return true; 
    }

    return false;
}

function set_initial($msisdn, $game){
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM ussd_manager WHERE msisdn = ? AND has_bet = 1 AND game = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ss", $msisdn, $game);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows < 1){
        return true;
    }

    return false;
}

function clear_debited($id, $game){
    global $conn;
    if (!$id) return;

    $table = "subscriptions";

    if (stripos($game, "Soka") !== FALSE){
        $table = "soka_subscriptions";
    }

    $stmt = $conn->prepare("UPDATE {$table} SET usestat = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

function modify_service_name($service){
    if (in_array($service, ["Total Goals", "Predictor", "Correct Score", "Picks", "Trivia"])){
        $service = "Yello {$service}";
    }
    
    return $service;
}

function set_debited($msisdn, $game){
    global $conn;

    $table = "subscriptions";

    if (stripos($game, "Soka") !== FALSE){
        $table = "soka_subscriptions";
    }

    $service = modify_service_name($game);

    $stmt = $conn->prepare("SELECT id FROM {$table} WHERE msisdn = ? AND service = ? AND usestat = 1 AND status = 1 ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ss", $msisdn, $service);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0){
        $row = $result->fetch_assoc();
        return [true, $row['id']];
    }

    return false;
}

function append_ussd($msisdn, $session_id, $ussd){
    global $conn;
    $stmt = $conn->prepare("SELECT id, ussd FROM ussd_manager WHERE msisdn = ? AND session_id = ? AND status = 'open' ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ss", $msisdn, $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows < 1){
        return null;
    }else{
        $row = $result->fetch_assoc();
        $new_ussd = $row['ussd'].$ussd;
        $stmt = $conn->prepare("UPDATE ussd_manager SET ussd = ? WHERE id = ?");
        $stmt->bind_param("si", $new_ussd, $row['id']);
        if ($stmt->execute()){
            return $new_ussd;
        }
    }
    return null;
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

function dd($v){
    print_r($v);
    die();
}

/*
return [
    ["id" => 1, "name" => "Chelsea vs. Tottenham"],
    ["id" => 2, "name" => "Man City vs. Sunderland"],
    ["id" => 3, "name" => "Arsenal vs. PSG"],
    ["id" => 4, "name" => "Everton vs. Barcelona"],
    ["id" => 5, "name" => "Bayern Munchen vs. Real Madrid"],
    ["id" => 6, "name" => "Stone City vs. MLS"],
    ["id" => 7, "name" => "Ice City vs. ISeeU"],
    ["id" => 8, "name" => "Nigeria vs. Ghana"],
];
*/