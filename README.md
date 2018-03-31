# Gist file uploader

Demo on [https://gist-file-uploader.herokuapp.com/](https://gist-file-uploader.herokuapp.com/)

## Install

### Install php-curl

Check if curl module is available

    ls -la /etc/php5/mods-available/

If it is, enable the curl module

    sudo php5enmod curl

If not, install it

    sudo apt-get update
    sudo apt-get install php5-curl

Restart Apache

    sudo service apache2 restart

### Create a Github app

Go to `Settings` (upper right) > `Developer settings` > `New OAuth App`

* Choose an application name
* Type the full URL of your app's website
* In "Authorization callback URL" type your app's website followed by `/auth`
* Copy the Client ID and Client Secret and change the constants in `inc.config.php`
