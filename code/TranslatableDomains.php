<?php

/**
 * TranslatableDomains helps determine top level domains, and handle 
 * registration of top level domains on silverstripe multi-lingual websites.
 *
 * untested with subsites module,
 * assumes all domain names are the same with different Top-Level-Domains
 * ex. mysite.com and mysite.fr are supported; my-other-site.com is not supported
 *
 */

class TranslatableDomains{
	
	/**
	 * domain_locale_map
	 * Arrays of top-level-domains that map to locales.
	 */
	
	static $domain_locale_map = array(
		//tld(.com, .net, .co.uk, etc) => locale(en_US, etc)
	);
	
	/**
	 * localhost_domain_locale_map
	 * Arrays of virtualhosts that map to locales.
	 */
	static $localhost_domain_locale_map = array(
		//tld(.com, .net, .co.uk, etc) => locale(en_US, etc)
	);
	
	/**
	 * addDomainHandler
	 * Method to add domains and locales to the {@link domain_locale_map}.
	 * errors will be thrown if developer tries to add the same tld more than once.
	 * 
	 * @param string $domain one top level domain (.com, .co.uk, .fr)
	 * @param string $locale one locale that should be mapped to the $domain param.
	 */
	
	function addDomainHandler($domain, $locale){
		if(isset(self::$domain_locale_map[$domain])) user_error("Overwriting domain", E_USER_WARNING);
		self::$domain_locale_map[$domain] = $locale;
	}
	
	/**
	 * addLocalhostDomainHandler
	 * Method to add virtualhosts and locales to the {@link localhost_domain_locale_map}.
	 * errors will be thrown if developer tries to add the same tld more than once.
	 * 
	 * @param string $domain one virtualhost (localhost, localhost-en, etc)
	 * @param string $locale one locale that should be mapped to the $domain param.
	 */
	
	function addLocalhostDomainHandler($domain, $locale){
		if(isset(self::$localhost_domain_locale_map[$domain])) user_error("Overwriting domain", E_USER_WARNING);
		self::$localhost_domain_locale_map[$domain] = $locale;
	}
	
	
	
	/**
	 * isLocalHost
	 * Checks to see if we are running on localhost 
	 * or a defined localhost-alternate. 
	 *
	 * @return Boolean True if current server is running localhost registered 
	 * using {@addLocalhostDomainHandler} or has localhost in the url.
	 *
	 */

	function isLocalHost(){
		$host  = $_SERVER["HTTP_HOST"];
		if(preg_match("/localhost/", $_SERVER["HTTP_HOST"])) return true;
		
		foreach (self::$localhost_domain_locale_map as $str => $val) {
		//check each $localhost_domain_locale_map key for the tld..
			if(preg_match('/^'.$str.'(:[0-9]+)?$/',$host)){
				return true;
		    }
		}
		return false;
	}
	
	
	
	/**
	 * getLocaleFromHost
	 * finds the tld from the server (www.mysite.com = com), then returns 
	 * the locale from the tld in the array $domain_locale_map 
	 *
	 * EXAMPLE: current site is www.mysite.com
	 *			look up com in domain_locale_map / localhost_domain_locale_map
	 *	
	 * @return locale associated with com: en_US (or whatever developer specifies)
	 *
	 */
	
	public function getLocaleFromHost(){
		$locale = i18n::default_locale();
		//be able to return default locale if nothing is found.
		$host  = $_SERVER["HTTP_HOST"];
		if(self::isLocalHost()){
			foreach (self::$localhost_domain_locale_map as $str => $val) {
			//check each $localhost_domain_locale_map key for the tld..
				if(preg_match('/^'.$str.'(:[0-9]+)?$/',$host)){
				//if its a match, stop and return the locale
					$locale = $val;
					break;
			    }
			}
		}else{ 
			foreach (self::$domain_locale_map as $str => $val) {
			//check each $domain_locale_map key for the tld..
				if(preg_match('/^(.*)(\.'.$str.')$/',$host)){
				//if its a match, stop and return the locale
					$locale = $val;
					break;
			    }
			}
		}
		return $locale;
	}
	
	
	
	
	/**
	 * convertLocaleToTLD
	 * finds the current locale and returns a domain name (based on current domain name)
	 * with the correct top level domain.
	 * checks if running localhost or on a webserver.
	 *
	 * EXAMPLE: current domain is www.mysite.jp,
	 *			Http request (url) is for page that is of a german locale.
	 *			Translatable class says page is german locale
	 *			Look up german locale in domain_locale_map
	 *			return www.mysite.de (with or without end /)
	 * 
	 * @param boolean $withEndSlash option to receive tld with or without the end slash
	 * @return string The Top Level Domain that is associated with the locale.
	 */
	 
	 
	public function convertLocaleToTLD($withEndSlash=true){
		$host  = $_SERVER["HTTP_HOST"];
		$currentLocale = Translatable::get_current_locale();
		$tld;
		
		if(self::isLocalHost()){
			$tld = Director::protocol().array_search($currentLocale, self::$localhost_domain_locale_map);
			
			if(preg_match('/:[0-9]+$/',$host)){
				// add the port if one is used
				$tld .= substr($host,strpos($host, ':'));
			}
		} else{
			//if live, switch the tld.
			$domain = Director::protocol().$_SERVER['SERVER_NAME'];
			
			foreach (self::$domain_locale_map as $str => $val) {
			// look through domain_locale_map to match current tld..  
			// (need to do this instead of substr incase tld = .co.uk)
				
				if(preg_match('/^(.*)(\.'.$str.')$/',$host)){
					$domain = substr($domain, 0, strripos($domain, $str));
					break;
				}
			}
			$ext = array_search($currentLocale, self::$domain_locale_map);
			$tld = $domain.$ext;
		}
		return ($withEndSlash) ? $tld.'/' : $tld;
	}
}