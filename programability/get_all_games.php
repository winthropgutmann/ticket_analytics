<?php
    require_once('functions.php');


    function game_type($event_title){

        if(strpos($event_title, "Preseason:") !== false){
            return "Preseason";
        }elseif(strpos($event_title, "Playoff:") !== false){
            return "Playoff";
        }else{
            return "Regular";
        }
    }


    function insert_event($count, $event_id, $title, $venue, $date, $query){
        if(strpos($title, " at ")){
            $teams = explode(" at ", $title);
            $home = trim($teams[1]);
            $away = trim($teams[0]);
            if (strpos($away, "Preseason:") !== false){
                $away = trim(str_replace("Preseason:", "", $away));
            }

            $insert_date = date("Y-m-d h:i:s");

            if($query == ""){
                $sql_query = "insert into events(
                     event_id
                    ,event_title
                    ,event_date
                    ,home_team
                    ,away_team
                    ,venue_name
                    ,game_type
                )
                values
                (\"{$event_id}\", \"{$title}\", \"". prepare_date($date) ."\", \"{$home}\", \"{$away}\", \"{$venue}\", \"" . game_type($title) ."\")";
            }else{
                $sql_query = $query . "\n\t,(\"{$event_id}\", \"{$title}\", \"" . prepare_date($date) . "\", \"{$home}\", \"{$away}\", \"{$venue}\", \"" . game_type($title) ."\")";
            }
        }else{
            $GLOBALS["warnings"]  +=1;
            write_to_log($GLOBALS["file"], "WARNING: [{$title}, {$event_id}] was unable to be processed!");
            return $query;
        }
        return $sql_query;
    }

    $date = getdate();
    $GLOBALS["file"] = fopen("../logs/fetch_games_{$date["year"]}_{$date["month"]}_{$date["mday"]}.txt", "a");
    $unique_events = array();
    $truncate = exec_query("truncate events");
    if($truncate)
        write_to_log($GLOBALS["file"], "WARNING: EVENTS TRUNCATED");
    $teams = exec_query("select team_name as team_name from teams");

    $count = 0;
    $GLOBALS["warnings"] = 0;
    $GLOBALS["errors"] = 0;
    $event_query = "";
    foreach(range(0, sizeof($teams)-1) as $t){
        write_to_log($GLOBALS["file"], $teams[$t]["team_name"]);
        $season = fetch_seasons($teams[$t]["team_name"]);
        foreach(range(0, sizeof($season["events"])-1) as $s){
            if(!in_array($season["events"][$s]["id"], $unique_events)){
                $unique_events[] = $season["events"][$s]["id"];
                $event_query = 
                    insert_event(
                         $count
                        ,$season["events"][$s]["id"]
                        ,$season["events"][$s]["title"]
                        ,$season["events"][$s]["venue"]["name"]
                        ,$season["events"][$s]["datetime_local"]
                        ,$event_query
                    );
                write_to_log($GLOBALS["file"], "Event ID: " . $season["events"][$s]["id"] . " Event Title: " .$season["events"][$s]["title"]);
            }
        }
    }
    write_to_log($GLOBALS["file"], "Executing SQL: \n \n" + $event_query);
    $result = exec_query($event_query);
    if($result){
        write_to_log($GLOBALS["file"], "SQL Executed Successfully!");
        write_to_log($GLOBALS["file"], "Task completed successfully! \n"
                    ."TASK Summary: {$date["weekday"]}, {$date["month"]} {$date["mday"]} {$date["year"]} {$date["hours"]}:{$date["minutes"]}:{$date["seconds"]}\n"
                    ."----------------------------- \n"
                    ."|ERRORS  : {$GLOBALS["errors"]} \n"
                    ."|WARNINGS: {$GLOBALS["warnings"]} \n"
                    ."----------------------------- \n \n \n"
            );
    }
    fclose($GLOBALS["file"]);
    print_r("\n SUCCESS \n");
?>