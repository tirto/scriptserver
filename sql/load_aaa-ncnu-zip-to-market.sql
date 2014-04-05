load data local infile 'aaa-ncnu-zip-to-market.csv' into table aaa_tag_zip_to_market 
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 lines
(zipcode, city, loc,parent,market)
