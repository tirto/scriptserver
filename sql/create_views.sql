use aaametrics;

create or replace VIEW aaa_tag_settings_vw AS 
SELECT pg.page_id, pg.rsid, a.agency_name, v.var_name, v.var_type, m.var_setting, pg.page_url, pn.partner_name, m.active
FROM aaa_tag_varmap m, aaa_tag_vars v, aaa_tag_pages pg, aaa_tag_partners pn, aaa_tag_agencies a
WHERE pg.page_id = m.page_id
AND m.var_id = v.var_id
AND pg.partner_id = pn.partner_id
AND v.agency_id = a.agency_id
AND m.active =1;


create or replace view aaa_tag_settings_travelocity as select * from aaa_tag_settings_vw where partner_name = 'travelocity';

create or replace view aaa_tag_settings_insurity as select * from aaa_tag_settings_vw where partner_name = 'insurity';

create or replace view aaa_tag_settings_limasalle as select * from aaa_tag_settings_vw where partner_name = 'limasalle';

create or replace view aaa_tag_settings_csaa as select * from aaa_tag_settings_vw where partner_name = 'csaa';

create or replace view aaa_tag_pageinfo_vw as 
select pg.page_id as page_id, vm.var_setting AS page_name, pg.page_url AS page_url,pn.partner_name AS partner_name,pg.rsid AS rsid, pg.ga_id, pg.referrer as referrer, pg.query_param as query_param, pg.referrer_query_param as referrer_query_param  
from aaa_tag_partners pn, aaa_tag_pages pg, aaa_tag_varmap vm 
where pn.partner_id = pg.partner_id
and vm.page_id = pg.page_id
and vm.var_id = 1; -- pagename var

create or replace view aaa_tag_partnertag_info_vw as select t.tag_id, t.tag_script, t.tag_type, t.tag_name, pt.active, p.partner_id, p.partner_name, t.agency_id, a.agency_name from aaa_tag_partners_tags pt, aaa_tag_partners p, aaa_tag_tags t, aaa_tag_agencies a where pt.partner_id = p.partner_id and pt.tag_id = t.tag_id and t.agency_id = a.agency_id;

create or replace view aaa_tag_pagetag_info_vw as select t.tag_id, t.tag_script, t.tag_type, t.tag_name, pt.active, p.page_id, p.page_name, t.agency_id, a.agency_name from aaa_tag_pages_tags pt, aaa_tag_pageinfo_vw p, aaa_tag_tags t, aaa_tag_agencies a where pt.page_id = p.page_id and pt.tag_id = t.tag_id and t.agency_id = a.agency_id;
