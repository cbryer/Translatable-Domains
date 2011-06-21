<?php

/**
 * The default behavior of Silverstripe is to show any page in any locale on the 
 * same top level domain.  This class is designed to look up the locale of a 
 * requested page, and change the url to the appropriate domain (set in 
 * TranslatableDomains class)
 *
 */

class SingleLocaleDomain extends DataObjectDecorator{
	
	/**
	 * ContentController
	 * lets us extend init() through this method on SiteTree.
	 * we will override page init to change domains if the page locale is 
	 * different than the detected locale of the domain.
	 *
	 * 3 Things to be aware of (while developing..  these are all accounted for here):
	 *		¥ Intended Locale of the Domain (.fr, .de, .com?)
	 *		¥ Page's Locale in the Database
	 *		¥ i18n locale that is in the header.
	 *
	 */
	
	public function contentcontrollerInit(){
	 
		if($this->owner->hasExtension('Translatable')){
			
			//find the correct locale
			$curLoc = TranslatableDomains::getLocaleFromHost();
			// compare page locale vs domain's locale
			// low occurance of these not matching, but important
			
			if(Translatable::get_current_locale() != $curLoc){
				// check to see if the page has a translation for the url, if so, translate.
				// helpful for homepages where / == /home but we want the german translation..
				
				if($this->owner->hasTranslation($curLoc)){
					//if page exists and translation exists, redirect & show translation	
					$correctPage = $this->owner->getTranslation($curLoc);
					Director::redirect($correctPage->Link());
				} else {
					//otherwise, find requested page by url, determine locale, and put us in the right domain.
					$newUrl = TranslatableDomains::convertLocaleToTLD($withEndSlash=false).$this->owner->Link();
					Director::redirect($newUrl);
				}
			} else i18n::set_locale($this->owner->Locale);
		}
	}
	
	
	/**
	 * other helpful utility methods
	 */
	
	/**
	 * PageByCurrentLocale
	 * gets a page in the default locale and finds its translation in the current locale
	 *
	 * @param string $pageURL url of a page in the default locale
	 * @return DataObject Translated record of the requested page in the current locale, null if none exists.
	 */
	
	function PageByCurrentLocale($pageURL) {
		if($pg = Translatable::get_one_by_locale('Page', Translatable::default_locale(), "URLSegment = '{$pageURL}'")) return $pg->getTranslation(Translatable::get_current_locale());
		
		return null;
	}
	
	
	/**
	 * PageByDefaultLocale
	 * gets a page in the default locale
	 *
	 * @param string $pageURL url of a page in the default locale
	 * @return DataObject requested page in the current locale, null if none exists.
	 */
	 
	function PageByDefaultLocale($pageURL){
		$defLoc = Translatable::default_locale();
		if($pg = Translatable::get_one_by_locale('Page', $defLoc, "URLSegment = '{$pageURL}'")) return $pg;
		
		return null;
	}
	
}