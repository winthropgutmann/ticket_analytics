<?php
    create table event(
         event_id int not null
        ,event_title varchar(255) not null
        ,event_date datetime not null
        ,home_team varchar(75) not null
        ,away_team varchar(75) not null
        ,venue_name varchar(255) not null
        ,lowest_price int not null
        ,avg_price int not null
        ,highest_price int not null
        ,score float not null
        ,insert_date datetime not null
        ,game_type varchar(20) not null
    );

// I dont think we want a primary key on this table, however a clustered index on event_id and insert_date might prove useful

/*
need to figure out some way of updating multiple columns with a single trigger
 or better yet find a clever solution to only insert new events that dont currently
 exist in the table within the same hour.

The idea here is that the the event 3375100 (bruins v habs) is loaded into the table
so we wouldnt want to load the same event when processing the habs games. Maybe some sort
of matrix would be useful. Example:
---------------------------------------------------------
| TEAM   | Bruins | Rangers | Penguins | Devils | Kings |
|--------------------------------------------------------
|Bruins  |    o   |    X    |     X    |    X   |   X   |
|--------------------------------------------------------
|Rangers |    o   |    o    |     X    |    X   |   X   |
|--------------------------------------------------------
|Penguins|    o   |    o    |     o    |    X   |   X   |
|--------------------------------------------------------
|Devils  |    o   |    o    |     o    |    o   |   X   |
|--------------------------------------------------------
|Kings   |    o   |    o    |     o    |    o   |   o   |
|--------------------------------------------------------

*/
    create trigger ins_year
    before insert on event
    for each row set new.is_year = year(now());

    create trigger ins_month
    before insert on event
    for each row set new.is_month = month(now());

    create trigger ins_day
    before insert on event
    for each row set new.is_year = day(now());

    create trigger ins_hour
    before insert on event
    for each row set new.is_year = hour(now());
?>