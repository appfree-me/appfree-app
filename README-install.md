## How to deploy

Tested on Ubuntu 24.04.2 LTS

Appfree consists of two repositories: `phone-server` and `appfree-app`. To have a working appfree installation, we have to provision and deploy both repositories. `appfree-app` depends on `phone-server`, so we setup `phone-server` first.

## Prerequisites

You need a telephone trunk accessible via SIP. Currently sipgate.de provider is supported out of the box.
## phone-server

phone-server provides the configured asterisk instance which appfree-app connects to.
```
git clone appfree@laurentpichler.com:phone-server  
cd phone-server  
```

Set Variables necessary for provisioning and deploy in .env:

```bash
cp .env.dist .env
```

For the purpose of this tutorial, we are assuming you are working on the server you want to provision, so we set DEPLOYHOST=localhost:
```bash
# SSH Host to deploy to  
DEPLOYHOST=localhost  
```  
Currently, username for deployment & execution is hardcoded to `appfree`.

Enter your SIP credentials in .env:  
Right now, only sipgate.de provider is supported out of the box.

```bash
SIP_SECRET=rEdactEd  
SIP_USERNAME=123456t0  
SIP_HOST=sipconnect.sipgate.de  
SIP_FROMDOMAIN=sipconnect.sipgate.de  
```  


There are three instances (prod, staging, local) pre-configured in asterisk, you need different phone numbers for each. Let's start by only defining phone number for prod instance.

For e. g. german number 0176123456, this should read in .env:

```bash
PHONE_NUMBER_APPFREE_PROD=49176123456
```


Now, on to provisioning:

```bash
bin/provision
```

This will install required packages, e. g. php, composer, asterisk.

UFW Firewall rules will be overwritten with provided firewall rules. These open necessary ports for inbound connections to appfree-app and outbound connections to Sipgate SIP API.


Now, deploy the project to your target server:

```
composer install
bin/deploy
````

User `appfree` must have ssh access for this to work.

Deployment of phone-server completed.
## appfree-app

Now we install the actual PHP app:

Checkout the repository:

```bash
git clone appfree@laurentpichler.com:appfree-app  
cd appfree-app

cp .env.dist .env
```

Set DEPLOYHOST in .env:

```bash
# SSH Host to deploy to  
DEPLOYHOST=localhost  
```  

Install Systemd Unit Files for user appfree, create `appfree` user account, password and database for mariadb:

```bash
bin/provision
```

Take note of the mariadb password you set here and enter it in the .env file!


```bash
DB_CONNECTION=mariadb  
DB_HOST=127.0.0.1 
DB_PORT=3306  
DB_DATABASE=appfree_app_local  
DB_USERNAME=appfree  
DB_PASSWORD=YOURPASSWORDHERE
```    

## Deploy

Before deploying, add your ssh key to appfree User account on DEPLOYHOST.

Deploy app to your server:

```bash
bin/deploy prod main
```

This deploys the latest branch `main` of the repository to `/home/appfree/deploy/prod/appfree-app` on DEPLOYHOST configured in .env

## How to run

#### Run as a system service

Use the systemctl commands explained [here](#Systemd)

#### Run standalone  (for development)

To run the app locally on your development machine and connect to `phone-server` running on your server, checkout the repository locally and set the appropriate variables in .env:

```bash
DEPLOYHOST=your-server.example
APP_ENV=local

# Also set the vars for the DB you want to connect to
```

To set up secure communication tunnels with the phone-server, first start
```bash
phone-server/bin/enable-forwarding
```

Then, run the app with
```
./bin/run-app
```  
This runs appfree with Xdebug enabled. Use only for development.

Run ```bin/deploy prod|staging```  to deploy your work to the other instances. 

### Database setup

Appfree uses Mariadb as a database. Fill in _DB_PASSWORD_ variable from .env.dist to configure access.

### [Systemd]

appfree is backed by systemd unit files which restart it automatically should the app crash.

Enable and start appfree with these commands after installing the debian packages:

```bash
systemctl --user enable appfree-app_local.service
systemctl --user start appfree-app_local.service
```

Appfree provisioning will create a user `appfree` in your system. Appfree doesn't need special privileges to run: it will run under the `appfree` user account.
Appfree doesn't need special privileges to run: Systemd unit files are installed in the `appfree` user home directory and a separate systemd instance running under the same user is used to manage the instance. 
