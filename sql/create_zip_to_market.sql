drop table aaa_tag_zip_to_market;

CREATE  TABLE aaa_tag_zip_to_market (
    `zipcode` INT NOT NULL ,
    `city` VARCHAR(128) NOT NULL ,
    `loc` VARCHAR(45) NOT NULL ,
    `parent` VARCHAR(45) NOT NULL ,
    `market` VARCHAR(45) NOT NULL ,
    PRIMARY KEY (`zipcode`) );


