drop table aaa_tag_location;

CREATE  TABLE aaa_tag_location (
    `loc` INT NOT NULL,
    `location_name` VARCHAR(128) NOT NULL ,
    `parent` VARCHAR(45) NOT NULL ,
    `type` VARCHAR(45) NOT NULL ,
    `state` VARCHAR(45) NOT NULL ,
    `channel` VARCHAR(45) NOT NULL ,
    PRIMARY KEY (`loc`) );


