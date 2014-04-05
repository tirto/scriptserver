load data local infile 'location.csv' into table aaa_tag_location 
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
(loc, location_name, parent,type,state,channel)
