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
	 * getLocaleFromHost
	 * finds the tld from the server (www.mysite.com = com), then returns 
	 * the locale from the tld in the array $domain_locale_map 
	 *
	 * uses {@getLocaleFromURL} to look up the tld from the server.
	 *	
	 * @return locale associated with the current top level domain.
	 *
	 */
	
	public function getLocaleFromHost(){
		$locale = self::getLocaleFromURL($_SERVER["HTTP_HOST"]);
		return $locale;
	}
	
	/**
	 * getLocaleFromURL
	 * finds the locale of the url passed into the function, and returns the associated locale
	 *
	 * @param string $host URL used to retrieve tld from.
	 * @return locale associated with tld: (.de == de_DE)
	 *
	 */
	
	public function getLocaleFromURL($host){
		$locale = i18n::default_locale();
		//be able to return default locale if nothing is found.
		
		if($tld = self::getTLD($host)) $locale = self::$domain_locale_map[$tld];
		return $locale;
	}
	
	/**
	 * getTLD
	 * finds the tld from the host parameter (www.mysite.com = com) 
	 *
	 * @param string $host URL used to retrieve tld from.
	 * @return tld associated with com: en_US (or whatever developer specifies)
	 *
	 */
	
	public function getTLD($host){
		foreach (self::$domain_locale_map as $tld => $locale) {
			//check each $domain_locale_map key for the tld..
			//if(preg_match('/^(.*)\b'.$tld.'\b(:[0-9]+)?/',$host)){
			if(preg_match('!^(.*)\b'.$tld.'\b(/.*$)?!',$host)){
				//if its a match, stop and return the tld
				return $tld;
		    }
		}
		//user_error("No Top Level Domain found", E_USER_WARNING);
		return null;
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
		$tld = self::setDomainByPageLocale($_SERVER["HTTP_HOST"]);
		return ($withEndSlash) ? $tld.'/' : $tld;
	}
	
	
	
	public function setDomainByPageLocale($host){
		if($tld = self::getTLD($host)){
			$currentLocale = Translatable::get_current_locale();
			$domain = Director::protocolAndHost();
			$domain = substr($domain, 0, strripos($domain, $tld));
			$ext = array_search($currentLocale, self::$domain_locale_map);
			return $domain.$ext;
		}
	}
}