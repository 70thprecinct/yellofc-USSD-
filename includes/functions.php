<?php
function handle_predictor_type($data, $base_sublim, $max){
    $response = "";
    $type = "false";
    $message_type = 2;
    $options = "";
    $append = false;
    $close = false;
    $is_valid_option = false;

    if ($data['sublim'] == $base_sublim){
        $response .= "Welcome to Predictor\nPick match outcomes for\n{$max} matches to win N".number_format($data['winnings'], 0)."\n";
    }

    if (strlen($data['ussd_string']) > 1){
        $data['sublim'] = preg_replace('/'.preg_quote($data['ussd_string'], '/').'$/', "0", $data['sublim']);
    }

    $count = strlen($data['sublim']) - 1;
    $user_input = $data['ussd_string'];
    $current_option = $count + 1;
    $type_options = get_default_type_options("predictor");
    $matches_array = get_matches($max);

    if ($count <= $max){
        if (in_array($user_input, [1, 2, 3])){
            if ($data['sublim'] != $base_sublim){
                switch ($user_input){
                    case 1: $user_input = "Home"; break;
                    case 2: $user_input = "Draw"; break;
                    case 3: $user_input = "Away"; break;
                }

                handle_user_pick($data['msisdn'], $data['session_id'], $user_input, $data['ussd_string'], ($count - 1), $matches_array);
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
        $matches = array_column($matches_array, "name");

        if (empty($matches)){
            $response = "No matches available for today.";
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
    }else{
        list($response, $close, $append) = handleSubmit($user_input, $max, $count, $data['sublim'], $append, "predictor");

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

    if ($data['sublim'] == $base_sublim){
        $response .= "Welcome to Total Goals\nEnter total goals scored\nin each match to win N".number_format($data['winnings'], 0)."\n";
    }

    if (strlen($data['ussd_string']) > 1){
        $data['sublim'] = preg_replace('/'.preg_quote($data['ussd_string'], '/').'$/', "0", $data['sublim']);
    }

    $count = strlen($data['sublim']) - 1;
    $user_input = $data['ussd_string'];
    $current_option = $count + 1;
    $type_options = get_default_type_options("total_goals");
    $matches_array = get_matches($max);

    if ($count <= $max){
        if ($data['sublim'] != $base_sublim){
            if (is_numeric($user_input)){
                handle_user_pick($data['msisdn'], $data['session_id'], $user_input, $data['ussd_string'], ($count - 1), $matches_array);
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
        $matches = array_column($matches_array, "name");

        if (empty($matches)){
            $response = "No matches available for today.";
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
    }else{
        list($response, $close, $append) = handleSubmit($user_input, $max, $count, $data['sublim'], $append, "total_goals");

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

function is_sport_score($value){
    $pattern = '/^\d+\s*-\s*\d+$/';
    return preg_match($pattern, trim($value));
}

function handle_correct_score_type($data, $base_sublim, $max){
    $response = "";
    $type = "false";
    $message_type = 2;
    $options = "";
    $append = false;
    $close = false;
    $is_valid_option = false;

    if ($data['sublim'] == $base_sublim){
        $response .= "Welcome to Correct Score\nEnter final scores for each match\n(e.g 2-1, 0-0)\nWin N".number_format($data['winnings'], 0)."\n";
    }

    if (is_sport_score($data['ussd_string'])){
        $data['sublim'] = str_replace($data['ussd_string'], "0", $data['sublim']);
    }

    if (strlen($data['ussd_string']) > 1){
        $data['sublim'] = preg_replace('/'.preg_quote($data['ussd_string'], '/').'$/', "0", $data['sublim']);
    }

    $count = strlen($data['sublim']) - 1;
    $user_input = $data['ussd_string'];
    $current_option = $count + 1;
    $type_options = get_default_type_options("correct_score");
    $matches_array = get_matches($max);

    if ($count <= $max){
        if ($data['sublim'] != $base_sublim){
            if (is_sport_score($user_input)){
                $user_input = str_replace(" ", "", $user_input);
                handle_user_pick($data['msisdn'], $data['session_id'], $user_input, $data['ussd_string'], ($count - 1), $matches_array);
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
        $matches = array_column($matches_array, "name");

        if (empty($matches)){
            $response = "No matches available for today.";
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
    }else{
        list($response, $close, $append) = handleSubmit($user_input, $max, $count, $data['sublim'], $append, "correct_score");

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

function get_matches($limit) {
    return [
        ["id" => 1, "name" => "Arsenal vs Manchester City"],
        ["id" => 2, "name" => "Real Madrid vs Barcelona"],
        ["id" => 3, "name" => "Bayern Munich vs Dortmund"],
        ["id" => 4, "name" => "Inter Milan vs AC Milan"],
        ["id" => 5, "name" => "Liverpool vs Chelsea"],
        ["id" => 6, "name" => "PSG vs Monaco"]
    ];

    global $conn;

    $query = "SELECT CONCAT(teamaname, ' vs ', teambname) AS fixture FROM mtncorrectscore_teams WHERE DATE(fixmdate) = DATE(NOW()) ORDER BY id DESC LIMIT {$limit}";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        error_log("Database Query Failed: " . mysqli_error($conn));
        return [];
    }

    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

    return array_column($data, 'fixture');
}

function handle_soka_type($data, $base_sublim, $max){
    $response = "";
    $type = "false";
    $message_type = 2;
    $options = "";
    $append = false;
    $close = false;
    $custom = false;

    if ($data['sublim'] == $base_sublim){
        $response .= "Welcome to Soka {$max}\nPick the scores for\n{$max} matches to win N".number_format($data['winnings'], 0)."\n";
    }

    if (is_sport_score($data['ussd_string'])){
        $data['sublim'] = str_replace($data['ussd_string'], "0", $data['sublim']);
    }

    if (strlen($data['ussd_string']) > 1){
        $data['sublim'] = preg_replace('/'.preg_quote($data['ussd_string'], '/').'$/', "0", $data['sublim']);
    }

    $count = strlen($data['sublim']) - 1;
    $user_input = $data['ussd_string'];
    $current_option = $count + 1;
    $type_options = get_default_type_options("soka");
    $matches_array = get_matches($max);

    if ($count <= $max){
        if ($data['sublim'] != $base_sublim){
            if ($user_input == 7){
                $response = "Please enter custom score (e.g 0-2, 3-9):\n";
                $custom = true;
                $type = "true";
                $message_type = 1;
            }else{
                $user_option = is_sport_score($user_input)? str_replace(" ", "", $user_input) : $type_options[$user_input - 1];
                $data['ussd_string'] = is_sport_score($user_input)? 7 : $data['ussd_string'];
                handle_user_pick($data['msisdn'], $data['session_id'], $user_option, $data['ussd_string'], ($count - 1), $matches_array);
                $append = true;
            }
        }else{
            $append = true;
        }
    }

    if ($count < $max){
        if (!$custom){
            $matches = array_column($matches_array, "name");

            if (empty($matches)){
                $response = "No matches available for today.";
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
        list($response, $close, $append) = handleSubmit($user_input, $max, $count, $data['sublim'], $append, "soka");

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

function handle_user_pick($msisdn, $session_id, $user_option, $ussd_string, $key, $matches_array){
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
            "ussd" => $ussd_string,
            "value" => $user_option,
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
    ];

    return $array[strtolower($type)] ?? [];
}

function handleSubmit($user_input, $max, $count, $checker, $append, $type){
    $response = "";
    $options = "";
    $close = false;
    $sublim = substr($checker, ($max + 1));

    if ($max == $count){
        $response = "Submit your entry?\n";
        $options = "1. Yes\n 2.Cancel";
    }else {
        if (in_array($user_input, [1, 2])){
            if ($user_input == 1){
                if (strlen($sublim) == 1){
                    if (in_array($type, ['soka'])){
                        $response = "To submit your entry,\n subscription is required.\n\nEntry fee: N100\n";
                    }else{
                        $response = "To submit your entry,\n subscription is required.\n\nStart 1-day trial(N0)\nThen N100/day auto-renew\n";
                    }
                    $options = "1. Continue\n 2.Cancel";
                    $append = true;
                }else if (strlen($sublim) == 2){
                    if (in_array($type, ['soka'])){
                        $response = "Processing request...\nAn SMS will be sent shortly. Reply 'YES' to confirm bet. Thank you.";
                    }else{
                        $response = "An SMS will be sent shortly. Reply 'YES' to confirm bet. Thank you.";
                    }
                    $close = true;
                }
            }else if ($user_input == 2){
                $response = "Cancelled successfully";
                $close = true;
            }  
        }else{
            $response = "Invalid option.\nSubmit your entry?\n";
            $options = "1. Yes\n 2.Cancel";
        }
    }

    $response .= $options;

    return [$response, $close, $append];
}

function handle_soka($data, $base_ussd, $max = null){
    $response = "Something went wrong";
    $type = "false";
    $message_type = 2;

    if ($data['checker'] == $base_ussd){
        $options = "1. Soka 4 - N2M\n2. Soka 6 - N3M\n3. Soka 8 - 10M";
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

function close_session($msisdn, $session_id){
    global $conn;
    $stmt = $conn->prepare("UPDATE ussd_manager SET status = 'closed' WHERE msisdn = ? AND session_id = ?");
    $stmt->bind_param("si", $msisdn, $session_id);
    $stmt->execute();
}

function create_session($msisdn, $session_id, $ussd){
    global $conn;
    $stmt = $conn->prepare("SELECT id, ussd FROM ussd_manager WHERE msisdn = ? AND session_id = ? AND status = 'open' ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ss", $msisdn, $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows < 1){
        $stmt = $conn->prepare("INSERT INTO ussd_manager (msisdn, session_id, ussd, status, datetime) VALUES (?, ?, ?, 'open', NOW())");
        $stmt->bind_param("sss", $msisdn, $session_id, $ussd);
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