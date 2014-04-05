<VirtualHost *:80>
	ServerAdmin richard.nishitoyo@goaaa.com
	ServerName  replatform.go.aaa.com

	DocumentRoot /var/www/csaalocal/pressflow
	ErrorLog /var/log/apache2/drupal/error.log
	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel warn
	CustomLog /var/log/apache2/drupal/access.log combined
        <Directory "/var/www/csaalocal/pressflow">
          AllowOverride All
        </Directory>
</VirtualHost>
