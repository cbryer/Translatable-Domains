<?php

/**
 * Register one locale per top level domain, and one locale per virtualhost.
 * mixing virtual hosts and live domains in the same array is not recommended as 
 * a live webserver may try to switch to a domain name of localhost.
 *
 * Usage on live web server:
 * <code>
 * TranslatableDomains::addDomainHandler('mysite.com','en_US');
 * TranslatableDomains::addDomainHandler('mysite.de','de_DE');
 * TranslatableDomains::addDomainHandler('mysite.jp','ja_JP');
 * </code>
 *
 * Usage on localhost
 * <code>
 * TranslatableDomains::addDomainHandler('localhost-en:8888','en_US');
 * TranslatableDomains::addDomainHandler('localhost-fr:8888','fr_FR');
 * TranslatableDomains::addDomainHandler('localhost:8888','en_US');
 * </code>
 *
 */

Object::add_extension('SiteTree', 'SingleLocaleDomain');