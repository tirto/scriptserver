load data local infile 'market.csv' into table aaa_tag_market
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
(market, market_name, parent, canon_market_name)
