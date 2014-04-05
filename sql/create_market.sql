drop table aaa_tag_market;

CREATE  TABLE aaa_tag_market (
    `market` VARCHAR(45) NOT NULL ,
    `market_name` VARCHAR(128) NOT NULL ,
    `parent` VARCHAR(45) NOT NULL ,
    PRIMARY KEY (`market`) );
