<?php

/**
 * Register one locale per top level domain, and one locale per virtualhost
 * Usage:
 * <code>
 * TranslatableDomains::addDomainHandler('com','en_US');
 * TranslatableDomains::addDomainHandler('de','de_DE');
 * TranslatableDomains::addDomainHandler('jp','ja_JP');
 *
 * TranslatableDomains::addLocalhostDomainHandler('localhost-en','en_US');
 * TranslatableDomains::addLocalhostDomainHandler('localhost-fr','fr_FR');
 * TranslatableDomains::addLocalhostDomainHandler('localhost','en_US');
 * </code>
 *
 */

Object::add_extension('SiteTree', 'SingleLocaleDomain');