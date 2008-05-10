=== Global Translator ===
Tags: translator, multilanguage, automatic translator, google translations, babelfish, promt, freetranslations, widget
Author: Davide Pozza
Contributors: 
Donate link: http://www.nothing2hide.net/donate_global_translator.php
Requires at least: 2.0
Tested up to: 2.5
Stable Tag: 0.9

Automatically translates your blog in fourteen different languages!

== Description ==

Global Translator automatically translates your blog in the following fourteen different languages:
English, French, Italian, German, Portuguese, Spanish, Japanese, Korean, Chinese, Arabic, Russian, 
Greek, Dutch and Norwegian.
The number of available translations will depend on your blog language and the translation engine you will chose to use.
Main features:

* Four different translation engines: Google Translation Engine, Babel Fish, Promt, FreeTranslations
* Search Engine Optimized: it uses the permalinks by adding the language code at the beginning of all your URI. 
	For example the english version on www.domain.com/mycategory/mypost will be automatically transformed in 
	www.domain.com/en/mycategory/mypost 
*	Fast Caching System: a built-in cache is provided in order to reduce the online connections to the translation engines 
* Fully configurable layout: you can easily customize the appearance of the translation bar by choosing between a TABLE 
	or DIV layout for the flags bar and by selecting the number of translations to make available to your visitors 

For the latest information and changelog visit the website

http://www.nothing2hide.net/wp-plugins/wordpress-global-translator-plugin/

== Installation ==

1. 	Upload the folder "global-translator" to the "wp-content/plugins" directory.
2. 	Activate the plugin through the 'Plugins' menu in WordPress. 
3.	From the main menu choose "Options->Global Translator" and select 
		your blog language and your preferred configuration options then select "Update Options".

How to upgrade
Uninstall the previous version and follow the Installation instructions.

== Configuration ==

If your theme is widged-enabled, just choose "Presentation->Widgets" from the administration main menu
and drag the "Global translator" widget on the preferred position on your sidebar.
If your theme is not widgetized, just add the following php code (usually to the sidebar.php file):  

<?php if(function_exists("gltr_build_flags_bar")) { gltr_build_flags_bar(); } ?>

After this simple operation, a bar containing the flags that represents all the available translations 
for your language will appear on your blog.

== Frequently Asked Questions ==


= Page Not Found (404) when clicking on a translation flag =

This is often due to a conflict with another plugin or to a custom .htaccess file which doesn't permit Global Translator 
to add its custom permalink rules. In order to identify the problem, try to deactivate all the other existing plugins and 
if nothing change check your .htaccess file and comment out all the non-standard rewrite rules. If you discover a conflicting 
plugin please send me an email (davide at nothing2hide.net).

= Error 403 when using Google Translations engine =

If Google receives too many translation requests for a single IP address (it usually happens when your site is crawled by 
spiders or bots which perform massive requests), it sometimes decides to temporarily block requests from your IP.
At now you have three different options:
	1. make sure to enable caching and ban prevention from the Global Translator options page
	2. try another translation engine 
	3. block bots access to your translated URL paths (/en/*, /fr/*, ...) on your robots.txt file

= The translated page has a bad/broken layout =

This is due to the translation engine action. I cannot do anything in order to prevent this problem :-)
I suggest you to try all the translation engines in order to choose the best one for your blog layout

= How do I upgrade Global Translator? =

In general this just requires that you replace the existing files with the new ones. Sometimes its a good idea to delete all 
the files in wp-content/plugins/global-translator/ and re-upload them fresh.

= I've just changed my perlalinks structure and Global Translator doesn't translate anymore =

Every time you update the permalinks structure of your blog, the custom rules previously added by Global Translator are overriden.
To solve the problem you must just refresh the Global Translator Options ("Update Options" button) on the administrative area.
