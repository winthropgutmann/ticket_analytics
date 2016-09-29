<?php
    require_once('api-keys.php');
    require_once('database-login.php');
    
    function get_all_games($num, $event_id, $match_up, $venue, $date){
        $teams = explode(" at ", $match_up);
        $home = trim($teams[1]);
        $away = trim($teams[0]);
        if (strpos($away, "Preseason:") !== false){
            $away = trim(str_replace("Preseason:", "", $away));
        }

        $insert_date = date("Y-m-d h:i:s");

        if(($num+1)%1000 == 0 || ($num) == 0)
            $sql_query = "insert into event(
                 event_id
                ,event_title
                ,event_date
                ,home_team
                ,away_team
                ,venue_name
                ,game_type
            )
            values
            (\"{$event_id}\", \"{$match_up}\", \"". prepare_date($date) ."\", \"{$home}\", \"{$away}\", \"{$venue}\", \"" . game_type($match_up) ."\")";
        else
            $sql_query = $query . "\n\t,(\"{$event_id}\", \"{$match_up}\", \"" . prepare_date($date) . "\", \"{$home}\", \"{$away}\", \"{$venue}\", \"" . game_type($match_up) ."\")";

        return $sql_query;
    }

    function exec_curl($team){
        $client = client_key();
        $client_secret = client_secret();
        $ch = curl_init(); 
        // curl_setopt($ch, CURLOPT_URL, "https://api.seatgeek.com/2/events/3375100?client_id={$client}&client_secret={$client_secret}&format=json"); 
        curl_setopt($ch, CURLOPT_URL, "https://api.seatgeek.com/2/events?performers.slug={$team}&client_id={$client}&client_secret={$client_secret}&format=json&per_page=5000&sort=datetime_utc.asc"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_FAILONERROR, true); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch); 
        curl_close($ch); 
        return json_decode($output,true);
    }

    $result = exec_query("select team_name from team limit 1");
    foreach(range(0,sizeof($result)) as $j){
        print_r($result);
    }