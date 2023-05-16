# CryoCore
A lightweight boilerplate for PHP enabling fast lightweight applications &amp; microservices.

# Notice
! this is a work in progress, this won't be fully stable for a few months. 

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