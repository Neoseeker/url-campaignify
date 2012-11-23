## URL-Campaignify

### Background

The open source web analytics tool Piwik can use campaigns and keywords
for categorising incoming links. They work by appending additional GET
params to your HTTP URLs:

    http://my-site.tld/?pk_campaign=newsletter-5&pk_kwd=header-link

[Read more about this technique in the Piwik docs](http://piwik.org/docs/tracking-campaigns/)

[Google analytics](https://support.google.com/analytics/bin/answer.py?hl=en&answer=1033863)
and probably most other analytics tool do basically the same thing.

### What

This class aims to make it easier to dynamically append such parameters to URLs.

#### Single URLs

Instead of worrying about `?` and `&` you can just do this:

    $uc = new UrlCampaignify();
    
    $url = "http://some-blog.tld/cms.php?post=123&layout=default";
    
    $newUrl = $uc->campaignify($url, "newsletter-5", "header-link");

The result has properly appended parameters:

    http://some-blog.tld/cms.php?post=123&layout=default&pk_campaign=newsletter-5&pk_kwd=header-link

#### Text blocks

You can also throw entire blobs of text at the function. It will find and
campaignify all HTTP URLs in it.

    $uc = new UrlCampaignify();
    
    $text = "Look at http://my-site.tld especially".
            "here: http://my-site.tld/news.htm";

    $newUrl = $uc->campaignify($text, "newsletter-5", "header-link");

Have a look at the test cases to see which situations and edge cases have been
covered -- or not.