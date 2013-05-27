<?php
/**
 * Utility class that helps adding gooogle analytics campaigns to URLs
 *
 * You can track the sources of your traffic better. This class aims to
 * be a convenient way to add such parameters to any URL or even a string
 * containing multiple URLs.
 *
 * This is based on Pixelistik url-campaignify but modified to
 * handle only Google Analytics campaigns.
 *
 * It also treats all subdomains when told to only translate certain domains
 *
 * @package UrlCampaignify
 * @author  Pixelistik <code@pixelistik.de>
 * @license MIT licensed, see LICENSE
 * @link    https://github.com/pixelistik/url-campaignify
 */

namespace Pixelistik;

/**
 * The single utility class
 */
class UrlCampaignify
{
	/**
	 * Regex to find URLs
	 *
	 * Taken from
	 * http://stackoverflow.com/a/2015516/376138
	 * (except added beginning/end conditions)
	 *
	 * Changed to fix a bug where inclusion of single quotes
	 * breaks the regexp when large strings use href=''
	*/
	const URL_REGEX =
		'/((href\s*=\s*["\'])?)                                 # optional preceding href attribute
		((https?):\/\/                                          # protocol
		(([a-z0-9$_\.\+!\*\(\),;\?&=-]|%[0-9a-f]{2})+         # username
		(:([a-z0-9$_\.\+!\*\(\),;\?&=-]|%[0-9a-f]{2})+)?      # password
		@)?(?#                                                  # auth requires @
		)((([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*           # domain segments AND
		[a-z][a-z0-9-]*[a-z0-9]                                 # top level domain  OR
		|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}
		(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])                 # IP address
		)(:\d+)?                                                # port
		)(((\/+([a-z0-9$_\.\+!\*\(\),;:@&=-]|%[0-9a-f]{2})*)* # path
		(\?([a-z0-9$_\.\+!\*\(\),;:@&=-]|%[0-9a-f]{2})*)      # query string
		?)?)?                                                   # path and query string optional
		(#([a-z0-9$_\.\+!\*\(\),;:@&=-]|%[0-9a-f]{2})*)?      # fragment
		))/ix';


	protected $utm_source;
	protected $utm_medium;
	protected $utm_term;
	protected $utm_content;
	protected $utm_campaign;

	protected $campaignify_subdomains = true;

	/**
	 * Counter that starts at 1 and is increased for every URL found in a
	 * multiple URL texts that is campaigified. Used for auto-increased keywords.
	 */
	protected $urlInTextNumber;

	/**
	* If set to true, UrlCampaignify::campaignify() will only look at URLs
	* in a href="" HTML attribute.
	*/
	protected $hrefOnly = false;

	/**
	 * String (one) or array (multiple strings). If specified, only URLs for
	 * these domain(s) will be campaignified.
	 * Note that www.domain.tld and domain.tld have to be specified separately.
	 */
	protected $domain = null;

	/**
	 * Constructor
	 *
	 * @param String|Array $domain Optional. A single string or an array of strings
	 */
	public function __construct($domain = null)
	{
		$this->add_domains($domain);
	}

	/**
	 * @param String|Array $domain Optional. A single string or an array of strings
	 */
	public function add_domains($domain) {
		if (is_string($domain)) {
			$domain = array($domain);
		}
		if (isset($this->domain)) {
			$this->domain = array_merge($this->domain,$domain);
		} else {
			$this->domain = $domain;
		}
	}

	/**
	 * Add a campaign and (optionally) keyword param to a single URL. This is
	 *     used as a callback for preg_replace_callback().
	 *
	 * @param Array $urlMatches Matches of a regex, containing URLs.
	 * @return string
	 */
	protected function campaignifyUrl($urlMatches)
	{
		// Full regex match is passed at index [0]
		// Entire URL is at [3]
		// Domain.tld is at [9]
		// Possible href=" is at [1]
		$url = $urlMatches[3];
		$domain = $urlMatches[9];
		$hrefPart = $urlMatches[1];

		// Are we on hrefOnly and not in a href attribute?
		$skipOnHref = $this->hrefOnly && $hrefPart === "";
		// Is a domain configured and we are not on it?
		if ($this->campaignify_subdomains) {
			$skipOnDomain = $this->domain && !preg_match("/^(.+\.)?(".implode('|',$this->domain).")$/i",$domain);
		} else {
			$skipOnDomain = $this->domain && !in_array($domain, $this->domain);
		}

		if ($skipOnHref || $skipOnDomain) {
			$newUrl = $url;
		} else {
			/* Do the thing: */
			// Parse existing querystring into an array
			$query = parse_url($url, PHP_URL_QUERY);
			$params = array();
			parse_str($query, $params);

			// Add our params, if no campaign is there yet, plus keyword if given
			if (!isset($params['utm_campaign']) || !isset($params['utm_medium'])) {
				if (!isset($params['utm_campaign'])) {
					$params['utm_campaign'] = $this->utm_campaign;
				}
				if ($this->utm_term && (!isset($params['utm_term']) || empty($params['utm_term']))) {
					// Put URL count into formatted keyword string (if given)
					$keywordValue = sprintf($this->utm_term, $this->urlInTextNumber);

					$params['utm_term'] = $keywordValue;
				}
				$utm_params = array('utm_source','utm_medium','utm_content');
				foreach ($utm_params as $utm_param) {
					if ($this->$utm_param) {
						// Put URL count into formatted keyword string (if given)
						if (!isset($params[$utm_param]) || empty($params[$utm_param])) {
							$params[$utm_param] = $this->$utm_param;
						}
					}
				}
			}

			$newQuery = http_build_query($params);

			if ($query) {
				// If there was a querystring already, replace it
				$newUrl = str_replace($query, $newQuery, $url);
			} else {
				// or just append the new one
				$newUrl = $url . '?' . $newQuery;
				// remove possible "??" if the URL already had a final "?"
				$newUrl = str_replace("??", "?", $newUrl);
			}

			$this->urlInTextNumber++;
		}

		// Re-attach possible href="
		return $hrefPart . $newUrl;
	}

	/**
	 * Add a campaign and (optionally) keyword param to all URLs in a text
	 *
	 * @param string $text     The text in which the URLs should be campaignified.
	 * @param string $utm_campaign
	 * @param string $utm_source
	 * @param string $utm_medium
	 * @param string $utm_term
	 * @param string $utm_content
	 *
	 * @return string The text with campaignified URLs.
	 */
	public function campaignify($text, $utm_campaign='',$utm_source='',$utm_medium='email',$utm_term='',$utm_content='')
	{
		$this->utm_campaign = $utm_campaign;
		$this->utm_source = $utm_source;
		$this->utm_medium = $utm_medium;
		$this->utm_term = $utm_term;
		$this->utm_content = $utm_content;

		$this->urlInTextNumber = 1;

		$this->hrefOnly = false;

		$text = preg_replace_callback(self::URL_REGEX, array($this, 'campaignifyUrl'), $text);

		return $text;
	}

	/**
	 * Add a campaign and (optionally) keyword param to all URLs in href attributes
	 *
	 * @param string $text     The text in which the URLs should be campaignified.
	 * @param string $utm_campaign
	 * @param string $utm_source
	 * @param string $utm_medium
	 * @param string $utm_term
	 * @param string $utm_content
	 *
	 * @return string The text with campaignified URLs.
	 */
	public function campaignifyHref($text, $utm_campaign='',$utm_source='',$utm_medium='email',$utm_term='',$utm_content='')
	{

		$this->utm_campaign = $utm_campaign;
		$this->utm_source = $utm_source;
		$this->utm_medium = $utm_medium;
		$this->utm_term = $utm_term;
		$this->utm_content = $utm_content;

		$this->urlInTextNumber = 1;

		$this->hrefOnly = true;

		$text = preg_replace_callback(self::URL_REGEX, array($this, 'campaignifyUrl'), $text);

		return $text;
	}

	/**
	 *
	 * @param $campaignify_subdomains bool
	 */
	function set_campaignify_subdomains($campaignify_subdomains) {
		$this->campaignify_subdomains = $campaignify_subdomains;
	}
}
