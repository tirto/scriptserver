delete from aaa_tag_varmap where var_id in (select var_id from aaa_tag_vars where var_name in ('s.state', 's.zip', 's.prop4', 's.eVar3'));

