<?php
require_once (dirname(__file__).'/header.php');

function gltr_get_dir_size($dir){
	$cachesize = 0;
  if (file_exists($dir) && is_dir($dir) && is_readable($dir)) {
    $handle = opendir($dir);
    while (FALSE !== ($item = readdir($handle))) {
      if($item != '.' && $item != '..') {
        $path = $dir.'/'.$item;
        if (file_exists($path) && is_file($path))
        	$cachesize += filesize($path);
      }
    }
	  closedir($handle);	
  }
  return $cachesize;
}
function gltr_get_cache_size(){
  $cachedir = dirname(__FILE__) . '/cache';
	return gltr_get_dir_size($cachedir);
}
function gltr_get_stale_size(){
  $cachedir = dirname(__FILE__) . '/cache/stale';
	return gltr_get_dir_size($cachedir);
}

function gltr_get_last_cached_file_time(){
	$res = -1;
  $last_connection_time = get_option("gltr_last_connection_time");
  if ($last_connection_time > 0){
	  $now = time();
	  $res = $now - $last_connection_time;
  }
	return $res;
}

load_plugin_textdomain('gltr'); // NLS

/*Lets add some default options if they don't exist*/
add_option('gltr_base_lang', 'en');
add_option('gltr_col_num', '0');
add_option('gltr_html_bar_tag', 'TABLE');
add_option('gltr_my_translation_engine', 'google');
add_option('gltr_preferred_languages', array());
add_option('gltr_ban_prevention', true);
add_option('gltr_enable_debug', false);
add_option('gltr_conn_interval',300);
add_option('gltr_sitemap_integration',false);
add_option("gltr_last_connection_time",0);
add_option("gltr_translation_status","unknown");
	
	
$location = get_option('siteurl') . '/wp-admin/admin.php?page=global-translator/options-translator.php'; // Form Action URI



/*check form submission and update options*/

if (isset($_POST['stage'])){
	//submitting something
	$gltr_base_lang 						= $_POST['gltr_base_lang'];
	$gltr_col_num 							= $_POST['gltr_col_num'];
	$gltr_html_bar_tag 					= $_POST['gltr_html_bar_tag'];
	$gltr_my_translation_engine = $_POST['gltr_my_translation_engine'];
	$gltr_conn_interval 				= $_POST['gltr_conn_interval'];
	

	if (isset($_POST['gltr_preferred_languages']))
		$gltr_preferred_languages = $_POST['gltr_preferred_languages'];
	
	if(isset($_POST['gltr_enable_debug'])) 
		$gltr_enable_debug = true; 
	else 
		$gltr_enable_debug = false;

	if(isset($_POST['gltr_ban_prevention'])) 
		$gltr_ban_prevention = true; 
	else 
		$gltr_ban_prevention = false;
	
	if(isset($_POST['gltr_sitemap_integration'])) 
		$gltr_sitemap_integration = true; 
	else 
		$gltr_sitemap_integration = false;
	
	
	if ('change' == $_POST['stage']) {
		//recalculate some things
		$gltr_my_translation_engine = $_POST['gltr_my_translation_engine'];
		$gltr_preferred_languages = get_option('gltr_preferred_languages');
	} else if ('process' == $_POST['stage']){
	  if(!empty($_POST["gltr_erase_cache"])) {
	  	//Erase cache button pressed
  	  $cachedir = dirname(__FILE__) . '/cache';
	    if (file_exists($cachedir) && is_dir($cachedir) && is_readable($cachedir)) {
	      $handle = opendir($cachedir);
	      while (FALSE !== ($item = readdir($handle))) {
	        if($item != '.' && $item != '..' && !is_dir($item)) {
	          $path = $cachedir.'/'.$item;
	          if (file_exists($path) && is_file($path))
	          	unlink($path);
	        }
	      }
	      closedir($handle);
	      $message = "Cache dirs successfully erased.";
	    } else {
	      //$message = "Unable to erase cache or cache dir '$cachedir' doesn't exist.";
	      //break;
	    }
		  
	  } else {
	  	//update options button pressed
	  	$iserror = false;
	    
	    if (count ($gltr_preferred_languages) == 0) {
	      $message .= "Error: you must choose almost one of the available translations.";
	      $iserror = true;
	    }
	    
	    if(!$iserror) {

	      update_option('gltr_base_lang', $_POST['gltr_base_lang']);
	      update_option('gltr_col_num', $_POST['gltr_col_num']);
	      update_option('gltr_html_bar_tag', $_POST['gltr_html_bar_tag']);
	      update_option('gltr_my_translation_engine', $_POST['gltr_my_translation_engine']);
	      update_option('gltr_preferred_languages', array());
	      update_option('gltr_preferred_languages', $_POST['gltr_preferred_languages']);
				update_option("gltr_last_connection_time",0);
				update_option("gltr_translation_status","unknown");
	      
	      $conn_int = $_POST['gltr_conn_interval'];
	      if (!is_numeric($conn_int))$conn_int = 300;
	      update_option('gltr_conn_interval', $conn_int);
				$gltr_conn_interval = $conn_int;
	
	      if(isset($_POST['gltr_ban_prevention']))
	        update_option('gltr_ban_prevention', true);
	      else
	        update_option('gltr_ban_prevention', false);

	      if(isset($_POST['gltr_sitemap_integration']))
	        update_option('gltr_sitemap_integration', true);
	      else
	        update_option('gltr_sitemap_integration', false);

	
	
	      if(isset($_POST['gltr_enable_debug']))
	        update_option('gltr_enable_debug', true);
	      else
	        update_option('gltr_enable_debug', false);
	
	
				$wp_rewrite->flush_rules();
	      $message = "Options saved.";
	    }
	  }
	}		
} else {
	//page loaded by menu: retrieve stored options
	$gltr_base_lang = get_option('gltr_base_lang');
	$gltr_col_num = get_option('gltr_col_num');
	$gltr_html_bar_tag = get_option('gltr_html_bar_tag');
	$gltr_my_translation_engine = get_option('gltr_my_translation_engine');
	$gltr_preferred_languages = get_option('gltr_preferred_languages');
	$gltr_ban_prevention = get_option('gltr_ban_prevention');
	$gltr_sitemap_integration = get_option('gltr_sitemap_integration');
	
	$gltr_enable_debug = get_option('gltr_enable_debug');
	$gltr_conn_interval = get_option('gltr_conn_interval');


	$gltr_current_engine = $gltr_available_engines[$gltr_my_translation_engine];
	$gltr_lang_matrix = $gltr_current_engine->get_languages_matrix();
	if (count($gltr_preferred_languages) == 0) {
		$i = 0;
		foreach($gltr_lang_matrix[$gltr_base_lang] as $lang_key => $lang_value){
			if ($lang_key == $gltr_base_lang) continue;
			$gltr_preferred_languages[]=$lang_key;
			$i++;
		}
		update_option('gltr_preferred_languages', $gltr_preferred_languages);
	}

  $cachedir = dirname(__file__) . '/cache';
  
  $message = "";
  
  if (!is_readable(dirname(__file__)) || !is_writable(dirname(__file__)) ){
    $message = "Unable to complete Global Translator initialization. Plese make writable and readable the following directory:
    <ul><li>".dirname(__file__)."</li></ul>";
  } else {
	  if (!is_dir($cachedir)){
	  	if(!mkdir($cachedir, 0777)){
	      $message = "Unable to complete Global Translator initialization. Plese manually create and chmod 777 the following directory:
	      <ul><li>".$cachedir."</li></ul>";
	  	} 
		} else if (!is_readable($cachedir) || !is_writable($cachedir) ){
	    $message = "Unable to complete Global Translator initialization. Plese chmod 777 the following directory:
	    <ul><li>".$cachedir."</li></ul>";
	  } 
	  
	  if (is_dir($cachedir) && is_readable($cachedir) && is_writable($cachedir)){
		  $staledir = dirname(__file__) . '/cache/stale';
	  	if (!is_dir($staledir)){
		  	if(!mkdir($staledir, 0777)){
		      $message = "Unable to complete Global Translator initialization. Plese manually create and chmod 777 the following directory:
		      <ul><li>".$staledir."</li></ul>";
		  	} 
			} else if (!is_readable($staledir) || !is_writable($staledir) ){
		    $message = "Unable to complete Global Translator initialization. Plese chmod 777 the following directory:
		    <ul><li>".$staledir."</li></ul>";
		  } 
	  }
	  
	  //check files
	  /*
	  $datafiles = array();
	  foreach($datafiles as $datafile){
	  	if(!is_file($datafile)){
		    if (!gltr_create_file($datafile)){
					$message .= "Unable to complete Global Translator initialization. Plese create and make readable and writeable the following file:
			    <ul><li>".$datafile."</li></ul><br />";  		
		    }
	  	} else if (!is_readable($datafile) || !is_writeable($datafile)){
				$message .= "Unable to complete Global Translator initialization. Plese make readable and writeable the following file:
		    <ul><li>".$datafile."</li></ul><br />";  		
	  	}
	  }  
	  */
	}  
}

//foreach($gltr_preferred_languages as $key => $value){echo "$value<br>";}

 
/*Get options for form fields*/
$gltr_current_engine = $gltr_available_engines[$gltr_my_translation_engine];
$gltr_lang_matrix = $gltr_current_engine->get_languages_matrix();


function gltr_build_js_function($base_lang, $selected_item) {
	global $gltr_current_engine;
	global $gltr_lang_matrix;
?>  
<script type="text/javascript">
calculateOptions('<?php echo $base_lang ?>', <?php echo $selected_item ?>);

function languageItem(lang, flags_num){
  this.lang=lang;
  this.flags_num=flags_num;
}

function calculateOptions(lang, selectedItem) {
  var flags_num = 0;
  var list = new Array();
<?php  
  $j=0;
  foreach($gltr_lang_matrix as $key => $value){
    echo "  list[$j] = new languageItem('$key', " . count($gltr_lang_matrix[$key]) . ");\n";
    $j++;
  }
?>  
  for (z = 0; z < document.forms['form1'].gltr_col_num.options.length; z++) {
    document.forms['form1'].gltr_col_num.options[z] = null;
  }
  document.forms['form1'].gltr_col_num.options.length = 0;
  
  for (y = 0; y < list.length; y++) {
    if (list[y].lang == lang){
      flags_num = list[y].flags_num;
      break;
    }
  }
  for (i = 0; i <= flags_num; i++) {
    if (i == 0) {
      opt_text='all the flags in a single row (default)';
    } else if (i == 1) {
      opt_text='1 flag for each row';
    } else {
      opt_text= i + ' flags for each row';
    }
    document.forms['form1'].gltr_col_num.options[i]=new Option(opt_text, i);
  }
  
  //I need to cycle again on the options list in order to correctly choose the selected item
  for (i = 0; i <= flags_num; i++) {
    document.forms['form1'].gltr_col_num.options[i].selected = (selectedItem == i);
  }
}

function calculateAvailableTranslations(lang, selectedItem) {
  var list = new Array();
<?php  
  $j=0;
  foreach($gltr_lang_matrix as $key => $value){
    echo "  list[$j] = new languageItem('$key', " . count($gltr_lang_matrix[$key]) . ");\n";
    $j++;
  }
?>  
  for (z = 0; z < document.forms['form1'].gltr_col_num.options.length; z++) {
    document.forms['form1'].gltr_col_num.options[z] = null;
  }
  document.forms['form1'].gltr_col_num.options.length = 0;
  
  for (y = 0; y < list.length; y++) {
    if (list[y].lang == lang){
      flags_num = list[y].flags_num;
      break;
    }
  }
  for (i = 0; i <= flags_num; i++) {
    if (i == 0) {
      opt_text='all the flags in a single row (default)';
    } else if (i == 1) {
      opt_text='1 flag for each row';
    } else {
      opt_text= i + ' flags for each row';
    }
    document.forms['form1'].gltr_col_num.options[i]=new Option(opt_text, i);
  }
  
  //I need to cycle again on the options list in order to correctly choose the selected item
  for (i = 0; i <= flags_num; i++) {
    document.forms['form1'].gltr_col_num.options[i].selected = (selectedItem == i);
  }
}
</script>
<?php
}
/*
if (gltr_is_currently_banned()){
	$message="<font color='red'>WARNING! Your blog seems to have been temporarily banned by the '".strtoupper(get_option('gltr_my_translation_engine'))."' translation engine. Try to increase the connection request interval on the \"Translation engine connection\" section.</font>";
}
*/

//Print out the message to the user, if any
if($message!="") { ?>
	
	<div class="updated"><strong><p>
<?php	echo $message; ?>
	</p></strong></div>

<?php } else { ?>
	
<?php	} ?>
			
<form name="test"></form>
<div class="wrap">
  <h2><?php _e('Global Translator ')?><?php echo($gltr_VERSION);?></h2>
  <form id="gltr_form" name="form1" method="post" action="<?php echo $location ?>">
  	<input type="hidden" name="stage" value="process" />

  	<fieldset class="options">
  		<h3><?php _e('Choose your translation engine') ?></h3>
  		<table width="100%" cellpadding="5" class="editform">
      <tr><td>
        <label><input type="radio" onclick="document.forms['form1'].stage.value='change';document.forms['form1'].submit();" 
          <?php if($gltr_my_translation_engine==null || $gltr_my_translation_engine == 'google') {?> checked <?php } ?> name="gltr_my_translation_engine" 
            value="google">&nbsp;<?php _e('Google Translation Services') ?>
        </label>
      </td></tr>
      <tr><td>
        <label><input type="radio" onclick="document.forms['form1'].stage.value='change';document.forms['form1'].submit();" 
          <?php if($gltr_my_translation_engine == 'promt') {?> checked <?php } ?> name="gltr_my_translation_engine" 
            value="promt">&nbsp;<?php _e('Promt Online Translator') ?>
        </label>
      </td></tr>
      <tr><td>
        <label><input type="radio" onclick="document.forms['form1'].stage.value='change';document.forms['form1'].submit();" 
          <?php if($gltr_my_translation_engine == 'babelfish') {?> checked <?php } ?> name="gltr_my_translation_engine" 
            value="babelfish">&nbsp;<?php _e('Altavista Babel Fish(at now it is just a redirect)') ?>
        </label>
      </td></tr>
      <tr><td>
        <label><input type="radio" onclick="document.forms['form1'].stage.value='change';document.forms['form1'].submit();" 
          <?php if($gltr_my_translation_engine == 'freetransl') {?> checked <?php } ?> name="gltr_my_translation_engine" 
            value="freetransl">&nbsp;<?php _e('FreeTranslator') ?>
        </label>
      </td></tr>
      </table>
    </fieldset>
    
    <fieldset class="options">
  		<h3><?php _e('Base settings') ?></h3>
    		<table width="100%" cellpadding="5" class="editform"><tr><td>
          <label><?php _e('My Blog is written in:') ?>
            <select name="gltr_base_lang" onchange="document.forms['form1'].stage.value='change';document.forms['form1'].submit();">
              <?php    
              $languages = $gltr_current_engine->get_available_languages();
              foreach($languages as $key => $value){
                if ($gltr_base_lang == $key) {
              ?>
              <option value="<?php echo $key ?>" selected ><?php echo $value ?></option>
              <?php
                } else {
              ?>
              <option value="<?php echo $key ?>"  ><?php echo $value ?></option>
              <?php
                }
              }
              ?>
            </select>
          </label>
        </td></tr>
        <tr><td><label><?php _e('Choose which translations you want to make available for your visitors:') ?><br/>
        	<table border="0">
        <?php    
        foreach($gltr_lang_matrix as $key => $langs){
          if ($gltr_base_lang == $key) {
          	$i = 0;
          	foreach($langs as $lang_key => $lang_value){
          		if ($gltr_base_lang == $lang_key) continue;
          		$chk_val = "";
          		if (count ($gltr_preferred_languages) == 0 || in_array($lang_key, $gltr_preferred_languages) ) 
          			$chk_val = "checked";
          		echo '<tr><td><input type="checkbox" name="gltr_preferred_languages[' . $i . ']" ' . $chk_val . ' value="' . $lang_key . '"></td>
          		<td><img src="' . gltr_get_flag_image($lang_key) . '"/></td><td>' . $lang_value . '</td></tr>';
          		$i++;
          	}
          }
        }
        ?>
        </table>
        </td></tr></table>
     </fieldset>

  	<fieldset class="options">
  		<h3><?php _e('Flags bar layout') ?></h3>
  		<table width="100%" cellpadding="5" class="editform"><tr><td>
        <label><input type="radio" <?php if($gltr_html_bar_tag == 'TABLE') {?> checked <?php } ?> name="gltr_html_bar_tag" value="TABLE">&nbsp;<?php _e('Enclose the flags inside a TABLE and show') ?>
          <select name="gltr_col_num"/>
        </label>
      </td></tr>
      <tr><td>
        <label><input type="radio" <?php if($gltr_html_bar_tag == 'DIV') {?> checked <?php } ?> name="gltr_html_bar_tag" value="DIV">&nbsp;<?php _e('Enclose the flags inside a DIV (for CSS customization)') ?>
        </label>
      </td></tr>
      </table>
    </fieldset>

  	<fieldset class="options">
  		<h3><?php _e('Cache management') ?></h3>
  		<table width="100%" cellpadding="5" class="editform">
  		<tr><td>
        <label>
	        	Global Translator uses a fast, smart, optimized, self-cleaning and built-in caching system in order to drastically reduce the connections to the translation engines.
						This feature cannot be optional and is needed in order to prevent from banning by the translation services. For the same reason the translation process will not be 
						immediate and the full translation of the blog could take a while: this is because by default only a translation request every 4 minutes will be allowed (see next section). 
	        	<br /> 
	        	The cache invalidation will be automatically (and smartly) handled when a post is created, deleted or updated.
	        	<br/>
	        	<strong>Cache dir size</strong>: <?php $size=round(gltr_get_cache_size()/1024,1); echo ($size);?> KB<br/>
	        	<strong>Stale dir size</strong>: <?php $size=round(gltr_get_stale_size()/1024,1); echo ($size);?> KB<br/>
	        	
        </label>
      </td></tr>
  		<tr><td>
        <label>
        
        <input type="submit"  name="gltr_erase_cache" value="<?php _e('Erase cache') ?> &raquo;" />        
        </label>
      </td></tr>
      </table>
    </fieldset>

  	<fieldset class="options">
  		<h3><?php _e('Translation engine connection') ?></h3>
  		<table width="100%" cellpadding="5" class="editform">
  		<tr><td>
        <label><?php _e('Allow only a translation request every ') ?>
	        	<input size="4"  maxlength="5" name="gltr_conn_interval" type="text" id="gltr_conn_interval" value="<?php echo($gltr_conn_interval);?>"/> seconds.</label><br /><br />
	        	This feature represents the final solution which can definitively prevent your blog from being banned by the translation engines.<br />
	        	For this reason we strongly discourage you to insert an interval value lower than "300" (5 minutes), which should represent an optimal value.<br />
	        	If your blog is sharing its IP address with other blogs using this plugin, the risk of being banned could come back again: in this case I suggest you to  
	        	increase the timeout value and wait for a while (some days could be necessary).<br />
	        	<ul>
						<?php
	        	$diff_time = gltr_get_last_cached_file_time();

						if ($diff_time > 0){
		        	echo ("<li>Latest allowed connection to the translation engine: <strong>");
	    	      if ($diff_time < 60){
				      	echo (round(($diff_time)) . " seconds ago");
	      			}else if ($diff_time > 60*60){
	      				echo (round(($diff_time)/3600) . " hours ago");
								    
		      		}else{
		      			echo (round(($diff_time)/60) . " minutes ago");
		      		}
						} else {
							echo ("<li>Latest allowed connection to the translation engine: <strong>not available");
						}
						echo ("</strong></li>");
						
						echo ("<li>Translations status: <strong>");	
						$ban_status = get_option("gltr_translation_status");					
						if ($ban_status == 'banned'){
							echo("<font color='red'>Bad response from the '".strtoupper(get_option('gltr_my_translation_engine'))."' translation engine: your blog could have been temporarily banned</font>");
						} else if ($ban_status == 'working'){
							echo("<font color='green'>Working properly</font>");
						} else {
							echo("not available");
						}
						echo ("</strong></li>");
	        	?>
	        </ul>
	        	
	        	
        
      </td></tr>
      </table>
    </fieldset>

  	<fieldset class="options">
  		<h3><?php _e('Bad spiders blocking system') ?></h3>
  		<table width="100%" cellpadding="5" class="editform">
  		<tr><td>
        <label><?php _e('Block "bad" web spiders and crawlers') ?>
	        	<input name="gltr_ban_prevention" type="checkbox" id="gltr_ban_prevention"  
	        	<?php if($gltr_ban_prevention == TRUE) {?> checked="checked" <?php } ?> /></label>
	        	<br />	        	<br />
	        	By enabling this option, Global Translator will block the access to the translated pages to a lot of "bad" web spiders.
 	          This function could help the <strong>built-in cache</strong> to prevent "unuseful" translation requests.
        
      </td></tr>
      </table>   
    </fieldset>

  	<fieldset class="options">
  		<h3><?php _e('Sitemap integration (beta)') ?></h3>
  		<table width="100%" cellpadding="5" class="editform">
  		<tr><td>
  			<?php 
  			if (gltr_sitemap_plugin_detected()){?>
        <label>
						<?php _e('Enable sitemap integration') ?>
	        	<input name="gltr_sitemap_integration" type="checkbox" id="gltr_sitemap_integration"  
	        	<?php if($gltr_sitemap_integration == TRUE) {?> checked="checked" <?php } ?> /></label>
	        	<br /><br />
	        	By enabling this option, Global Translator will automatically provide the translated urls to the "<strong>Google XML Sitemaps Generator for WordPress</strong>" plugin.<br />
						After the next sitemap rebuild, all the translated urls will be added to your sitemap.xml file.<br />
						This feature could make the sitemap generation process slow (a lot of urls could be added): I strongly suggest you to 
						enable the <strong>"Build the sitemap in a background process"</strong> option from the "<strong>Google XML Sitemaps Generator for WordPress</strong>" 
						admin page, otherwise the post saving/publishing actions could become very slow.
        
      <?php
      } else {?>
        <label>"Google XML Sitemaps Generator for WordPress" not detected.<br />
        	Please download, install and activate the "<a target="_blank" href="http://www.arnebrachhold.de/projects/wordpress-plugins/google-xml-sitemaps-generator/">Google XML Sitemaps Generator for WordPress 3.*</a>" in order to enable this feature.
        </label>
      <?php
      }?>
      </td></tr>
      </table>
    </fieldset>
    
  	<fieldset class="options">
  		<h3><?php _e('Debug') ?></h3>
  		<table width="100%" cellpadding="5" class="editform">
  		<tr><td>
        <label><?php _e('Enable debug') ?>
	        	<input name="gltr_enable_debug" type="checkbox" id="gltr_enable_debug"  
	        	<?php if($gltr_enable_debug == TRUE) {?> checked="checked" <?php } ?> /><br />	        	<br />
	        	By enabling this option, Global Translator will trace all its activities on the <strong>"debug.log"</strong> file, which will be saved in the following directory:<br/>
	        	<strong><?php echo(dirname(__file__));?></strong>.<br />
        </label>
      </td></tr>
      </table>
    </fieldset>

    <p class="submit">
      <input type="submit" name="gltr_save" value="<?php _e('Update options') ?> &raquo;" />
    </p>
		<br /><br />
		<fieldset class="options">
			<h3><?php _e('Thanks for using this plugin!') ?></h3>
				<strong><p><?php echo __('If you are satisfied with the results, isn\'t it worth at least one dollar? 
					<a href="http://www.nothing2hide.net/donate_global_translator.php">Donations</a> help me to continue support and development of this <i>free</i> software! '); ?> 
					</p></strong>
		</fieldset>

		<fieldset class="options">
			<h3><?php _e('Informations and support') ?></h3>
			<p><?php echo str_replace("%s","<a href=\"http://www.nothing2hide.net/wp-plugins/wordpress-global-translator-plugin/\">http://www.nothing2hide.net/wp-plugins/wordpress-global-translator-plugin/</a>",
				__("Check %s for updates and comment there if you have any problems / questions / suggestions.")); ?></p>
		</fieldset>
  </form>
</div>

<?php
gltr_build_js_function($gltr_base_lang, $gltr_col_num);
?>