<VirtualHost *:80>
	ServerAdmin richard.nishitoyo@goaaa.com
	ServerName www.metrics.go.aaa.com
	ServerAlias metrics.go.aaa.com

	DocumentRoot /var/www/metrics.go.aaa.com
	<Directory />
		Options FollowSymLinks
		AllowOverride None
	</Directory>
	<Directory /var/www/>
		Options -Indexes FollowSymLinks MultiViews
		AllowOverride None
		Order allow,deny
		allow from all
	</Directory>

	ScriptAlias /cgi-bin/ /usr/lib/cgi-bin/
	<Directory "/usr/lib/cgi-bin">
		AllowOverride None
		Options +ExecCGI -MultiViews +SymLinksIfOwnerMatch
		Order allow,deny
		Allow from all
	</Directory>

	ErrorLog /var/log/apache2/metrics.go.aaa.com/error.log

	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel warn

	CustomLog /var/log/apache2/metrics.go.aaa.com/access.log combined
	#CustomLog "|bin/rotatelogs /var/log/apache2/metrics.go.aaa.com/access.log 86400" common 
	#CustomLog "|bin/rotatelogs /var/log/apache2/metrics.go.aaa.com/error.log 86400" common 

    Alias /doc/ "/usr/share/doc/"
    <Directory "/usr/share/doc/">
        Options Indexes MultiViews FollowSymLinks
        AllowOverride None
        Order deny,allow
        Deny from all
        Allow from 127.0.0.0/255.0.0.0 ::1/128
    </Directory>

  AliasMatch ^/ss/partner_js/(.*\.js) /home/aaametrics/partner_js/$1
  AliasMatch ^/ss/(.*\.js) /home/aaametrics/js/$1
</VirtualHost>
