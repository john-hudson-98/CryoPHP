
![Logo](https://github.com/john-hudson-98/CryoCore/blob/main/public/cryocore.png?raw=true)


# CryoCore

An IoC & Low/Minimal Code Framework that brings PHP to life. Create an entire application with very minimal code. Great for startup projects and projects with many microservices. This project is slowly starting to move towards a Configuration as Code Framework, although fallback PHP will always be available! Allowing you to create entire applications without a single line of PHP, all this is being designed at the moment so keep checking back to see my progress.
## Authors

- [@john-hudson-98](https://www.github.com/john-hudson-98)
  (jhudsonjejames@gmail.com)

I am actively accepting contributors, please get in contact if you want to join in on developing an exciting PHP library. Upon a single successful contribution I will be adding you to this list!


## Feedback

If you have any feedback, please post it in issues under `enhancement`. I'm happy to extend the functionality of this framework if it will benefit more people. 


## Documentation

[Wiki](https://github.com/john-hudson-98/CryoCore/wiki)


## Deployment

To deploy this project run

```bash
  cd /path/to/documentRoot
  git clone "https://github.com/john-hudson-98/CryoCore/" .
```

I've developed this using XAMPP, so my .htaccess file works on XAMPP and already comes with this project, but if this is on a hosted server, chances are you need to update your htaccess file to look like this:
```bash
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.php/$1 [NC,L,QSA]
```


## License

This framework is unlicensed, ignore any license set on the repository, this is completely free for personal use, same for commercial use. 

If this is used commercially, please let me know! I'm excited to see where this code ends up!s