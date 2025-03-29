<div align="center">
    <a href="https://www.appfree.me/"><img src="https://github.com/appfree-me/appfree-app/raw/main/images/logos/appfree.png"></img></a>
</div>

<!--<img alt="Static Badge" src="https://img.shields.io/badge/Laravel-12-red?link=https%3A%2F%2Flaravel.com%2F&link=https%3A%2F%2Flaravel.com%2F"></img>-->

<img alt="Static Badge" src="https://img.shields.io/badge/Laravel-12-blue?link=https%3A%2F%2Flaravel.com%2Fdocs%2F12.x%2Freleases">

Appfree is a framework allowing you to easily create IVR apps, e. g. apps which can be interacted with from a normal telephone or featurephone. It is based on a modern tech stack: PHP 8 and Laravel.

### About Appfree
 ☎️ **A framework to provide smartphone-only services via plain old telephone service** ☎️

Appfree is a framework allowing you to easily create Interactive Voice Response apps, e. g. apps which can be interacted with from a normal telephone or featurephone. 

Based on Asterisk PBX and ReactPHP, you can develop apps which allow callers to interact with your service. Instead of a smartphone app users can call your telephone number and interact with your app written in Appfree.


The framework comes with a fully functional application for the city of Munich bike sharing service "MVG Rad", which is normally usable only via an Android/iOS app.  

With appfree, it is usable from every plain old telephone.


### Project components

#### appfree-connector

- https://github.com/appfree-me/appfree-app

Main repository for the appfree-connector. Contains the framework and application logic for IVR apps.

#### appfree-phone-server

- https://github.com/appfree-me/phone-server

Phone server backend providing connectivity to the phone network via Asterisk application. One instance of `phone-server` supports connections by multiple `appfree-`app instances.

### Features

  - Framework for implementing IVR apps
  - Define state machines to process call flows
  - Call flows are defined based on event sequences
  - Build integration tests based on synthesized event flows
  - MVG Rad sample app

### Installation 

See [Installation](./README-install.md)

### Architecture

See [architecture](./README-architecture.md)

### MVG Rad example app

See [example app](./README-apps.md)

### License

[MIT License](LICENSE.md)

### Supported by

<a href="https://prototypefund.de/">
  <img class="logo-other" src="images/logos/ptf.png" height="150"/>
</a>
<a href="https://www.bmbf.en/">
  <img src="images/logos/bmbf_de.jpg" height="150"/>
</a>

