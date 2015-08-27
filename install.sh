#!/bin/bash
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

if  [ -z $SUDO_USER  ]; then 
	echo "You must have root privilege to do the installation."
	exit 1;
fi

type -P php &> /dev/null || (echo "php it is not accessible to the script"; exit 1;)
if [ -f "composer.phar" ]; then
    php composer.phar self-update
else
    type -P curl &> /dev/null || (echo "Curl it is not accessible to the script"; exit 1;)
    curl -sS https://getcomposer.org/installer | php || (echo "Error downloading composer"; exit 1;)
fi
php composer.phar install || (echo "Error downloading dependencies"; exit 1;)
echo "Successfully updated dependencies"

read -p "You want to run the queues with? (cron/supervisord)" QUEUE
if [ $QUEUE = "cron" ]; then
	echo "Creating crontab job..."
  	line="* * * * * php ${DIR}/artisan schedule:run 1>> /dev/null 2>&1"
  	crontab -l | { cat; echo "$line"; } | crontab -
  	echo "crontab created"
else
  	echo "Installing supervisord"
	sudo apt-get install -y supervisor || (echo "Error Installing supervisord"; exit 1;) 

	CONFIG=$(cat <<EOF
[program:service-email-queue-listen]
command=php ${DIR}/artisan queue:listen --sleep=3 --tries=3 --daemon
user=$USER
process_name=%(program_name)s_%(process_num)d
directory=${DIR}
stdout_logfile=${DIR}/storage/logs/service.email.log
redirect_stderr=true
autostart=true
autorestart=true
startretries=3
numprocs=2
EOF
)
	sudo touch /etc/supervisor/conf.d/serviceemail.conf
	sudo echo "${CONFIG}" > /etc/supervisor/conf.d/serviceemail.conf

	sudo service supervisor restart
fi
echo "Successfully installed"



