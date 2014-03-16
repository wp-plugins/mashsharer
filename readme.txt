=== Mashshare Share Buttons ===
Contributors: ReneHermi
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: Mashable, Share button, Facebook Share button, Twitter Share Button, Social Share, Share, Google+, Twitter, Facebook, Digg, Email, Stumble Upon, Linkedin,+1, add to any, AddThis, addtoany, admin, bookmark, bookmarking, bookmarks, buffer, button, del.icio.us, Digg, e-mail, email, Facebook, facebook like, google, google plus, google plus one, icon, icons, image, images, Like, linkedin, links, lockerz, page, pages, pin, pin it, pinit, pinterest, plugin, plus 1, plus one, Post, posts, Reddit, save, seo, Share, Shareaholic, sharedaddy, sharethis, sharing, shortcode, sidebar, sociable, social, social bookmarking, social bookmarks, statistics, stats, stumbleupon, svg, technorati, tumblr, tweet, twitter, vector, widget, wpmu
Requires at least: 3.1+
Tested up to: 3.8.1
Stable tag: 1.1.8

Mashshare sharing plugin is a high-performance share functionality inspired by the great website Mashable for Facebook and Twitter


== Description == 

> Mashshare Share Buttons shows the total share counts of Facebook and Twitter at a glance 
It puts some beautiful and clean designed Share Buttons on top and end of your posts to get the best most possible social share feedback from your user.
It´s inspired by the Share buttons Mashable is using on his website.

<h3> Mashshare demo </h3>

[Share Buttons](http://www.mashshare.net "Share-Buttons - Mashable inspired Share Buttons")


This plugin is in active development and will be updated on a regular basis - Please do not rate negative before i tried my best to solve your issue. Thanks buddy!

= Main features Features =

* Performance improvement for your website as no external scripts and count data is loaded
* Privacy protection for your user - No permanent connection to Facebook, Twitter and Google needed for sharing
* High-Performance caching functionality. You decide how often counts are updated.
* All counts will be collected in your database and loaded first from cache. No further database requests than.
* Up to 10.000 free daily requests
* Up to 40.000 free additional daily requests with an api key (Get it free at sharedcount.com)
* Works with every Theme
* Works in pages and posts
* Automatic embedding or manual via Shortcode into posts and pages
* Simple installation and setup
* Uninstaller: Removes all plugin tables and settings in the WP database
* Service and support by the author
* Periodic updates and improvements. (Feel free to tell me your demand)
* More Share Buttons are coming soon. 

**Shortcodes**

* Use `[mashshare]` anywhere in pages or post's text to show the buttons and total count where you like to at a custom position.
Buttons are shown exactly where you put the shortcode in.
* For manual insertion of the Share Buttons in your template files use the following php code where you want to show your Mash share buttons:`mashsharer();`
Configure the Share buttons sharing function in the settings page of the plugin.
* Change the color and font size of Mashshare directly in the css file `yourwebsite.com/wp-content/mashsharer/assets/mashsharer.css`
* With one of the next updates i will give you the possibility to change color and font-size on the settings page. So you dont have to fiddle around in css files any longer.

= How does it work? =

Mashshare makes use of the great webservice sharedcount.com and periodically checks for the total count 
of all your Facebook and Twitter shares and cumulates them. It than shows the total number beside the Share buttons. 
No need to embed dozens of external slow loading scripts into your website. 
 
= How to install and setup? =
Install it via the admin dashboard and to 'Plugins', click 'Add New' and search the plugins for 'Mashshare'. Install the plugin with 'Install Now'.
After installation goto the settings page Settings->Mashshare and make your changes there.


== Frequently Asked Questions ==

> Find here the most asked questions and my answers. If you have any special question do not hesitate to write me personally at rene[at]digitalsday.com

<h4>Why is the total count not shown immediately after sharing?</h4>
It takes some time for the script to detect the sharing. So wait a few minutes than you see the total calculated clicks. Keep also in mind the caching time you defined in the admin panel.
So when you set the plugin to 5minutes caching time. You have to wait at least for 5minutes until the click count is shown.

<h4>Do i need a Mashshare or sharedcount account?</h4>
No you don´t. There is no account needed for up to 10.000 daily requests. For most websites this is suitable enough as Mashshare make use of exensive caching so 
the requests to sharedount are reduced to a little. If you want to have more often updated share counts you can register at sharecount for a free account and than are able
to use a free api key which increases your daily request limit up to 50.000 which should be fine for very large websites. Within the settings page of Mashshare Share buttons you find the sharedcount register link.

<h4>Does this plugin sends any personal user data to you or to Facebook, Twitter etc.?</h4>

No, there is no personal data send to Facebook, Twitter, Google and other services. There is also no data which goes to my hands that includes any IP or other data. 
The big advantage of using this Mashare Share buttons is the independance in comparision to other plugins which creates steady connections to Facebook and Co. 
So there is no IP based data send to the social networks or to sharedcount. 

<h4>How is sharedcount able to get the total number of shares?</h4>
Sharedcount is using public available API services of the social networks which deliver only the number of shares for a specific webpage. Sharedcount is not able to see who shared anything, only how often.

<h4>Do i have to do manual changes in Javascript or HTML Code?</h4>
There is no need for you to make any manual changes. The plugin does everything for you. But if you are an experienced web-developer you are free to use the php function mashsharer(); in your templates.

<h4>Is there a shortcode for pages and posts?</h4>
Use the shortcode [mashshare] to embed the Share Buttons in pages or posts.

<h4>Why do i get a blank page when i try to activate or uninstall the plugin</h4>
Most times this is a result of some php server limits. Check your logfiles to see which values must be increased.
I can also assist you with such problems, but keep in mind that they are mostly not caused by this plugin.

<h4>When i click on the share buttons, nothing happens and no popup window</h4>
Mostly that is because you are using any third party and outdated theme which is not coded very well by the author and is not using the Wordpress API for embeding external plugin script.
For Mashsharer make sure your website source contains the script /mashsharer/assets/mashsharer.js

So if you have no chance to update or change your theme do some hardcoding and put the following line into the head template of your theme file:
`<script type='text/javascript' src='http://yourwebsite.com/wp-content/plugins/mashsharer/assets/mashsharer.js?ver=1.1'></script>`



== Official Site ==
* http://www.digitalsday.com

== Installation ==
1. Download the plugin "Mashshare" , unzip and place it in your wp-content/plugins/ folder. You can alternatively upload and install it via the WordPress plugin backend.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Select Plugins->Mashshare

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png
3. screenshot-4.png

== Changelog ==

= 1.1.8 =
* Fix: Change font-size to 13px
* New: Install Addons

= 1.1.7 =
* Fix: changed mashsharer() to mashsharer('');

= 1.1.6 =
* New: graphical icons

= 1.1.5 =
* Fix: Broken Layout when no page option, (frontpage, posts, pages) is activated

= 1.1.4 =
* New: Support for more social networks (background work)
* New: Allow shortcode in text widgets
* New: Option to allow or prevent share buttons on frontpage

* Fix: No bgcolor for count
* Fix: Use onlick instead javascript in href. Prevents issues with YOAST analytics plugin

= 1.1.3 =
* Fix: Sanitation fix for international languages. E.g. french

= 1.1.2 =
Fix: Disable share buttons on frontpage
Fix: Prevent share buttons double shown on pages.
Fix: Disable Share Button in feeds

= 1.1.1 =

Fix: Error in sharing title in EDD easy digital download and other third party plugins

= 1.1.0 = 
* Fix: Disable sharer in excerpts
* New: Add support contact data
* New: Change public name to mashshare (shorter is better sometimes)
* New: Add Shortcode [mashshare]

= 1.0 = 
* First release