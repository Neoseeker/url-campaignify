<?php
namespace Pixelistik;

use \Pixelistik\UrlCampaignify;

class UrlCampaignifyTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var UrlCampaignify $uc
	 */
	protected $uc;
    public function setUp()
    {
        $this->uc = new UrlCampaignify();
    }

    /* Tests for single URLs */
    /**
     * Test if the conversion works with URLs being fed in that do not have a
     * querystring already
     */
    public function testSingleUrlsNoQuerystring()
    {
        // Just a campaign added
        $input = 'http://test.de';
        $expected = 'http://test.de?utm_campaign=newsletter-nov-2012&utm_medium=email';
        $result = $this->uc->campaignify($input, 'newsletter-nov-2012');
        $this->assertEquals($expected, $result);

        $input = 'http://test.de/kontakt.html';
        $expected = 'http://test.de/kontakt.html?utm_campaign=newsletter-nov-2012&utm_medium=email';
        $result = $this->uc->campaignify($input, 'newsletter-nov-2012');
        $this->assertEquals($expected, $result);

        // A campaign added plus keyword
        $input = 'http://test.de';
        $expected = 'http://test.de?utm_campaign=newsletter-nov-2012&utm_term=link1&utm_medium=email';
        $result = $this->uc->campaignify($input, 'newsletter-nov-2012', '', 'email', 'link1');
        $this->assertEquals($expected, $result);

        $input = 'http://test.de/impressum.htm';
        $expected = 'http://test.de/impressum.htm?utm_campaign=newsletter-nov-2012&utm_term=link1&utm_medium=email';
        $result = $this->uc->campaignify($input, 'newsletter-nov-2012', '', 'email', 'link1');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test if the conversion works with URLs being fed in that do not have a
     * querystring already, but a "?" at the end
     */
    public function testSingleUrlsQuerySign()
    {
        $input = 'http://test.de?';
        $expected = 'http://test.de?utm_campaign=newsletter-nov-2012&utm_medium=email';
        $result = $this->uc->campaignify($input, 'newsletter-nov-2012');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test if the conversion works with URLs being fed in that have a
     * querystring already
     */
    public function testSingleUrlsExistingQuerystring()
    {
        // Just a campaign added
        $input = 'http://test.de?param1=one&param2=two';
        $expected = 'http://test.de?param1=one&param2=two&utm_campaign=newsletter-nov-2012&utm_medium=email';
        $result = $this->uc->campaignify($input, 'newsletter-nov-2012');
        $this->assertEquals($expected, $result);

        // A campaign added plus keyword
        $input = 'http://test.de?p1=one&param2=two';
        $expected = 'http://test.de?p1=one&param2=two&utm_campaign=newsletter-nov-2012&utm_term=link1&utm_medium=email';
        $result = $this->uc->campaignify($input, 'newsletter-nov-2012', '', 'email', 'link1');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test if the conversion properly accepts and produces urlencoded strings
     */
    public function testSingleUrlsUrlencode()
    {
        // Given URL already has urlencoded strings
        $input = 'http://test.de?p1=one%2Cvalue&param2=two';
        $expected = 'http://test.de?p1=one%2Cvalue&param2=two&utm_campaign=newsletter-nov-2012&utm_term=link1&utm_medium=email';
        $result = $this->uc->campaignify($input, 'newsletter-nov-2012', '', 'email', 'link1');
        $this->assertEquals($expected, $result);
        // Campaign and keyword have chars that need to be urlencoded, too
        $input = 'http://test.de?p1=one%2Cvalue&param2=two';
        $expected = 'http://test.de?p1=one%2Cvalue&param2=two&utm_campaign=newsletter+nov%2C2012&utm_term=link%2C1&utm_medium=email';
        $result = $this->uc->campaignify($input, 'newsletter nov,2012', '', 'email', 'link,1');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test if the conversion leaves existing campaigns alone
     */
    public function testSingleUrlsExistingCampaign()
    {
        // Just a campaign existing, should stay
        $input = 'http://test.de?utm_campaign=leave-me-alone';
        $expected = 'http://test.de?utm_campaign=leave-me-alone';
        $result = $this->uc->campaignify($input, 'override-attempt');
        $this->assertEquals($expected, $result);

        // A campaign plus keyword existing
        $input = 'http://test.de?utm_campaign=leave-me-alone&utm_term=me-too';
        $expected = 'http://test.de?utm_campaign=leave-me-alone&utm_term=me-too';
        $result = $this->uc->campaignify($input, 'override-attempt', '', 'email', 'override-attempt');
        $this->assertEquals($expected, $result);

        // A campaign existing, keyword should NOT be added
        // (keywords mostly make no sense without their campaign)
        $input = 'http://test.de?utm_campaign=leave-me-alone';
        $expected = 'http://test.de?utm_campaign=leave-me-alone';
        $result = $this->uc->campaignify($input, 'override-attempt', '', 'email', 'override-attempt');
        $this->assertEquals($expected, $result);
    }

    /**
     * If a param is another URL (properly encoded), it should be left alone
     */
    public function testSingleUrlsUrlInParam()
    {
        $input = 'http://test.de?share=http%3A%2F%2Fexample.org';
        $expected = 'http://test.de?share=http%3A%2F%2Fexample.org&utm_campaign=news&utm_medium=email';
        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);
    }

    public function testDomainSpecific()
    {
        $this->uc = new UrlCampaignify('test.com');
	    $this->uc->set_campaignify_subdomains(false);

        // Campaigify specified domain
        $input = 'http://test.com';
        $expected = 'http://test.com?utm_campaign=news&utm_medium=email';
        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);

        $input = 'http://test.com/?param=one';
        $expected = 'http://test.com/?param=one&utm_campaign=news&utm_medium=email';
        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);

        // Do not campaignify other domains, including subdomains of the configured
        $input = 'http://www.test.com';
        $expected = 'http://www.test.com';
        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);

        $input = 'http://test.de';
        $expected = 'http://test.de';
        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);

        $input = 'http://test.de/1.html';
        $expected = 'http://test.de/1.html';
        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);
    }

	public function testDomainSpecificWithSubDomains()
	{
		$this->uc = new UrlCampaignify('test.com');

		// Campaigify specified domain
		$input = 'http://sub.test.com';
		$expected = 'http://sub.test.com?utm_campaign=news&utm_medium=email';
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);

		$input = 'http://sub.test.com/?param=one';
		$expected = 'http://sub.test.com/?param=one&utm_campaign=news&utm_medium=email';
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);

		// Do not campaignify other domains, including subdomains of the configured
		$input = 'http://www.test.com';
		$expected = 'http://www.test.com?utm_campaign=news&utm_medium=email';
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);

		//domains that aren't the exact match won't be counted
		$input = 'http://test.de';
		$expected = 'http://test.de';
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);

		$input = 'http://test.com.sg';
		$expected = 'http://test.com.sg';
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);


		$input = 'http://test.de/1.html';
		$expected = 'http://test.de/1.html';
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);
	}

    public function testDomainSpecificMultiple()
    {
        $this->uc = new UrlCampaignify(
            array('test.com', 'www.test.com', 'testing.com')
        );

        // Campaignify specified domains
        $input = 'http://test.com';
        $expected = 'http://test.com?utm_campaign=news&utm_medium=email';
        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);

        $input = 'http://www.test.com';
        $expected = 'http://www.test.com?utm_campaign=news&utm_medium=email';
        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);

        $input = 'http://testing.com';
        $expected = 'http://testing.com?utm_campaign=news&utm_medium=email';
        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);

        // Do not campaignify other domains
        $input = 'http://test.de';
        $expected = 'http://test.de';
        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);

        $input = 'http://test.de/1.html';
        $expected = 'http://test.de/1.html';
        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);
    }

    /* Tests for entire texts */

    public function testTextMultipleURLs()
    {
        $input = "Lorem ipsum dolor https://test.com/ sit
        amet, consetetur sadipscing elitr,
        sed diam nonumy eirmod tempor invidunt ut labore et dolore magna
        aliquyam erat, sed diam voluptua.
        At http://test.co.uk/test.html";

        $expected = "Lorem ipsum dolor https://test.com/?utm_campaign=news&utm_medium=email sit
        amet, consetetur sadipscing elitr,
        sed diam nonumy eirmod tempor invidunt ut labore et dolore magna
        aliquyam erat, sed diam voluptua.
        At http://test.co.uk/test.html?utm_campaign=news&utm_medium=email";

        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);
    }

    /**
     * Text with the same URL repeated twice
     */
    public function testTextMultipleRepeatedURLs()
    {
        $input = "Lorem http://test.com ipsum http://test.com";

        $expected = "Lorem http://test.com?utm_campaign=news&utm_medium=email ipsum http://test.com?utm_campaign=news&utm_medium=email";

        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test correct handling of URLs in <> brackets
     */
    public function testTextBracketedUrl()
    {
        $input = "Lorem <http://test.com>";

        $expected = "Lorem <http://test.com?utm_campaign=news&utm_medium=email>";

        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test correct handling of a sentence-ending dot after a URL
     */
    public function testTextUrlEndDot()
    {
        $input = "Please go to http://test.com.";

        $expected = "Please go to http://test.com?utm_campaign=news&utm_medium=email.";

        $result = $this->uc->campaignify($input, 'news');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test formatted keyword string with URL counter
     *
     * If the keyword contains a sprintf() compatible string with an Integer
     * (%d) in it, the URL number of the current URL is inserted. This number
     * starts at 1 and is only useful for multiple URL texts.
     */
    public function testAutoIncreasedKeywordFormatting()
    {
        $input = "This http://test.com and that http://test.com";
        $expected = "This http://test.com?utm_campaign=news&utm_term=link-1&utm_medium=email and ".
            "that http://test.com?utm_campaign=news&utm_term=link-2&utm_medium=email";

        $result = $this->uc->campaignify($input, 'news', '', 'email', 'link-%d');
        $this->assertEquals($expected, $result);

        $input = "This http://test.com and that http://test.com";
        $expected = "This http://test.com?utm_campaign=news&utm_term=1 and ".
            "that http://test.com?utm_campaign=news&utm_term=2";

        $result = $this->uc->campaignify($input, 'news', '', '', '%d');
        $this->assertEquals($expected, $result);
    }

    /* Tests for only handling href attributes in texts */

    /**
     * Test if a campaignifyHref() only picks up URLs in href attribute
     */
    public function testTextMultipleURLsHrefOnly()
    {
        $input = 'Hello. <a href="http://test.com">Page</a>.'.
            'More http://test.com/nope.htm';

        $expected = 'Hello. <a href="http://test.com?utm_campaign=yes&utm_medium=email">Page</a>.'.
            'More http://test.com/nope.htm';

        $result = $this->uc->campaignifyHref($input, 'yes');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test for some variations of the href attribute
     */
    public function testHrefAcceptance()
    {
        // More whitespace
        $input = 'Hello. <a href = "http://test.com">Page</a>.';
        $expected = 'Hello. <a href = "http://test.com?utm_campaign=yes&utm_medium=email">Page</a>.';

        $result = $this->uc->campaignifyHref($input, 'yes');
        $this->assertEquals($expected, $result);

        // Single quotes
        $input = "Hello. <a href='http://test.com'>Page</a>.";
        $expected = "Hello. <a href='http://test.com?utm_campaign=yes&utm_medium=email'>Page</a>.";

        $result = $this->uc->campaignifyHref($input, 'yes');
        $this->assertEquals($expected, $result);
    }
}
