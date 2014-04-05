load data local infile 'dart_input2.csv' into table aaa_tag_dart_stg
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 lines
(d_page_url,d_type, d_cat, d_tag_name, d_page_name)
