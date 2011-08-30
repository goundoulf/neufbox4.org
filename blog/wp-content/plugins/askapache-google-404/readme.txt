=== AskApache Google 404 ===
Contributors: AskApache
Donate link: http://www.askapache.com/donate/
Tags: google, 404, errordocument, htaccess, error, notfound, ajax, search, seo, mistyped, urls, news, videos, images, blogs, optimized, askapache, post, admin, adsense, askapache, missing, admin, template, traffic
Requires at least: 2.7
Tested up to: 3.1-alpha
Stable tag: 4.8.2.2


== Description ==

AskApache Google 404 is a sweet and simple plugin that takes over the handling of any HTTP Errors that your blog has from time to time.  The most common type of error is when a page cannot be found, due to a bad link, mistyped URL, etc.. So this plugin uses some AJAX code, Google Search API'S,  and a few tricks to display a very helpful and Search-Engine Optimized Error Page. The default displays Google Search Results for images, news, blogs, videos, web, custom search engine, and your own site. It also searches for part of the requested filename that was not found, but it attaches your domain to the search for SEO and greater results.

This new version also adds related posts, recent posts, and integrates thickbox for instant previews.

[See it Live](http://www.askapache.com/htaccess-wordpress-php-google?robots=mod_rewrite) at [AskApache](http://www.askapache.com/)


== Installation ==

This section describes how to install the plugin and get it working. http://www.askapache.com/seo/404-google-wordpress-plugin.html

1. Upload the zip file to the /wp-content/plugins/ directory and unzip. 
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to your Options Panel and open the "AA Google 404" submenu. /wp-admin/options-general.php?page=askapache-google-404.php
4. Enter in your Google Search API Key and configure your settings.
5. If you use a 404.php file, add <?php if(function_exists('aa_google_404'))aa_google_404();?> to the body.


== Frequently Asked Questions ==

Do I need a Google Account?

Yes.

Do I need a 404.php template file?

No, one is included with the plugin.

My 404.php page isn't being served for 404 Not Found errors!?

Add this to your [.htaccess file](http://www.askapache.com/htaccess/htaccess.html "AskApache .htaccess File Tutorial") -- and read my [.htaccess Tutorial](http://www.askapache.com/htaccess/htaccess.html "AskApache .htaccess File Tutorial") for more information.

ErrorDocument 404 /index.php?error=404
Redirect 404 /index.php?error=404

Fixing Status Headers

For super-advanced users, or those with access and knowledge of [Apache .htaccess/httpd.conf files](http://www.askapache.com/htaccess/htaccess.html "AskApache .htaccess File Tutorial") you should check that your error pages are correctly returning a [404 Not Found HTTP Header](http://www.askapache.com/htaccess/apache-status-code-headers-errordocument.html "404 Not Found HTTP Header") and not a 200 OK Header which appears to be the default for many WP installs, this plugin attempts to fix this using PHP, but the best way is to use my .htaccess trick above.  You can check your headers by requesting a bad url on your site using my [online Advanced HTTP Header Tool](http://www.askapache.com/online-tools/http-headers-tool/ "HTTP Header Viewer").


== Other Notes ==

Future Awesomeness

The goal of this plugin is to boost your sites SEO by telling search engines to ignore your error pages, with the focus on human users to increase people staying on your site and being able to find what they were originally looking for on your site. Because I am obsessed with fast web pages, many various speed/efficiency improvements are also on the horizon.

Another feature that I am using with beta versions of this plugin, is tracking information for you to go over at your leisure, to fix recurring problems. The information is collected is the requested url that wasnt found, the referring url that contains the invalid link.

The reason I didnt include it in this release is because for sites like AskApache with a very high volume of traffic (and thus 404 requests) this feature can create a bottleneck and slow down or freeze a blog if thousands of 404 errors are being requested and saved to the database. This could also very quickly be used by malicious entities as a Denial of Service attack. So I am figuring out and putting into place limits.. like once a specific requested url resulting in a not found error has been requested 100x in a day, an email is sent to the blog administrator. But to prevent Email DoS and similar problems with the number and interval of emails allowed by your email provider other considerations on limits need to be examined.

FAST! CACHE! SPEED!

Future versions of this plugin will add this option for everyone.. Basically, there will be an option to switch to using a 100% javascript (instead of javascript + php) method of handling 404 errors, this will be BIG because the plugin will simply create 1 static html file named 404.html and then use .htaccess ErrorDocument to redirect all 404 errors to this static html file. The downside is the only way to get stuff like related posts and recent posts would be to use ajax or to create the 404.html static file at regular intervals or for multiple error requests. This will help tremendously in keeping your site and server speedy as it will reduce CPU/Memory/Disk IO/and Database Queries to almost nothing. Stay tuned.

One other big improvement or feature-add is to show the admin a list of error urls and allow the admin to specify the correct url that the error url should point to. Then using mod_rewrite rules automatically generated by the plugin and added to .htaccess these error urls will 301 redirect to the correct urls, boosting your SEO further and also helping your visitors. A big difference between this method and other redirection plugins is that it will use mod_rewrite, I would really like to avoid using php to redirect or rewrite to other urls, as this method has a HUGE downside in terms of your site and servers speed, bandwidth usage, CPU/Memory usage, Disk Input/Output (writes/reads), security issues, Database Usage, among other problems.
Generating Revenue

Anyone smart enough to find and use this plugin deserves to earn a little income too, so I am working on integrating AdSense into the Search Results. Currently this is very new and not enabled or allowed by Google in certain circumstances and just isnt a feature yet of the Google AJAX API. At the very least I am going to add a custom search engine results for your site that will allow you to display relevant ads, but I am still waiting for some clarification from my Google Homeslices on whether we can use the AJAX API to display ADS on 404 error pages automatically based on the requested url or if that violates the Google TOS, which is something I would never condone or even get close to violating. If not then we will have to settle for no ADS being displayed automatically and only being displayed if the user actually types something in the search box. So go get your AdSense account (free) and also sign up for a Google CSE (custom search engine) as soon as possible.


== Screenshots ==

1. Basic AskApache 404 Look
2. Related Links Feature
3. Configuration Panel
4. New 404 Google Helper