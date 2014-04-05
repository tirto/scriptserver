#/bin/bash
DATETIME=`date +%Y%m%d-%H%M%S`
BACKUP_DIR=ss-$DATETIME

mkdir -p ./bak/$BACKUP_DIR
mkdir -p ./bak/$BACKUP_DIR/sql
mkdir -p ./bak/$BACKUP_DIR/js
mkdir -p ./bak/$BACKUP_DIR/v1
mkdir -p ./bak/$BACKUP_DIR/v1/includes
mkdir -p ./bak/$BACKUP_DIR/dbetl
mkdir -p ./bak/$BACKUP_DIR/conf
mkdir -p ./bak/$BACKUP_DIR/partner_js

cp README* ./bak/$BACKUP_DIR
cp *.sh   ./bak/$BACKUP_DIR
cp v1/*.php ./bak/$BACKUP_DIR/v1
cp v1/includes/*.inc ./bak/$BACKUP_DIR/v1/includes
cp sql/*.sql ./bak/$BACKUP_DIR/sql
cp js/*.js ./bak/$BACKUP_DIR/js
cp dbetl/*.php ./bak/$BACKUP_DIR/dbetl/
cp conf/*  ./bak/$BACKUP_DIR/conf
cp partner_js/*.js  ./bak/$BACKUP_DIR/partner_js

# apache config
cp /etc/apache2/sites-available/* ./bak/$BACKUP_DIR/conf

# database dump
mysqldump -u aaametrics -paaametrics123 aaametrics > ./bak/$BACKUP_DIR/sql/aaametrics_$DATETIME.sql
