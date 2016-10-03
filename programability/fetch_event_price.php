<?php
    require_once("functions.php");
    $minutes = date("i");

    function insert_event_price($event_id, $lowest, $avg, $highest, $score, $query){

        if($query == ""){
            $sql_query = "insert into event_price(
                 event_id
                ,lowest_price
                ,avg_price
                ,highest_price
                ,score
                ,insert_date
            )
            values
            (\"{$event_id}\", \"{$lowest}\", \"{$avg}\", \"{$highest}\", \"{$score}\", now())";
        }else{
            $sql_query = $query . "\n\t,(\"{$event_id}\", \"{$lowest}\", \"{$avg}\", \"{$highest}\", \"{$score}\", now())";
        }
        return $sql_query;
    }

    if($minutes == "00" || $minutes == "15" || $minutes == "30" || $minutes == "45"){
        $todays_events = exec_query("select event_id as event_id from events where event_date = CURDATE()");
        $three_day_events = exec_query("select event_id as event_id from events where DATEDIFF(CURDATE(), event_date) between -3 and -1");
        $all_other_events = exec_query("select event_id as event_id from events where DATEDIFF(CURDATE(), event_date) <= -4");;
        $todays_query = "";
        $three_day_query = "";
        $other_query = "";
        


        print_r("Todays\n");

        if(sizeof($todays_events) != 0){
            if($minutes == "15" || $minutes == "30" || $minutes == "45"){
                foreach(range(0, sizeof($todays_events)-1) as $a){
                    $event = fetch_event($todays_events[$a]["event_id"]);
                    $todays_query = insert_event_price($event["id"], $event["stats"]["lowest_price"], $event["stats"]["average_price"], $event["stats"]["highest_price"], $event["score"], $todays_query);
                    print_r($event["id"] . " " . $event["stats"]["lowest_price"] . " " . $event["stats"]["average_price"] . " " . $event["stats"]["highest_price"] . " " . $event["score"] . "\n");
                }
                $result = exec_query($todays_query);
                if($result)
                    print_r("todays_query success!\n");
            }else{
                    print_r("not the right time: today\n");
                }
        }else{
            print_r("no today events\n");
        }

        print_r("Threeday + today\n");

        if(sizeof($todays_events) != 0 || sizeof($three_day_events) != 0){
            if($minutes == "00"){
                if(sizeof($todays_events) != 0 && sizeof($three_day_events) != 0)
                    $three_day_events[] = $todays_events;
                foreach(range(0, sizeof($three_day_events)-1) as $a){
                    $event = fetch_event($three_day_events[$a]["event_id"]);
                    print_r($event["id"] . " " . $event["stats"]["lowest_price"] . " " . $event["stats"]["average_price"] . " " . $event["stats"]["highest_price"] . " " . $event["score"] . "\n");
                    $three_day_query = insert_event_price($event["id"], $event["stats"]["lowest_price"], $event["stats"]["average_price"], $event["stats"]["highest_price"], $event["score"], $three_day_query);
                }
                $result = exec_query($three_day_query);
                if($result)
                    print_r("three_day_query success!\n");
            }else{
                    print_r("not the right time: threeday + today\n");
                }
        }else{
            print_r("no threeday events\n");
        }


        print_r("other\n");

        if(sizeof($all_other_events) != 0){
            if(date("h:i") == "00:00"){
                foreach(range(0, sizeof($all_other_events)-1) as $a){
                    $event = fetch_event($all_other_events[$a]["event_id"]);
                    $other_query = insert_event_price($event["id"], $event["stats"]["lowest_price"], $event["stats"]["average_price"], $event["stats"]["highest_price"], $event["score"], $other_query);
                    print_r($event["id"] . " " . $event["stats"]["lowest_price"] . " " . $event["stats"]["average_price"] . " " . $event["stats"]["highest_price"] . " " . $event["score"] . "\n");
                }
                $result = exec_query($other_query);
                if($result)
                    print_r("other_query success!\n");
            }else{
                    print_r("not the right time: other\n");
                }
        }else{
            print_r("no other events\n");
        }


        print_r("\n \nTASK: SUCCESS\n");
    }else{
        print_r("Not time to run anything!\n");
    }
?>