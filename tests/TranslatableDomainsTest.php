<?php

class TranslatableDomainsTest extends SapphireTest {
	static $fixture_file = 'Translatable-Domains/tests/TranslatableDomainsTest.yml';
	static $tempDomainArray;
	
	function setUp() {
		parent::setUp();
		self::$tempDomainArray = TranslatableDomains::$domain_locale_map;
		TranslatableDomains::$domain_locale_map = array(
			'mysite.com' => 'en_US', 
			'another.de' => 'de_DE', 
			'mysite.co.uk' => 'en_GB',
			'localhost-jp' => 'ja_JP',
			'localhost-fr' => 'fr_FR',
			'localhost-it' => 'it_IT'
		);
	}
	
	function tearDown() {
		TranslatableDomains::$domain_locale_map = self::$tempDomainArray;
		parent::tearDown();
	}
	
	/**
	* Test translation group existance
	*/
	function testTranslationGroups() {
		$home = $this->objFromFixture("Page", "home");
		$hasTranslation = $home->hasTranslation("de_DE");
		$this->assertTrue($hasTranslation, "no de translation group");
	}
	
	/**
	* Test lookup of a locale from url
	*/
	
	function testURLtoLocale(){
		foreach(TranslatableDomains::$domain_locale_map as $TLD => $domainLocale){
			$www_domain = "http://www.{$TLD}";
			$no_www_domain = "http://{$TLD}";
			$domain_with_sub = "http://sub.{$TLD}";
			$domain_with_double_sub = "http://sub.sub.{$TLD}";
			
			$www_domain_w_port = "http://www.{$TLD}:8888";
			$no_www_domain_w_port = "http://{$TLD}:8888";
			$domain_with_sub_w_port = "http://sub.{$TLD}:8888";
			$domain_with_double_sub_w_port = "http://sub.sub.{$TLD}:8888";
			
			$this->assertEquals(TranslatableDomains::getLocaleFromURL($www_domain),$domainLocale, "Failed Testing domain with www, using {$www_domain} => {$domainLocale}");
			$this->assertEquals(TranslatableDomains::getLocaleFromURL($no_www_domain),$domainLocale, "Failed Testing domain with no www, using {$no_www_domain} => {$domainLocale}");
			$this->assertEquals(TranslatableDomains::getLocaleFromURL($domain_with_sub),$domainLocale, "Failed Testing domain with subdomain, using {$domain_with_sub} => {$domainLocale}");
			$this->assertEquals(TranslatableDomains::getLocaleFromURL($domain_with_double_sub),$domainLocale, "Failed Testing domain with double subdomain, using {$domain_with_double_sub} => {$domainLocale}");
			$this->assertEquals(TranslatableDomains::getLocaleFromURL($www_domain_w_port),$domainLocale, "Failed Testing domain with www and port, using {$www_domain_w_port} => {$domainLocale}");
			$this->assertEquals(TranslatableDomains::getLocaleFromURL($no_www_domain_w_port),$domainLocale, "Failed Testing domain with no www and port, using {$no_www_domain_w_port} => {$domainLocale}");
			$this->assertEquals(TranslatableDomains::getLocaleFromURL($domain_with_sub_w_port),$domainLocale, "Failed Testing domain with subdomain and port, using {$domain_with_sub_w_port} => {$domainLocale}");
			$this->assertEquals(TranslatableDomains::getLocaleFromURL($domain_with_double_sub_w_port),$domainLocale, "Failed Testing domain with double subdomain and port, using {$domain_with_double_sub_w_port} => {$domainLocale}");
		}
		$this->assertEquals(TranslatableDomains::getLocaleFromURL("http://subsite.mysite.xyz"), 'en_US', 'getLocaleFromURL should return default locale if no locales are matched.');
		$this->assertNull(TranslatableDomains::getTLD("http://subsite.mysite.xyz"), "Failed Testing domain, domain should not be registered with domain_locale_map");
		
	}
	
	
	/**
	 * Test conversion of a url's locale to a TLD
	 * (make sure a german page shows with a .de extension)
	 */
	
	function testConvertURLLocaleToTLD(){
		
		$orig_locale = Translatable::get_current_locale();
		
		Translatable::set_current_locale('en_US');
		$url = "http://www.mysite.co.uk:8888/home/";
		$expectedResult = "mysite.com";
		$newURL = TranslatableDomains::setDomainByPageLocale($url);
		$newURLTLD = TranslatableDomains::getTLD($newURL);
		$this->assertTrue($newURLTLD == $expectedResult, "Failed converting $url to $expectedResult");
		
		Translatable::set_current_locale('de_DE');
		$url = "http://mysite.com/home-de/";
		$expectedResult = "another.de";
		$newURL = TranslatableDomains::setDomainByPageLocale($url);
		$newURLTLD = TranslatableDomains::getTLD($newURL);
		$this->assertTrue($newURLTLD == $expectedResult, "Failed converting $url to $expectedResult");
		
		Translatable::set_current_locale('en_GB');
		$url = "http://sub.sub.mysite.com/home-gb/";
		$expectedResult = "mysite.co.uk";
		$newURL = TranslatableDomains::setDomainByPageLocale($url);
		$newURLTLD = TranslatableDomains::getTLD($newURL);
		$this->assertTrue($newURLTLD == $expectedResult, "Failed converting $url to $expectedResult");
		
		Translatable::set_current_locale('fr_FR');
		$url = "http://sub.localhost-jp/home-fr/";
		$expectedResult = "localhost-fr";
		$newURL = TranslatableDomains::setDomainByPageLocale($url);
		$newURLTLD = TranslatableDomains::getTLD($newURL);
		$this->assertTrue($newURLTLD == $expectedResult, "Failed converting $url to $expectedResult");
		
		Translatable::set_current_locale('ja_JP');
		$url = "http://localhost-jp:8888/home-jp/";
		$expectedResult = "localhost-jp";
		$newURL = TranslatableDomains::setDomainByPageLocale($url);
		$newURLTLD = TranslatableDomains::getTLD($newURL);
		$this->assertTrue($newURLTLD == $expectedResult, "Failed converting $url to $expectedResult");

		Translatable::set_current_locale($orig_locale);		
		
	}
}