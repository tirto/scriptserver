insert into aaa_tag_pages SELECT * FROM `aaametrics`.`aaa_tag_pages_stg`;

insert into aaa_tag_varmap select * from aaa_tag_varmap_stg;

commit;

