# CryoCore
A lightweight boilerplate for PHP enabling fast lightweight applications &amp; microservices.

To run locally simply clone to your `htdocs/public_html/www` directory.

## What is CryoCore
A small library designed at massively reducing development time through the use of annotations.

With CryoCore you can add annotations to code, annotations can act as flags or modifiers, you can
create your own annotations however CyroCore's MVC framework has all the annotations you need to create
a database powered backend. 

## How do I get setup
First, clone this repository into your web servers document root, I have a controller already setup that goes
into more detail, however If you're just browsing:

```
<?php

    namespace App;

    @Controller
    class LandingController {

        @Get( path="/" , produces="text/text" )
        public function Homepage(){
            return "Hello World";
        }

    }

?>
```
If you've cloned this repository, head over to http://localhost/ and see "Hello World"

# React Support
I've added support for react apps, you can have many micro-apps if necessary simply setup a new controller:
```
<?php

    namespace App\Controllers;

    @Controller 
    @ReactApp( app_name="appname" , local_url="http://localhost:3000")
    @ReactRoute( match_type="starts_with" , value="/appname/" , mapsTo="/appname/")
    class Portfolio {
        
    }

?>
```
Note: `mapsTo` allows you to map to a react app when using `npm start` on create-react-app.
make sure this matches what's in your package.json -> "homepage" value.

`local_url` is used for local development, you can use this inline with create-react-app. 
`app_name` maps directly to `/public/reactapps/YOUR_APP_NAME` it loads the resources automatically.

When you use `npm run build` in `create-react-app` copy and paste the contents of the build directory (not the build directory itself, but its descendents) into YOUR_APP_NAME directory.

If you want to fallback to a React App (Lets say you have 1), in your .env.local or .env.production file, add this line:
```
react.fallback_app="YOUR_APP_NAME"
```

This will autoroute your react app. 

These are the basics, I will be adding a lot more functionality to this, here is a template .gitignore file for your projects:
```
Cryo/*
var/*
```

There is a plugins directory, in the near future I'll be working on building functionality to add plugins but for now its
just core functionality