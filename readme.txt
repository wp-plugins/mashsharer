﻿=== Share Button Mashshare ===
Author URL: https://www.mashshare.net
Plugin URL: https://www.mashshare.net
Contributors: Rene Hermenau
Donate link: https://www.mashshare.net/buy-me-a-coffee/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: Mashable, Share button, share buttons, Facebook Share button, Twitter Share Button, Social Share, Social buttons, Share, Google+, Twitter, Facebook, Digg, Email, Stumble Upon, Linkedin,+1, add to any, AddThis, addtoany, admin, bookmark, bookmarking, bookmarks, buffer, button, del.icio.us, Digg, e-mail, email, Facebook, facebook like, google, google plus, google plus one, icon, icons, image, images, Like, linkedin, links, lockerz, page, pages, pin, pin it, pinit, pinterest, plugin, plus 1, plus one, Post, posts, Reddit, save, seo, Share, Shareaholic, sharedaddy, sharethis, sharing, shortcode, sidebar, sociable, social, social bookmarking, social bookmarks, statistics, stats, stumbleupon, svg, technorati, tumblr, tweet, twitter, vector, widget, wpmu
Requires at least: 3.1+
Tested up to: 3.9.2
Stable tag: 2.0.3

Mashshare share buttons plugin is a high-performance share functionality inspired by the great website Mashable for Facebook and Twitter


== Description == 

> Mashshare Share Buttons shows the total share counts of Facebook and Twitter at a glance 
It puts some beautiful and clean designed Share Buttons on top and end of your posts to get the best most possible social share feedback from your user.
It´s inspired by the Share buttons Mashable is using on his website.

<h3> Mashshare demo </h3>

[Share Buttons](http://www.mashshare.net/?ref=1 "Share-Buttons - Mashable inspired Share Buttons")


This plugin is in active development and will be updated on a regular basis - Please do not rate negative before i tried my best to solve your issue. Thanks buddy!

= Main features Features =

* Performance improvement for your website as no external scripts and count data is loaded
* Privacy protection for your user - No permanent connection to Facebook, Twitter and Google needed for sharing
* High-Performance caching functionality. You decide how often counts are updated.
* All counts will be collected in your database and loaded first from cache. No further database requests than.
* Up to 10.000 free daily requests
* Up to 40.000 free additional daily requests with an api key (Get it free at sharedcount.com)
* Social buttons works with every Theme
* Works on pages and posts
* Automatic embedding or manual via Shortcode into posts and pages
* Simple installation and setup
* Uninstaller: Removes all plugin tables and settings in the WP database
* Service and support by the author
* Periodic updates and improvements. (Feel free to tell me your demand)
* More Share Buttons are coming soon. 

New in v. 2.x

* New: Improved performance
* New: Option to disable share count completely  (no sql queries will be generated any longer)
* New: Shortcode option to disable share counts
* New: Check if curl is working on the server
* New: Option to disable share cache for testing purposes
* New: Use of sharp and crisp clear font icons instead png icons
* New: Button 'extra content' for content slider subcribe forms or any other content
* New: Use a link behind the Subscribe button instead the toggle slider
* New: Complete rewrite of css for easier modifications
* New: Improved extension system
* New: Improved backend, new Add-On page
* New: Multi language capable, *.po files
* New: Change color of share counts via setting
* New: Count up animation for share counts (Does not work for shortcodes and on blog pages)
* New: HTML5 Tag <aside> wrapped around to tell search engines that the share buttons are not part of the content
* New: Plus button moves to end of share buttons when activated and does not stay longer in place.
* New: Drag and drop sort order of services.
* New: Enable desired services with one click
* New: Choose which network should be visible all the time This ones will be large sized by default. Other ones are behind the plus sign
* New: Two different share button styles includes
* New: Choose border radius of the buttons from settings
* New: Keep settings when plugin is uninstalled - optional
* New: Custom CSS field

**Shortcodes**

* Use `[mashshare]` anywhere in pages or post's text to show the buttons and total count where you like to at a custom position.
Buttons are shown exactly on the place where you use the shortcode in your content.

There are more shortcodes:

 `[mashshare shares="false" buttons="true"]` for buttons without shares
 `[mashshare shares="true" buttons="false"]` for sharecount without buttons

* For manual insertion of the Share Buttons in your template files use the following php code where you want to show your Mash share buttons:`do_action('mashshare');`
Configure the Share buttons sharing function in the settings page of the plugin.
* Change the color of Mashshare count with setting option.

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

<h4>Why is Facebook only sharing the URL and not the title and description of my page?</h4>
Facebook does not longer supports custom titles, descriptions and images in the sharer.php but you can use open graph meta tags to show the desired custom formats. So if you theme does not suppport open graph meta tags (you see them in the html header as og: tags) use a plugin. Personally i use 'NextGEN Facebook Open Graph', which is great and easy to use:
http://wordpress.org/plugins/nextgen-facebook/

After installation you can check with the Facebook debugger how Facebook is seeing your site: 
http://wordpress.org/plugins/nextgen-facebook/


== Official Site ==
* http://www.mashshare.net

== Installation ==
1. Download the share button plugin "Mashshare" , unzip and place it in your wp-content/plugins/ folder. You can alternatively upload and install it via the WordPress plugin backend.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Select Plugins->Mashshare

== Screenshots ==

1. Share buttons possible styles
2. Share button Visual settings
3. Mashshare General settings
4. Social share button networks (included are Facebook, Twitter and Subscribe)

== Changelog ==

Attention: This 2.x version of Mashshare share buttons is a major update and uses new css3 share buttons and vector scalable icon fonts which are much easier to modify, sharper and more beautiful. 
As a drawback your old css styles will no longer be compatible with this share buttons. 
Please make sure that you have the latest responsive Add-On and network Add-On before you install it.

If you already use an older version of Mashshare and you did a lot of customizing work for your buttons,
test this update first before you install it. Converting your old share buttons to the new ones will be easy 
and you can contact me if you need any help: info@mashshare.net

Yours, René

See release notes and changelog at:
https://www.mashshare.net/changelogs/mashshare/changelog.txt

= 1.2.4 =
* New: Option to round the shares e.g. 1.5k instead 1500

= 1.2.3 =
* Fix: linkedIn Sharebutton

= 1.2.2 =
* Share button Compatibility for WordPress 3.9.1
* Change Share button api.sharedcount.com to free.sharedcount.com (more reliable)

= 1.2.1 =
* Fix: Header already send due to wp_redirect 

= 1.2.0 =
* Change: FAQ
* Fix: Share button Facebook URL not shared on mobile devices.

= 1.1.9 =
* Fix: Change share button rating link in admin
* Fix: Change share button check for addon
* Fix: Sanitation for ampersand and hash / urlencode

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
