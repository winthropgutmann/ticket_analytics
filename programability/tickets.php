<?php 
    require_once('api-keys.php');
    require_once('database-login.php');

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

    function game_type($event_title){

        if(strpos($event_title, "Preseason:") !== false){
            return "Preseason";
        }elseif(strpos($event_title, "Playoff:") !== false){
            return "Playoff";
        }else{
            return "Regular";
        }
    }

    function build_query($num, $event_id, $match_up, $venue, $date, $score, $low, $avg, $high, $query){
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
                ,lowest_price
                ,avg_price
                ,highest_price
                ,score
                ,insert_date
                ,game_type
            )
            values
            (\"{$event_id}\", \"{$match_up}\", \"". prepare_date($date) ."\", \"{$home}\", \"{$away}\", \"{$venue}\", {$low}, {$avg}, {$high}, {$score}, \"{$insert_date}\", \"" . game_type($match_up) ."\")";
        else
            $sql_query = $query . "\n\t,(\"{$event_id}\", \"{$match_up}\", \"" . prepare_date($date) . "\", \"{$home}\", \"{$away}\", \"{$venue}\", {$low}, {$avg}, {$high}, {$score}, \"{$insert_date}\", \"" . game_type($match_up) ."\")";

        return $sql_query;
    }

    function exec_curl(){
        $client = client_key();
        $client_secret = client_secret();
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
        return json_decode($output,true);
    }
?>