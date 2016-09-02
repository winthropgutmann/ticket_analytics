<?php 
    $json = exec_curl();
    $event_count = sizeof($json["events"]);
    $sql_query = "";

    foreach(range(0,$event_count-1) as $j)
    {       
        $sql_query = 
                    build_query(
                         $j//num
                        ,$json["events"][$j]["id"]//event_id
                        ,$json["events"][$j]["title"] //match_up
                        ,$json["events"][$j]["venue"]["name"] //venue
                        ,$json["events"][$j]["datetime_local"]//date
                        ,$json["events"][$j]["score"]//score
                        ,$json["events"][$j]["stats"]["lowest_price"]//low
                        ,$json["events"][$j]["stats"]["average_price"]//avg
                        ,$json["events"][$j]["stats"]["highest_price"]//high
                        ,$sql_query//appending query
                    );
        if(($j+1)%1000 == 0 || ($j+1) == $event_count){
            exec_query($sql_query);
        }
    }
    // if($json["events"][$j]["venue"]["name"] == "TD Garden")
    // print_r("Event ID  : " .$json["events"][$j]["id"] . "\n");
    // print_r("Team  ID  : " .$json["events"][$j]["performers"][0]["primary"] . "\n");
    // print_r("     Match up  : " .$json["events"][$j]["title"] . "\n");
    // print_r("     Venue     : " .$json["events"][$j]["venue"]["name"] . " " . $json["events"][$j]["venue"]["extended_address"] . "\n");
    // print_r("     Date      : " .$json["events"][$j]["datetime_local"] . "\n");
    // print_r("     Popularity: " .$json["events"][$j]["score"] . "\n");
    // print_r("     Avg Price : " .$json["events"][$j]["stats"]["average_price"] . "\n");
    // print_r("     Low Price : " .$json["events"][$j]["stats"]["lowest_price"] . "\n");
    // print_r("     Top Price : " .$json["events"][$j]["stats"]["highest_price"] . "\n");
    function prepare_date($date){
        return str_ireplace("T", " ", $date);
    }

    function exec_query($query){
        $servername = "localhost";
        $username = "root";
        $password = "abithiw2itb";
        $dbname = "tickets";
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        // print_r($query);
        if(!$conn)
        {
            die("Connection failed: " . mysqli_connect_error());
        }else{
            $result = mysqli_query($conn, $query);
            if(!$result){
                die("Query: {$query}\n\n\n" . mysqli_error($conn));
            }
            var_dump($result);
        }
    }

    function build_query($num, $event_id, $match_up, $venue, $date, $score, $low, $avg, $high, $query){
        $teams = explode(" at ", $match_up);
        $home = trim($teams[1]);
        $away = trim($teams[0]);
        if (strpos($away, "Preseason:") !== false){
            $away = trim(str_replace("Preseason:", "", $away));
        }

        if(($num+1)%1000 == 0 || ($num) == 0)
            $sql_query = "insert into event(
                 event_id
                ,event_title
                ,event_date
                ,home_team
                ,away_team
                ,venue_name
                ,lowest_price
                ,avg_price
                ,highest_price
                ,score
            )
            values
            (\"{$event_id}\", \"{$match_up}\", \"". prepare_date($date) ."\", \"{$home}\", \"{$away}\", \"{$venue}\", {$low}, {$avg}, {$high}, {$score})";
        else
            $sql_query = $query . "\n\t,(\"{$event_id}\", \"{$match_up}\", \"" . prepare_date($date) . "\", \"{$home}\", \"{$away}\", \"{$venue}\", {$low}, {$avg}, {$high}, {$score})";

        return $sql_query;    
    }

    function exec_curl(){
        $client = 'NTU3MTMyNnwxNDcyNjgxMzAx';
        $client_secret = 'ypO0cwm-VzN_8FZrMG1hV1zIilADcYFytnyHroNZ';
        $ch = curl_init(); 
        // curl_setopt($ch, CURLOPT_URL, "https://api.seatgeek.com/2/events/3375100?client_id={$client}&client_secret={$client_secret}&format=json"); 
        curl_setopt($ch, CURLOPT_URL, "https://api.seatgeek.com/2/events?performers.slug=boston-bruins&client_id={$client}&client_secret={$client_secret}&format=json&per_page=5000&sort=datetime_utc.asc"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_FAILONERROR, true); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch); 
        curl_close($ch); 
        return json_decode($output);
    }
?>