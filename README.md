# CryoCore
A lightweight boilerplate for PHP enabling fast lightweight applications &amp; microservices.

# Notice
! this is a work in progress, this won't be fully stable for a few months. 

# .htaccess
While developing I'm using xampp, for me:
```
FallbackResource /index.php
```
works fine, however if you've deployed this to a server that's hosted try:
```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.php/$1 [NC,L,QSA]
```
This redirects all requests where the path doesn't exist to index.php, allowing Routing to work correctly

# Route React App
```
<?php

    namespace App\Frontend;

    @Controller
    @ReactApp( app_name="portfolio" , local_url="http://localhost:3000")
    @ReactRoute( match_type="equals" , value="/" , mapsTo="/" )
    class Portfolio {

    }

?>
```