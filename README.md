# Opoink Base
This is the base platform of opoink framework.


for the installation procedure please follow instruction at https://www.opoink.com/docs?cat=2&page=1

after cloning the repo in your xampp 7.3.26
add a vhosting for your api

if composer
    then open in visual studio code in terminal 'composer upgrade opoink/framework'
else
    in terminal 'wget https://getcomposer.org/composer.phar'
    composer.phar upgrade opoink/framework

then add .htaccess in your api folder
then add in .htaccess


sql_mode="STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"
<!-- 

RewriteEngine On
# The following will redirect all unexpected visitor to
# other domain, 
# usefull for development purposes	

#	RewriteBase /
#	RewriteCond %{REMOTE_HOST} !^192.168.1.1
#	RewriteRule .* http://opoink-host.com [R=302,L]

	
# Hindi kasi maacces ng server yung auth
# kaya kaya nilagay ito 
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Credentials "true"
    Header set Access-Control-Allow-Methods "*"
    Header set Access-Control-Allow-Headers "Content-Type,X-Amz-Date,Authorization, Devicetoken, Deviceid"
    Header set Access-Control-Request-Headers "X-Requested-With, accept, content-type, Authorization, Devicetoken, Deviceid"
</IfModule>
	
# The following rule tells Apache that if the requested filename
# exists, simply serve it.

	RewriteCond %{REQUEST_FILENAME} -s [OR]
	RewriteCond %{REQUEST_FILENAME} -l
	## RewriteCond %{REQUEST_FILENAME} -l [OR]
	## RewriteCond %{REQUEST_FILENAME} -d

	RewriteRule ^.*$ - [L]

# The following rewrites all other queries to index.php. The 
# condition ensures that if you are using Apache aliases to do
# mass virtual hosting or installed the project in a subdirectory,
# the base path will be prepended to allow proper resolution of
# the index.php file; it will work in non-aliased environments
# as well, providing a safe, one-size fits all solution.

	RewriteCond %{REQUEST_URI}::$1 ^(/.+)/(.*)::\2$
	RewriteRule ^(.*) - [E=BASE:%1]
	RewriteRule ^(.*)$ %{ENV:BASE}/index.php [L]

	
<IfModule mod_expires.c>
	ExpiresActive On
	ExpiresByType image/jpg "access plus 1 year"
	ExpiresByType image/jpeg "access plus 1 year"
	ExpiresByType image/gif "access plus 1 year"
	ExpiresByType image/png "access plus 1 year"
	ExpiresByType text/css "access plus 1 month"
	ExpiresByType application/pdf "access plus 1 month"
	ExpiresByType text/x-javascript "access plus 1 month"
	ExpiresByType application/x-shockwave-flash "access plus 1 month"
	ExpiresByType application/json "access plus 0 seconds"
	ExpiresByType image/x-icon "access plus 1 year"

	############################################
	## Add default Expires header
	## http://developer.yahoo.com/performance/rules.html#expires

    ExpiresDefault "access plus 1 year"
    ExpiresByType text/html "access plus 1 day"
    ExpiresByType text/plain "access plus 1 day"

</IfModule>

<Files "composer.json">
	Order Allow,Deny
	Deny from all
</Files>
<Files "composer.lock">
	Order Allow,Deny
	Deny from all
</Files>
<Files "composer.phar">
	Order Allow,Deny
	Deny from all
</Files>

 -->
