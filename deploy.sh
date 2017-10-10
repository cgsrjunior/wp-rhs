#!/bin/bash

git pull /home/migracao.redehumanizasus.net/integracao/wp-rhs

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
cd /home/migracao.redehumanizasus.net/integracao/wp-rhsmigration-scripts
php rhs_migrations.php all
