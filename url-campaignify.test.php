<?php
require_once('url-campaignify.php');

class UrlCampaignifyTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
		$this->uc = new UrlCampaignify();
	}
	
	/* Tests for single URLs */
	/**
	 * Test if the conversion works with URLs being fed in that do not have a 
	 * querystring already
	 */
	public function testSingleUrlsNoQuerystring() {
		// Just a campaign added
		$input = 'http://test.de';
		$expected = 'http://test.de?pk_campaign=newsletter-nov-2012';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012');
		$this->assertEquals($expected, $result);
		
		$input = 'http://test.de/kontakt.html';
		$expected = 'http://test.de/kontakt.html?pk_campaign=newsletter-nov-2012';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012');
		$this->assertEquals($expected, $result);
		
		// A campaign added plus keyword
		$input = 'http://test.de';
		$expected = 'http://test.de?pk_campaign=newsletter-nov-2012&pk_keyword=link1';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012', 'link1');
		$this->assertEquals($expected, $result);
		
		$input = 'http://test.de/impressum.htm';
		$expected = 'http://test.de/impressum.htm?pk_campaign=newsletter-nov-2012&pk_keyword=link1';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012', 'link1');
		$this->assertEquals($expected, $result);
	}
	
	/**
	 * Test if the conversion works with URLs being fed in that do not have a 
	 * querystring already, but a "?" at the end
	 */
	public function testSingleUrlsQuerySign() {
		$input = 'http://test.de?';
		$expected = 'http://test.de?pk_campaign=newsletter-nov-2012';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012');
		$this->assertEquals($expected, $result);
	}
	
	/**
	 * Test if the conversion works with URLs being fed in that have a 
	 * querystring already
	 */
	public function testSingleUrlsExistingQuerystring() {
		// Just a campaign added
		$input = 'http://test.de?param1=one&param2=two';
		$expected = 'http://test.de?param1=one&param2=two&pk_campaign=newsletter-nov-2012';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012');
		$this->assertEquals($expected, $result);
		
		// A campaign added plus keyword
		$input = 'http://test.de?p1=one&param2=two';
		$expected = 'http://test.de?p1=one&param2=two&pk_campaign=newsletter-nov-2012&pk_keyword=link1';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012', 'link1');
		$this->assertEquals($expected, $result);
	}
	
	/**
	 * Test if the conversion properly accepts and produces urlencoded strings
	 */
	public function testSingleUrlsUrlencode() {
		// Given URL already has urlencoded strings
		$input = 'http://test.de?p1=one%2Cvalue&param2=two';
		$expected = 'http://test.de?p1=one%2Cvalue&param2=two&pk_campaign=newsletter-nov-2012&pk_keyword=link1';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012', 'link1');
		$this->assertEquals($expected, $result);
		// Campaign and keyword have chars that need to be urlencoded, too
		$input = 'http://test.de?p1=one%2Cvalue&param2=two';
		$expected = 'http://test.de?p1=one%2Cvalue&param2=two&pk_campaign=newsletter+nov%2C2012&pk_keyword=link%2C1';
		$result = $this->uc->campaignify($input, 'newsletter nov,2012', 'link,1');
		$this->assertEquals($expected, $result);
	}
	
	/* Tests for entire texts */
	
	public function testTextMultipleURLs() {
		$input = "Lorem ipsum dolor https://test.com/ sit 
		amet, consetetur sadipscing elitr, 
		sed diam nonumy eirmod tempor invidunt ut labore et dolore magna 
		aliquyam erat, sed diam voluptua. 
		At http://test.co.uk/test.html";
		
		$expected = "Lorem ipsum dolor https://test.com/?pk_campaign=news sit 
		amet, consetetur sadipscing elitr, 
		sed diam nonumy eirmod tempor invidunt ut labore et dolore magna 
		aliquyam erat, sed diam voluptua. 
		At http://test.co.uk/test.html?pk_campaign=news";
		
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);
	}
	
	/**
	 * Text with the same URL repeated twice
	 */
	public function testTextMultipleRepeatedURLs() {
		$input = "Lorem ipsum dolor http://test.com sit 
		amet, consetetur sadipscing elitr, 
		sed diam nonumy eirmod tempor invidunt ut labore et dolore magna 
		aliquyam erat, sed diam voluptua. 
		At http://test.com";
		
		$expected = "Lorem ipsum dolor http://test.com/?pk_campaign=news sit 
		amet, consetetur sadipscing elitr, 
		sed diam nonumy eirmod tempor invidunt ut labore et dolore magna 
		aliquyam erat, sed diam voluptua. 
		At http://test.com?pk_campaign=news";
		
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);
	}
}