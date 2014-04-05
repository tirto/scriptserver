delete from aaa_tag_varmap where page_id in (select page_id from aaa_tag_pages where rsid = 'aca-ncnu-prod');

delete from aaa_tag_pages where rsid = 'aca-ncnu-prod';

commit;

truncate table aaa_tag_pages_stg;  

truncate table aaa_tag_varmap_stg;

