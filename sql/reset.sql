
delete from aaa_tag_varmap where page_id in (select page_id from aaa_tag_pages where partner_id = 1);

delete from aaa_tag_pages where partner_id = 1;
insert into aaa_tag_pages(page_id, page_url, partner_id) select no, pageURL, 1 from aaa_tag_partnermap where partner = 'travelocity';

insert into aaa_tag_varmap (page_id, var_id, var_setting)
