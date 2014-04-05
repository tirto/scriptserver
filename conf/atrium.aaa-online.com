<VirtualHost *:80>
	ServerAdmin david.wolfe@goaaa.com
	ServerName  atrium.aaa-online.com

	DocumentRoot /var/www/atrium.aaa-online.com
	ErrorLog /var/log/apache2/atrium/error.log
	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel warn
	CustomLog /var/log/apache2/atrium/access.log combined
        <Directory "/var/www/atrium">
          AllowOverride All
        </Directory>
</VirtualHost>
