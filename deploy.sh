#!/bin/bash

cd /home/migracao.redehumanizasus.net/integracao/wp-rhs/ 
pwd
git pull

if [ $1 = 'dev' ]
then
   composer install
else
    composer install --no-dev
fi

sh /home/migracao.redehumanizasus.net/integracao/wp-rhs/compile-sass.sh
cd /home/migracao.redehumanizasus.net/integracao/wp-rhs/public
wp rewrite flush
wp language core update
cd /home/migracao.redehumanizasus.net/integracao/wp-rhs/migration-scripts
php /home/migracao.redehumanizasus.net/integracao/wp-rhs/rhs_migrations.php all
