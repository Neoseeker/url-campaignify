## URL-Campaignify

### Background

This is a tool to help add Google Analytics campaign tracking paramters to URLs and entire text blocks.

It was [originally written by pixelistik](https://github.com/pixelistik/url-campaignify) to add campaign links for Piwik, an open source web analytics tool.

This fork has been modified to work only with Google Analytics and supports all of GA's
campaign paramters for categorising incoming links. This tool works by appending additional GET
params to your HTTP URLs:

    http://my-site.tld/?utm_campaign=newsletter-5&utm_term=header-link&medium=email&utm_source=response

[Read more about this on Google analytics](https://support.google.com/analytics/bin/answer.py?hl=en&answer=1033863).

### What

This class aims to make it easier to dynamically append such parameters to URLs.

#### Single URLs

Instead of worrying about `?` and `&` you can just do this:

    $uc = new UrlCampaignify();

    $url = "http://some-blog.tld/cms.php?post=123&layout=default";

    $newUrl = $uc->campaignify($url, "newsletter-5", "header-link");

The result has properly appended parameters:

    http://some-blog.tld/cms.php?post=123&layout=default&utm_campaign=newsletter-5&utm_term=header-link

#### Text blocks

You can also throw entire blobs of text at the function. It will find and
campaignify all HTTP URLs in it.

    $uc = new UrlCampaignify();

    $text = "Look at http://my-site.tld especially".
            "here: http://my-site.tld/news.htm";

    $newUrl = $uc->campaignify($text, "newsletter-5", "header-link");

If you are expecting HTML input, it makes sense to only change the URLs
in `href` attributes. Use `campaignifyHref()` for this. It will turn

    See <a href="http://site.tld">http://site.tld</a> for more information.

into

    See <a href="http://site.tld?utm_campaign=foo">http://site.tld</a> for more information.

Have a look at the test cases to see which situations and edge cases have been
covered -- or not.

#### Auto-number URLs in text blocks

All campaignified URLs in a text block are counted (starting at 1). You can use
the current number of a URL in your keyword in `sprintf()` style. This is useful
if you want to differentiate between several identical URLs in one text.

    $uc = new UrlCampaignify();

    $text = "Here comes the header link: http://my-site.tld".
            "here is a long and verbose text".
            "and another link at the end: http://my-site.tld";

    $newUrl = $uc->campaignify($text, "news", "link-%d");

Will give you

    Here comes the header link: http://my-site.tld?utm_campaign=news&utm_term=link-1
    here is a long and verbose text and another link at the end:
    http://my-site.tld?utm_campaign=news&utm_term=link-2";

#### Domains

It only makes sense to add campaigns if you actually analyse them. This implies
that you control the site and its analytics tool. You can restrict UrlCampaignify
to only work on URLs on a given Domain. Just pass it to the constructor

    $uc = new UrlCampaignify('my-site.tld')

Note that subdomains are automatically included (this differ's from the original behaviour),
so the above instance *will* touch URLs on `www.my-site.tld`.

You can disable this automatic behaviour by using

    $uc->set_campaignify_subdomains(false);

You can specify multiple domains as an array:

    $uc = new UrlCampaignify(array('my-site.tld', 'www.my-site.tld', 'my-other-site.tld'))

### Major Differences Between This Fork and Original

1. Uses only Google Analytics: utm_campaign, utm_source, utm_medium etc.  utm_medium defaults to "email"
2. Subdomains will be modified by default.
3. The campaignify() and campaignifyHref() methods has been modified to accept Google Analytics parameters
4. Campaignify will apply to URLs that do not have utm_medium set (the original code only applies campaignify
if utm_campaign is not set)
5. Campaignify will not replace utm values already present in URLs.

### Installation

#### Using composer

URL-Campaignify matches the PSR-0 file layout and is on packagist. You should
be able to simply type

    composer require  neoseeker/url-campaignify:dev-master
    composer install

to get the latest code from the master branch included into your project.

#### Just grabbing the file

You can also simply download the single file that provides the class:
https://github.com/Neoseeker/url-campaignify/raw/master/src/Pixelistik/UrlCampaignify.php