<?php
    create table event(
         event_id int
        ,event_title varchar(255)
        ,event_date datetime
        ,home_team varchar(75)
        ,away_team varchar(75)
        ,venue_name varchar(255)
        ,lowest_price int
        ,avg_price int
        ,highest_price int
        ,score float
    );
?>