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

These are the basics, I will be adding a lot more functionality to this, here is a template .gitignore file for your projects:
```
Cryo/*
var/*
```

There is a plugins directory, in the near future I'll be working on building functionality to add plugins but for now its
just core functionality