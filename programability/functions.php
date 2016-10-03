<?php 

    require_once('api-keys.php');
    require_once('database-login.php');

    function fetch_seasons($team){
        $client = client_key();
        $client_secret = client_secret();
        $ch = curl_init(); 
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

    function fetch_event($event_id){
        $client = client_key();
        $client_secret = client_secret();
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, "https://api.seatgeek.com/2/events/{$event_id}?client_id={$client}&client_secret={$client_secret}&format=json&sort=datetime_utc.asc"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_FAILONERROR, true); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch); 
        curl_close($ch); 
        return json_decode($output,true);
    }

    function prepare_date($date){
        return str_ireplace("T", " ", $date);
    }

    function write_to_log($file, $str){
        $str = "\n" . $str;
        fwrite($file, $str);
    }

    function exec_query($query){
        $db = database_login();
        $ret_result = array();
        $conn = mysqli_connect($db["servername"], $db["username"], $db["password"], $db["dbname"]);
        if(!$conn)
        {
            die("Connection failed: " . mysqli_connect_error());
        }else{
            mysqli_set_charset($conn,"utf8");
            $result = mysqli_query($conn, $query);
            if(!$result){
                die("Query: {$query}\n\n\n" . mysqli_error($conn));
                var_dump("query failsure: " . mysqli_error() . "\n\nresult: " . $result);
            }else{
                if(!is_object($result)){
                    return $result;
                }else{
                    while($row = mysqli_fetch_assoc($result)){
                        $ret_result[] = $row;
                    }
                }
            }
        }
        mysqli_free_result($result);
        mysqli_close($conn);
        return $ret_result;
    }

?>