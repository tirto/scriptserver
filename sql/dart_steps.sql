SELECT * FROM `aaametrics`.`aaa_tag_pages_tags_stg`;

select count(*), tag_id, tag_name from aaa_tag_tags_stg;

select tag_id, tag_name from aaa_tag_tags_stg;

select count(*), page_id, tag_name from aaa_tag_dart_url_stg;

select page_id, tag_name from aaa_tag_dart_url_stg;

select page_id, tag_id, 0 from aaa_tag_dart_url_stg u, aaa_tag_tags_stg d where d.tag_name = u.tag_name;



commit;

rollback;

select * from aaa_tag_tags_stg;

select * from aaa_tag_pages_tags;

select count(*) from aaa_tag_dart_url_stg;

select count(*) from aaa_tag_tags_stg;

select * from aaa_tag_pages where page_id in (select page_id from aaa_tag_dart_url_stg);

delete from aaa_tag_pages where page_id in (select page_id from aaa_tag_dart_url_stg);

commit;

select * from aaa_tag_tags where tag_id in (select tag_id from aaa_tag_tags_stg);

delete from aaa_tag_tags where tag_id in (select tag_id from aaa_tag_tags_stg);

commit;

truncate table aaa_tag_dart_stg;

truncate table aaa_tag_tags_stg;

truncate table aaa_tag_dart_url_stg;


select * from aaa_tag_dart_stg;

select * from  aaa_tag_tags_stg;

select * from  aaa_tag_dart_url_stg;


insert into aaa_tag_pages_stg (page_id, partner_id, page_url, site_id, rsid) select page_id,  3, page_url, 1, 'aca-ncnu-prod' from aaa_tag_dart_url_stg;

commit;

select * from aaa_tag_pages_stg;

insert into aaa_tag_pages(page_id, partner_id, page_url, site_id, rsid, ga_id) select page_id, partner_id, page_url, site_id, rsid, 'UA-1870671-13' from aaa_tag_pages_stg;

select * from aaa_tag_pages;

commit;

select * from aaa_tag_tags_stg;

insert into aaa_tag_tags select * from aaa_tag_tags_stg;

commit;

select * from aaa_tag_tags;


select * from aaa_tag_pages_tags_stg;

truncate table aaa_tag_pages_tags_stg;

insert into aaa_tag_pages_tags_stg (page_id, tag_id, active) select page_id, tag_id, 0 from aaa_tag_dart_url_stg u, aaa_tag_tags_stg d where d.tag_name = u.tag_name;

insert into aaa_tag_pages_tags select * from aaa_tag_pages_tags_stg;

select * from aaa_tag_pages_tags_stg;

select tag_id from aaa_tag_pages_tags_stg where tag_id not in (select tag_id from aaa_tag_tags);

commit;

-- page name var mapping

SELECT * FROM `aaametrics`.`aaa_tag_varmap_stg`;

truncate table aaa_tag_varmap_stg;

create table aaa_tag_varmap_stg_20110330 as select * from aaa_tag_varmap_stg;

select * from aaa_tag_varmap_stg_20110330;

select page_id, 1 var_id, page_name var_setting, 1 active from aaa_tag_dart_url_stg;

insert into aaa_tag_varmap_stg select page_id, 1 var_id, page_name var_setting, 1 active from aaa_tag_dart_url_stg;

commit;

insert into aaa_tag_varmap select * from aaa_tag_varmap_stg;

commit;
