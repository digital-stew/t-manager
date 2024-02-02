#!/bin/bash
mysql -u debian-sys-maint --password=`cat /etc/mysql/credentials.php |grep password | cut -f2 -d'"'` t-manager < testing.sql # desktop dev
npx cypress run
exit 0