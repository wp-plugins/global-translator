Installation
============
Upload the folder "global-translator" into your "wp-content/plugins" directory.
Log in to Wordpress Administration area, choose "Plugins" from the main menu, find "Global Translator" 
and click the "Activate" button. From the main menu choose "Options->Global Translator" and select 
your blog language and your preferred configuration options then select "Update Options".

Upgrading
=========
Uninstall the previous version and follow the Installation instructions.

Configuration
=============
If your theme is widged-enabled, just choose "Presentation->Widgets" from the administration main menu
and drag the "Global translator" widget on the preferred position on your sidebar.
If your theme is not widgetized, just add the following php code (usually to the sidebar.php file):  
<?php if(function_exists("gltr_build_flags_bar")) { gltr_build_flags_bar(); } ?>

After this simple operation, a bar containing the flags that represents all the available translations 
for your language will appear on your blog.

Uninstallation
==============
Log in to Wordpress Administration area, choose "Plugins" from the main menu, find the name of the 
plugin "Global Translator", and click the "Deactivate" button.