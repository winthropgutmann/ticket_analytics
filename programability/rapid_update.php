<?php
    require_once('api-keys.php');
    require_once('database-login.php');

    function get_todays_games(){

        $sql_query = "select distinct(event_id) from event where DATE(event_date) = CURDATE()";
        $result = exec_query($sql_query);
        print_r($result);
    }
?>