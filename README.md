scriptserver
============

Generic scriptserver code for returning Javascript payloads, e.g. pixel, other JS, etc. based on a MySQL db
script server docs

purpose: 
  used for consolidate tagging from different partner sites
  
directories:
- conf
    scriptserver.properties: log4php properties file
    dbetl.properties: log4php properties file
    logrotate.conf: logrotate config file

- dbetl
    read partner mapping input file and reset database tables 
    files:
    - load_partnermap*.php
    - reset_partnermap*.php
    directories:
    - logs
        dbetl.log

- input
    input file for dbetl

- instructions
    how to install ss (sent to partner sites)

- js
    javascripts for script server development/testing
    files:
    - ss.js
        main javascript that responsible for handling the user cookie, setting page variables, calling the script server via AJAX, and firing beacon to analytics
    
    - s_code.js
        production omniture's site catalyst javascript code
    
    - s_code_dev.js
        development omniture's site catalyst javascript code
    
    - ga.js 
        google analytics code

    - ss_<partner>.js 
        javascript code for each partner (change the config section to modify RSID, partner value, s_code version, etc.)

- log4php
    logging services: http://logging.apache.org/log4php/
    scriptserver.properties: config for script server
    dbetl.properties: config for etl

- logs
    scriptserver.log: tells you what script server is doing

- partner_js
    this is where we put any partner javascript (loaded by script server). note: if the partner js has document.write(), it will rewrite the page! so, use document.body.appendChild() instead.

- serverkey:
    contains server certificates 

- sql
    create.sql: table and views create script
    create_views.sql: drop/recreate views
    other sql scripts used for ETL

- test
    bunch of test scripts

- v1 (version 1) -- this is where script code resides
  ss.php 
    the main script. it processes user requests, applies special logic, queries the db, and returns payloads (img/iframe/js) for a given page and partner

  includes dir:
    # for script server
    db.inc: database config
    aaa_<viewname>.inc: class to handle all db operation for a specific task
       e.g. aaa_tag_settings.inc: operations on script server variable mapping
    omniture.inc: manages beacon creation and firing

    # for dbetl
    aaa_input_file.inc: insert input file into db
    aaa_partner_map.inc: load and reset partner mappings

    # common shared function and vars
    common.inc 

    # others *.inc files for ETL

