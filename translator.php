<?php


/*  Copyright 2006  Davide Pozza  (email : davide@nothing2hide.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/* Credits:
Special thanks also to Jason F. Irwin, Ibnu Asad, Ozh, ttancm, Fable, Satollo and the many others who have provided feedback, spotted bugs, and suggested improvements.
*/

/* *****INSTRUCTIONS*****

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


***********************


Change Log
0.9.1
- Activated new Prompt configuration
- Fixed little issue with Portuguese translation
- Fixed Swedish and Czech flags icons (thanks to Mijk Bee)
- Added event-based cache invalidation and increased default cache timeout

0.9
- Added support for 10 new languages for Google Translations engine: Bulgarian, Czech, Croat, Danish, Finnish, Hindi, Polish, Rumanian, Swedish, Greek, Norwegian
- Updated flags icons (provided by famfamfam.com)

0.8
- Updated Prompt engine
- Added experimental translation engines ban prevention system
- Improved caching management
- Improved setup process
- Fixed a bug on building links for "Default Permalink Structure"

0.7.2
- Fixed other bug on building links for "Default Permalink Structure"
- Optimized translation flags for search engines and bots
- changed cached filename in order to prevent duplicates
- added messages for filesystem permissions issues
- updated Google translation languages options (added Greek and Dutch)

0.7.1
- Fixed bug "Call to a member function on a non-object in /[....]/query.php". 
  It happens only on certain servers with a custom PHP configuration
- Fixed bug on building links for "Default Permalink Structure"

0.7
- Added two new translation engines: FreeTranslation and Promt Online Translation
- Added USER-AGENT filter in order to prevent unuseless connections to the translation services
- Added support for Default Permalink Structure (i.e.: "www.site.com/?p=111")
- Added widgetization: Global Translator is now widgetized!
- Fixed some bugs and file permission issues
- Excluded RSS feeds and trackback urls translation
- Fixed some problems on translated pages 

0.6.2
- Updated in order to handle the new Babelfish translation URL.(Thanks to Roel!)

0.6.1
- Fixed some layout issues
- Fixed url parsing bugs

0.6
- Fixed compatibility problem with Firestats
- Added the "gltr_" prefix for all the functions names in order to reduce naming conflicts with other plugins
- Added new configuration feature: now you can choose to enable a custom number of translations
- Removed PHP short tags
- Added alt attribute for flags IMG
- Added support to BabelFish Engine: this should help to solve the "403 Error" by Google
- Added my signature to the translation bar. It can be removed, but you should add a link to my blog on your blogroll.
- Replaced all the flags images
- Added help messages for cache support
- Added automatic permalink update system: you don't need to re-save your permalinks settings
- Fixed many link replacement issues
- Added hreflang attribute to the flags bar links
- Added id attribute to <A> Tag for each flag link
- Added DIV tag for the translation bar
- Added support for the following new languages: Russian, Greek, Dutch

0.5
- Added BLOG_URL variable
- Improved url replacement
- Added caching support (experimental): the cached object will be stored inside the following directory:
"[...]/wp-content/plugins/global-translator/cache".
- Fixed japanese support (just another bug)

0.4.1
- Better request headers
- Bug fix: the translated page contains also the original page

0.4
- The plugin has been completely rewritten
- Added permalinks support for all the supported languages
- Added automatic blog links substitution in order to preserve the selected language.
- Added Arabic support
- Fixed Japanese support
- Removed "setTimeout(180);" call: it is not supported by certain servers
- Added new option which permits to split the flags in more than one row

0.3/0.2
- Bugfix version
- Added Options Page

0.1
- Initial release
*/

require_once (dirname(__file__).'/header.php');

define('DEBUG', false);


define('FLAG_BAR_BEGIN', '<!--FLAG_BAR_BEGIN-->');
define('FLAG_BAR_END', '<!--FLAG_BAR_END-->');
define('USER_AGENT','Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
define('LANGS_PATTERN', 'it|ko|zh-CN|pt|en|de|fr|es|ja|ar|ru|el|nl|zh|zt|no|bg|cs|hr|da|fi|hi|pl|ro|sv');
define('LANGS_PATTERN_WITH_SLASHES', '/it/|/ko/|/zh-CN/|/pt/|/en/|/de/|/fr/|/es/|/ja/|/ar/|/ru/|/el/|/nl/|/zh/|/zt/|/no/|/bg/|/cs/|/hr/|/da/|/fi/|/hi/|/pl/|/ro/|/sv/');
define('LANGS_PATTERN_WITHOUT_FINAL_SLASH', '/it|/ko|/zh-CN|/pt|/en|/de|/fr|/es|/ja|/ar|/ru|/el|/nl|/zh|/zt|/no|/bg|/cs|/hr|/da|/fi|/hi|/pl|/ro|/sv');


define('BASE_LANG', get_option('gltr_base_lang'));
define('BAR_COLUMNS', get_option('gltr_col_num'));
define('USE_CACHE', get_option('gltr_use_cache'));
define('BAN_PREVENTION', get_option('gltr_ban_prevention'));
define('HTML_BAR_TAG', get_option('gltr_html_bar_tag'));
define('TRANSLATION_ENGINE', get_option('gltr_my_translation_engine'));
define('BLOG_URL', get_settings('siteurl'));
define('BLOG_HOME', get_settings('home'));
define('BLOG_HOME_ESCAPED', str_replace('/', '\\/', BLOG_HOME));

$gltr_result = '';
$gltr_engine = $gltr_available_engines[TRANSLATION_ENGINE];

add_filter('query_vars', 'gltr_insert_my_rewrite_query_vars');
add_action('parse_query', 'gltr_insert_my_rewrite_parse_query');
add_action('admin_menu', 'gltr_add_options_page');
add_action('init', 'gltr_translator_init');

add_action('publish_post', 'erase_common_cache_files');
add_action('edit_post', 'erase_common_cache_files');
add_action('delete_post', 'erase_common_cache_files');
add_action('publish_phone', 'erase_common_cache_files');
add_action('trackback_post', 'erase_common_cache_files');
add_action('pingback_post', 'erase_common_cache_files');
add_action('comment_post', 'erase_common_cache_files');
add_action('edit_comment', 'erase_common_cache_files');
add_action('wp_set_comment_status', 'erase_common_cache_files');
//add_action('delete_comment', 'erase_common_cache_files');
//add_action('switch_theme', 'erase_common_cache_files');


function gltr_translator_init()
{
  global $wp_rewrite;
  if (isset($wp_rewrite) && $wp_rewrite->using_permalinks()) {
    define('REWRITEON', true);
    define('LINKBASE', $wp_rewrite->root);
  } else {
    define('REWRITEON', false);
    define('KEYWORDS_REWRITEON', '0');
    define('LINKBASE', '');
  }
  if (REWRITEON) {
    add_filter('generate_rewrite_rules', 'gltr_translations_rewrite');
  }
  //gltr_debug("GT $gltr_VERSION initialized.");
}

function gltr_build_translation_url($srcLang, $destLang, $urlToTransl)
{
  global $gltr_engine;
  $tokens = array('${URL}', '${SRCLANG}', '${DESTLANG}');
  $srcLang = $gltr_engine->decode_lang_code($srcLang);
  $destLang = $gltr_engine->decode_lang_code($destLang);
  $values = array($urlToTransl, $srcLang, $destLang);
  $res = str_replace($tokens, $values, $gltr_engine->get_base_url());
  if ($gltr_engine->get_name() == 'freetransl'){
    $tmp_buf = gltr_http_get_content("http://www.freetranslation.com/");
    $matches = array();
    preg_match(
      '/\<input type="hidden" name="username" value="([^"]*)" \/>\<input type="hidden" name="password" value="([^"]*)" \/>/',$tmp_buf,$matches);
      
    $res .= "&username=$matches[1]&password=$matches[2]";
  }
  return $res;
} 


function gltr_translate($lang, $url)
{
  global $gltr_engine;
  $url = gltr_get_self_url();

  if (REWRITEON) {
    $pattern1 = '/(' . BLOG_HOME_ESCAPED . ')(\\/(' . LANGS_PATTERN . ')\\/)(.+)/';
    $pattern2 = '/(' . BLOG_HOME_ESCAPED . ')\\/(' . LANGS_PATTERN . ')[\\/]{0,1}$/';

    if (preg_match($pattern1, $url)) {
      $url_to_translate = preg_replace($pattern1, '\\1/\\4', $url);
    } elseif (preg_match($pattern2, $url)) {
      $url_to_translate = preg_replace($pattern2, '\\1', $url);
    }
  } else {
    $url_to_translate = preg_replace('/(.*)(lang=' . LANGS_PATTERN . ')(.*)/', '\\1\\3',
      $url);
  }

  $resource = gltr_build_translation_url(BASE_LANG, $lang, $url_to_translate);
  
  $buf = gltr_http_get_content($resource);

  return gltr_clean_translated_page($buf, $lang);

}

function gltr_http_get_content($resource){
  $isredirect = true;
  $redirect = null;

  while ($isredirect) {
    $isredirect = false;
    if (isset($redirect_url)) {
      $resource = $redirect_url;
    }

    $url_parsed = parse_url($resource);
    $host = $url_parsed["host"];
    $port = $url_parsed["port"];
    if ($port == 0)
      $port = 80;
    $path = $url_parsed["path"];
    if (empty($path))
      $path = "/";
    $query = $url_parsed["query"];
    $http_q = $path . '?' . $query;

    $req = gltr_build_request($host, $http_q);

    $fp = @fsockopen($host, $port, $errno, $errstr);

    if (!$fp) {
      return "$errstr ($errno)<br />\n";
    } else {
      fputs($fp, $req, strlen($req)); // send request
      //gltr_debug("Translation request: $req");
      $buf = '';
      $isFlagBar = false;
      $flagBarWritten = false;
      $beginFound = false;
      $endFound = false;
      $inHeaders = true;
			$prevline='';
      while (!feof($fp)) {
        $line = fgets($fp);
        if ($inHeaders) {
          if (trim($line) == '' && trim($prevline) == '') {
            $inHeaders = false;
            continue;
          }
          $prevline = $line;
          if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
            // Skip to the next header
            continue;
          } 
          $key = strtolower(trim($m[1]));
          $val = trim($m[2]);
					if ($key == 'location') {
            $redirect_url = $val;
            $pos = strpos($redirect_url, 'http://sorry.google.com');
						//gltr_debug("redirect[$pos]:: $redirect_url");
            if ($pos !== false){
            	$buf = "<html><body><center><br /><br /><b>Sorry, the translation engine is temporarily not available. Please try again later</b><br /><br /><a href='".get_settings('home')."'>Home page</a></center></body></html>";
            	$isredirect = false;
            } else {
            	$isredirect = true;
            }
          	break;
          }
          continue;
        }
				
        $buf .= $line . "\n";
      } //end while
    }
    fclose($fp);
  } //while($isredirect) 
  return $buf; 
}

function gltr_clean_translated_page($buf, $lang)
{
  global $gltr_engine;

  //Clean the links modified by the translation engine
  //$buf = urldecode ($buf);

  $buf = preg_replace($gltr_engine->get_links_pattern(), $gltr_engine->get_links_replacement(), $buf);
  $buf = urldecode($buf);

	if (!gltr_is_browser() && BAN_PREVENTION)
	  $nofollow = "rel=\"nofollow\"";
	else
	  $nofollow = " ";
  if (REWRITEON) {
    $pattern = "/<a[^>]*href=\"" . BLOG_HOME_ESCAPED . "(((?![\"])(?!\/trackback\/)(?!\/feed\/).)*)\"([^>]*)>/i";
  	$repl = "<a $nofollow href=\"" . BLOG_HOME . '/' . $lang . "\\1\" \\5>";
    $buf = preg_replace($pattern, $repl, $buf);
  } else {
    $pattern = "/<a[^>]*href=\"" . BLOG_HOME_ESCAPED . "\/\?(((?![\"])(?!\/trackback\/)(?!\/feed\/).)*)\"([^>]*)>/i";
    $repl = "<a $nofollow href=\"" . BLOG_HOME . "?\\1&lang=$lang\" \\5>";
    $buf = preg_replace($pattern, $repl, $buf);
    
    $pattern = "/<a[^>]*href=\"" . BLOG_HOME_ESCAPED . "[\/]{0,1}\"([^>]*)>/i";
    $repl = "<a $nofollow href=\"" . BLOG_HOME . "?lang=$lang\" \\1>";
    $buf = preg_replace($pattern, $repl, $buf);
  }

  //let's remove custom header added by certain engines
  if (TRANSLATION_ENGINE == 'promt') {
    //$buf = preg_replace("/\<div class='PROMT_HEADER'(.*)\<\/div\>/i", "", $buf);
    //$buf = preg_replace("/\<span class=\"UNKNOWN_WORD\"\>([^\<]*)\<\/span\>/i", "\\1",$buf);
    $buf = preg_replace("/onmouseout=\"OnMouseLeaveSpan\(this\)\"/i", "",$buf);
    $buf = preg_replace("/onmouseover=\"OnMouseOverSpanTran\(this,event\)\"/i", "",$buf);
    $buf = preg_replace("/<span class=\"src_para\">/i", "<span style=\"display:none;\">",$buf);
  } else if (TRANSLATION_ENGINE == 'freetransl') {
    $buf = preg_replace("/\<div(.*)http:\/\/www\.freetranslation\.com\/images\/logo\.gif(.*)\<\/div\>/i", "", $buf);
    $buf = str_replace(array("{L","L}"), array("",""), $buf);
  } else if (TRANSLATION_ENGINE == 'google') {
    $buf = preg_replace("/_setupIW()/", "", $buf);
  }

  //insert the flags bar
	$bar = gltr_get_flags_bar();

  if (strpos($buf, FLAG_BAR_BEGIN) > 0 && strpos($buf, FLAG_BAR_END) > 0) {
    $buf = substr($buf, 0, strpos($buf, FLAG_BAR_BEGIN)) . $bar . substr($buf,
      strpos($buf, FLAG_BAR_END) + strlen(FLAG_BAR_END));
  } else {
    gltr_debug("Flags bar tokens not found: unhandled page type (RSS feed?)");
  }
  return $buf;
}

function gltr_build_request($host, $http_req)
{
  $res = "GET $http_req HTTP/1.0\r\n";
  $res .= "Host: $host\r\n";
  $res .= "User-Agent: " . USER_AGENT . " \r\n";
  //$res .= "Content-Type: application/x-www-form-urlencoded\r\n";
  $res .= "Content-Length: 0\r\n";
  $res .= "Connection: close\r\n\r\n";
  return $res;
}


function gltr_get_flags_bar()
{
  global $gltr_engine, $wp_query;
	if (!isset($gltr_engine)||$gltr_engine == null){
		gltr_debug("WARNING: Options not set!!");
		return "<b>Global Translator not configured yet.</b>";
	}

  $use_table = false;
  if (HTML_BAR_TAG == 'TABLE')
    $use_table = true;
  $num_cols = BAR_COLUMNS;

  $buf = '';
  if ($num_cols < 0)
    $num_cols = 0;
	
  
  $transl_map = $gltr_engine->get_languages_matrix();

  $translations = $transl_map[BASE_LANG];

  $transl_count = count($translations); 

  $buf .= "\n" . FLAG_BAR_BEGIN; //initial marker

	$is_original_page = !isset($wp_query->query_vars['lang']);
	$is_browser = gltr_is_browser();
	$is_search_engine = !$is_browser;
	$is_indexable_page = (	(function_exists("is_single") && is_single())	|| (function_exists("is_page") 	&& is_page()) 	|| (function_exists("is_home") 	&& is_home()) );

  //if ( ($is_original_page && $is_indexable_page && $is_search_engine) || $is_browser || !BAN_PREVENTION){

    if ($use_table)
      $buf .= "<table border='0'><tr>";
    else
      $buf .= "<div id=\"translation_bar\">";
  
    $curr_col = 0;
  
    //filter preferred
    $preferred_transl = array();
    foreach ($translations as $key => $value) {
      if ($key == BASE_LANG || in_array($key, get_option('gltr_preferred_languages')))
        $preferred_transl[$key] = $value;
    }
    
    foreach ($preferred_transl as $key => $value) {
      if ($curr_col >= $num_cols && $num_cols > 0) {
        if ($use_table)
          $buf .= "</tr><tr>";
        $curr_col = 0;
      }
      $flg_url = gltr_get_translated_url($key, gltr_get_self_url());
      $flg_image_url = gltr_get_flag_image($key);
      if ($use_table)
        $buf .= "<td>";
      $buf .= "<a id='flag_$key' href='$flg_url' hreflang='$key' $lnk_attr><img id='flag_img_$key' src='$flg_image_url' alt='$value flag' title='$value'  border='0' /></a>";
      if ($use_table)
        $buf .= "</td>";
      if ($num_cols > 0)
        $curr_col += 1;
    }
  
    while ($curr_col < $num_cols && $num_cols > 0) {
      if ($use_table)
        $buf .= "<td>&nbsp;</td>";
      $curr_col += 1;
    }
  
  
    if ($num_cols == 0)
      $num_cols = count($translations);
      
    //***************************************************************************************
    //Yes, you can remove my website link from the flags bar, but you should put it on another place 
    //on your blog, for example on your sidebar (i.e. inside your blogroll).
    //This plugin is hard to develop and maintain and I freely redistribute it; I'm only asking 
    //you a backlink to my website (http://www.nothing2hide.net). This will be very appreciated!! 
    //Thanks!    
    //
    $n2hlink = "<a style=\"font-size:9px;\" href=\"http://www.nothing2hide.net\">By N2H</a>";
    if ($use_table)
      $buf .= "</tr><tr><td colspan=\"$num_cols\">$n2hlink</td></tr></table>";
    else
      $buf .= "<div id=\"transl_sign\">$n2hlink</div></div>";
  //} else {
  //	$n2hlink = "<a href=\"http://www.nothing2hide.net\">By N2H</a>";
  //  $buf .= $n2hlink;
  //}    
  $buf .= FLAG_BAR_END . "\n"; //final marker
  return $buf;
}

function gltr_build_flags_bar()
{
  echo (gltr_get_flags_bar());
}

//ONLY for backward compatibility!
function build_flags_bar()
{
  echo (gltr_get_flags_bar());
}

function gltr_get_translated_url($language, $url)
{
  if (REWRITEON) {

    $pattern = '/' . BLOG_HOME_ESCAPED . '\\/((' . LANGS_PATTERN . ')[\\/])*(.*)/';

    if (preg_match($pattern, $url)) {
      $uri = preg_replace($pattern, '\\3', $url);
    } else {
      $uri = '';
    }

    if ($language == BASE_LANG)
      $url = BLOG_HOME . '/' . $uri;
    else
      $url = BLOG_HOME . '/' . $language . '/' . $uri;
  } else {
    //REWRITEOFF
    $pattern1 = '/(.*)([&|\?]{1})lang=(' . LANGS_PATTERN . ')(.*)/';
    $pattern2 = '/(.*[&|\?]{1})lang=(' . LANGS_PATTERN . ')(.*)/';

    if ($language == BASE_LANG) {
      $url = preg_replace($pattern1, '\\1\\4', $url);
    } else
      if (preg_match($pattern2, $url)) {
        $url = preg_replace($pattern2, '\\1lang=' . $language . '\\3', $url);
      } else {
        if (strpos($url,'?')===false)
          $url .= '?lang=' . $language;
        else
          $url .= '&lang=' . $language;
      }

  }

  return $url;
}

function gltr_get_flag_image($language)
{
  //thanks neanton!
  $path = strstr(realpath(dirname(__file__)), 'wp-content');
  $path = str_replace('\\', '/', $path);
  return BLOG_URL . '/' . $path . '/flag_' . $language . '.png';
}

function gltr_get_self_url()
{
  $full_url = 'http';
  $script_name = '';
  if (isset($_SERVER['REQUEST_URI'])) {
    $script_name = $_SERVER['REQUEST_URI'];
  } else {
    $script_name = $_SERVER['PHP_SELF'];
    if ($_SERVER['QUERY_STRING'] > ' ') {
      $script_name .= '?' . $_SERVER['QUERY_STRING'];
    }
  }
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $full_url .= 's';
  }
  $full_url .= '://';
  if ($_SERVER['SERVER_PORT'] != '80') {
    $full_url .= $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $script_name;
  } else {
    $full_url .= $_SERVER['HTTP_HOST'] . $script_name;
  }
  return $full_url;
}

//rewrite rules definitions
function gltr_translations_rewrite($wp_rewrite)
{
  $translations_rules = array('^(' . LANGS_PATTERN . ')$' =>
    'index.php?lang=$matches[1]', '^(' . LANGS_PATTERN . ')/(.+?)$' =>
    'index.php?lang=$matches[1]&url=$matches[2]');
  $wp_rewrite->rules = $translations_rules + $wp_rewrite->rules;
}

function gltr_get_cookies()
{
  $string = '';
  while ($key = key($_COOKIE)) {
    if (preg_match("/^wordpress|^comment_author_email_/", $key)) {
      $string .= $_COOKIE[$key] . ",";
    }
    next($_COOKIE);
  }
  reset($_COOKIE);
  return $string;
}

function gltr_get_page_content($lang, $url)
{
  $page = '';
  if (USE_CACHE) {
    $hash = gltr_hashReqUri($_SERVER['REQUEST_URI']);
    gltr_debug("Hashing uri: $req to: $hash");
    $cachedir = dirname(__file__) . '/cache';

    if (!is_dir($cachedir)) {
      mkdir($cachedir, 0777);
    }

    if (!is_dir($cachedir)) {
    	gltr_debug("Creating cache dir: $cachedir");
      mkdir($cachedir, 0777);
    }
					
    $filename = $cachedir . '/' . $hash;
    
    if(file_exists($filename) && !is_readable($filename) ){
    	return "<b>Global Translator has detected a problem with your filesystem permissions:<br />The cached file <em>$filename</em> cannot be read. <br />Please chmod it to 777 or disable the caching support from the admin page.</b>";	
    }
    if(file_exists($filename) && !is_writeable($filename) ){
    	return "<b>Global Translator has detected a problem with your filesystem permissions:<br />The cached file <em>$filename</em> cannot be modified. <br />Please chmod it to 777 or disable the caching support from the admin page.</b>";	
    }
    
    if (file_exists($filename) && filesize($filename) > 0) {
      // We are done, just return the file and exit
      gltr_debug("cache: returning cached version ($hash) for url:" .
        gltr_get_self_url());
      $handle = fopen($filename, "rb");
      $page = fread($handle, filesize($filename));
      $page .= "<!--CACHED VERSION (timeout: " . CACHE_TIMEOUT . "): $unique_url_string ($hash)-->";
      fclose($handle);

      //check the cached file
      if (strpos($page, FLAG_BAR_BEGIN) <= 0 && strpos($page, FLAG_BAR_END) <= 0) {
        gltr_debug("cache: deleting BAD cached version ($filename) for url:" .
          gltr_get_self_url());
        //bad cached file
        unlink($filename);
      }
    } else {

      $page = gltr_translate($lang, $url);
      gltr_debug("cache: caching ($filename) url:" . gltr_get_self_url());
      $handle = fopen($filename, "wb");
      if (flock($handle, LOCK_EX)) { // do an exclusive lock
        fwrite($handle, $page); //write
        flock($handle, LOCK_UN); // release the lock
      } else {
        fwrite($handle, $page); //Couldn't lock the file ! Try anyway to write but it is not a good thing
      }
      fclose($handle);
      $page .= "<!--NOT CACHED VERSION: ($hash)-->";
    }

  } else {
    //Caching support disabled
   	gltr_debug("Translating $url to $lang. No cache support");
    $page = gltr_translate($lang, $url);
  }

  return $page;
}

function gltr_hashReqUri($uri){
    $req = preg_replace('/(.*)\/$/', '\\1', $uri);
    $req = preg_replace('/#.*$/', '', $req);
    $hash = str_replace(array('?','<','>',':','\\','/','*','|','"'), '_', $req);
    return $hash;
}

function gltr_filter_content($content)
{
  global $gltr_result;
  return $gltr_result;
}

function gltr_insert_my_rewrite_query_vars($vars)
{
  array_push($vars, 'lang', 'url');
  return $vars;
}

function gltr_insert_my_rewrite_parse_query($query)
{
  global $gltr_result;
  if (isset($query->query_vars['lang'])) {
  	
	  if (!is_user_agent_allowed() && BAN_PREVENTION){
  		gltr_debug("Limiting bot/crawler access to resource:$url");
  		header('HTTP/1.x 404 Not Found'); 
  		die();
  	}else {
     	$lang = $query->query_vars['lang'];
     	$url = $query->query_vars['url'];
     	if (empty($url)) {
       	$url = '';
      }
  		
      $gltr_result = gltr_get_page_content($lang, $url);
  
      ob_start('gltr_filter_content');
    }
  }
}

function gltr_add_options_page()
{
  $path = dirname(__file__);
  $pos = strrpos($path, '/') + 1;
  $option_file = substr($path, $pos) . '/options-translator.php';
  add_options_page('Global Translator Options', 'Global Translator', 8, $option_file);
}


function gltr_debug($msg)
{
  if (DEBUG) {
    $today = date("Y-m-d H:i:s ");
    $myFile = dirname(__file__) . "/debug.log";
    $fh = fopen($myFile, 'a') or die("Can't open debug file. Please manually create the 'debug.log' file (inside the 'global-translator' directory) and make it writable.");
    $ua_simple = preg_replace("/(.*)\s\(.*/","\\1",$_SERVER['HTTP_USER_AGENT']);
    fwrite($fh, $today . " [from: ".$_SERVER['REMOTE_ADDR']."|$ua_simple] - " . $msg . "\n");
    fclose($fh);
  }
}

function gltr_is_translation_engine() {
	$translation_engines = array(
	"195.131.10.152",//promt
	"209.85.136.136"
	);
	foreach ($translation_engines as $key => $value) {
    if ($_SERVER['REMOTE_ADDR'] == strtoupper($value)) {
      return true;
    }
  }
  return false;
}

function gltr_is_browser() {
  $browsers_ua = array(
  "compatible; MSIE", 
  "UP.Browser",
  "Mozilla", 
  "Opera/7", 
  "NSPlayer", 
  "Opera/6",
  "Avant Browser"
  );
  if (isset($_SERVER['HTTP_USER_AGENT']))
    $ua = strtoupper($_SERVER['HTTP_USER_AGENT']);
  else
    $ua = "";

  if ($ua == "") {
    return false;
  } else {
    while (list($key, $val) = each($browsers_ua)) {
      if (strstr($ua, strtoupper($val))) {
        return true;
      }
    }
  }
  return false;
}

function is_user_agent_allowed()
{

  $not_allowed = array("Wget", "EmailSiphon", "WebZIP", "MSProxy/2.0", "EmailWolf",
    "webbandit", "MS FrontPage", "GetRight", "AdMuncher", "Sqworm", "SurveyBot",
    "TurnitinBot", "WebMirror", "WebMiner", "WebStripper", "WebSauger", "WebReaper",
    "WebSite eXtractor", "Teleport Pro", "CherryPicker", "Crescent Internet ToolPak",
    "EmailCollect", "ExtractorPro", "NEWT ActiveX", "sexsearcher", "ia_archive",
    "NameCrawler", "Email spider", "GetSmart", "Grabber", "GrabNet", "EmailHarvest",
    "Go!Zilla", "LeechFTP", "Vampire", "SmartDownload", "Sucker", "SuperHTTP",
    "Collector", "Zeus", "Telesoft", "URLBlaze", "VobSub", "Vacuum", "Space Bison",
    "WinWAP", "3D-FTP", "Wapalizer", "DTS agent", "DA 5.", "NetAnts", "Netspider",
    "Disco Pump", "WebFetch", "DiscoFinder", "NetZip", "Express WebPictures",
    "Download Demon", "eCatch", "WebAuto", "Offline Expl", "HTTrack",
    "Mass Download", "Mister Pix", "SuperBot", "WebCopier", "FlashGet", "larbin",
    "SiteSnagger", "FlashGet", "NPBot", "Kontiki","Java","ETS V5.1",
    "IDBot", "id-search", "libwww", "lwp-trivial", "curl", "PHP/", "urllib", 
    "GT::WWW", "Snoopy", "MFC_Tear_Sample", "HTTP::Lite", "PHPCrawl", "URI::Fetch", 
    "Zend_Http_Client", "http client", "PECL::HTTP","libwww-perl");

  $allowed = array("compatible; MSIE", "T720", "MIDP-1.0", "AU-MIC", "UP.Browser",
    "SonyEricsson", "MobilePhone SCP", "NW.Browser", "Mozilla", "UP.Link",
    "Windows-Media-Player", "MOT-TA02", "Nokia", "Opera/7", "NSPlayer",
    "GoogleBot", "Opera/6", "Panasonic", "Thinflow", "contype", "klondike", "UPG1",
    "SEC-SGHS100", "Scooter", "almaden.ibm.com",
    "SpaceBison/0.01 [fu] (Win67; X; ShonenKnife)", "Internetseer","MSNBOT-MEDIA/",
    "MEDIAPARTNERS-GOOGLE","MSNBOT","Avant Browser");

  if (isset($_SERVER['HTTP_USER_AGENT']))
    $ua = strtoupper($_SERVER['HTTP_USER_AGENT']);
  else
    $ua = "";
  if ($ua == "") {
    return false;
  } else {
    while (list($key, $val) = each($not_allowed)) {
      if (strstr($ua, strtoupper($val))) {
        gltr_debug("Detected and blocked user agent: $ua");
        return false;
      }
    }
  }

  $notknown = 1;
  while (list($key, $val) = each($allowed)) {
    if (strstr($ua, strtoupper($val))) {
      $notknown = 0;
    }
  }

  if ($notknown) {
    gltr_debug("Warning: unknown user agent: $ua");
  }
  return true;
}

function erase_common_cache_files($post_ID) {
  $single_post_pattern = "";
  
	if (isset($post_ID)){
  	if (REWRITEON) {
  		$uri = substr (get_permalink($post_ID), strlen(get_option('home')) );
  		$single_post_pattern = gltr_hashReqUri($uri);
  	} else {
  		$single_post_pattern = $post_ID;
  	}
	}

  $cachedir = dirname(__FILE__) . '/cache';
  if (file_exists($cachedir) && is_dir($cachedir) && is_readable($cachedir)) {
    $handle = opendir($cachedir);
    
    
    while (FALSE !== ($item = readdir($handle))) {
    	if (REWRITEON) {
        if(	$item != '.' && $item != '..' && (
													strstr($item, '_category') || 
													preg_match('/_(' . LANGS_PATTERN . ')_[0-9]{4}_[0-9]{2}$/', $item) ||
													preg_match('/_(' . LANGS_PATTERN . ')_page_[0-9]+$/', $item) ||
													preg_match('/_(' . LANGS_PATTERN . ')_tag_(.*)$/', $item) ||
													preg_match('/_(' . LANGS_PATTERN . ')$/', $item) ||
													preg_match('/_(' . LANGS_PATTERN . ')'.$single_post_pattern.'$/', $item) )
        	) {
          $path = $cachedir.'/'.$item;
          if (file_exists($path) && is_file($path)){
  	        gltr_debug("Erase event. Erasing $path");
          	unlink($path);
          }
        }
      } else {
        if(	$item != '.' && $item != '..' && (!preg_match('/_p=[0-9]+/', $item) || preg_match('/_p='.$single_post_pattern.'/', $item))) {
          $path = $cachedir.'/'.$item;
          if (file_exists($path) && is_file($path)){
  	        gltr_debug("Erase event [no permalink]. Erasing $path");
          	unlink($path);
          }
        }
      }
    }
    $message = "Cache dirs successfully erased.";
  } else {
    //$message = "Unable to erase cache or cache dir '$cachedir' doesn't exist.";
    //break;
  }
  
}

function widget_global_translator_init() {

  if(!function_exists('register_sidebar_widget')) { return; }
  function widget_global_translator($args) {
    extract($args);
    echo $before_widget . $before_title . $after_title;
    gltr_build_flags_bar();
    echo $after_widget;
  }
  register_sidebar_widget('Global Translator','widget_global_translator');

}
add_action('plugins_loaded', 'widget_global_translator_init');
?>