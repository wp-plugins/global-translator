<?php
require_once (dirname(__file__).'/header.php');

load_plugin_textdomain('gltr'); // NLS

/*Lets add some default options if they don't exist*/
add_option('gltr_base_lang', 'en');
add_option('gltr_col_num', '0');
add_option('gltr_use_cache', false);
add_option('gltr_cache_timeout', '3600');
add_option('gltr_html_bar_tag', 'TABLE');
add_option('gltr_my_translation_engine', 'google');
add_option('gltr_preferred_languages', array());

	
	
$location = get_option('siteurl') . '/wp-admin/admin.php?page=global-translator/options-translator.php'; // Form Action URI



/*check form submission and update options*/

if (isset($_POST['stage'])){
	//submitting something
	$gltr_base_lang 						= $_POST['gltr_base_lang'];
	$gltr_col_num 							= $_POST['gltr_col_num'];
	$gltr_cache_timeout 				= $_POST['gltr_cache_timeout'];
	$gltr_html_bar_tag 					= $_POST['gltr_html_bar_tag'];
	$gltr_my_translation_engine = $_POST['gltr_my_translation_engine'];
	if(isset($_POST['gltr_use_cache'])) 
		$gltr_use_cache = true; 
	else 
		$gltr_use_cache = false;
	if (isset($_POST['gltr_preferred_languages']))
		$gltr_preferred_languages = $_POST['gltr_preferred_languages'];
	
	
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
	        if($item != '.' && $item != '..') {
	          $path = $cachedir.'/'.$item;
	          unlink($path);
	        }
	      }
	      $message = "Cache successfully erased";
	    } else {
	      $message = "Unable to erase cache or cache dir not existing";
	    }
	  } else {
	  	//update options button pressed
	  	$iserror = false;
	    $timeout = $_POST['gltr_cache_timeout'];
	    
	    if (!preg_match('/^[0-9]*$/i', $timeout) && isset($_POST['gltr_use_cache'])){
	      $message = "Error: invalid cache timeout value. Please insert a numeric value or leave it blank.";
	      $iserror = true;
	    } 
	    
	    if (count ($gltr_preferred_languages) == 0) {
	      $message .= "Error: you must choose almost one of the available translations.";
	      $iserror = true;
	    }
	    
	    if(!$iserror) {
	      if ($timeout == "") $timeout = "3600";
	      update_option('gltr_base_lang', $_POST['gltr_base_lang']);
	      update_option('gltr_col_num', $_POST['gltr_col_num']);
	      update_option('gltr_cache_timeout', $timeout);
	      update_option('gltr_html_bar_tag', $_POST['gltr_html_bar_tag']);
	      update_option('gltr_my_translation_engine', $_POST['gltr_my_translation_engine']);
	      update_option('gltr_preferred_languages', array());
	      update_option('gltr_preferred_languages', $_POST['gltr_preferred_languages']);
	
	      if(isset($_POST['gltr_use_cache']))
	        update_option('gltr_use_cache', true);
	      else
	        update_option('gltr_use_cache', false);
	
				$wp_rewrite->flush_rules();
	      $message = "Options saved.";
	    }
	  }
	}		
} else {
	//page loaded by menu: retrieve stored options
	$gltr_base_lang = get_option('gltr_base_lang');
	$gltr_col_num = get_option('gltr_col_num');
	$gltr_use_cache = get_option('gltr_use_cache');
	$gltr_cache_timeout = get_option('gltr_cache_timeout');
	$gltr_html_bar_tag = get_option('gltr_html_bar_tag');
	$gltr_my_translation_engine = get_option('gltr_my_translation_engine');
	$gltr_preferred_languages = get_option('gltr_preferred_languages');

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

//Print out the message to the user, if any
if($message!="") { ?>
	
	<div class="updated"><strong><p>
<?php	echo $message; ?>
	</p></strong></div>

<?php } ?>
			
<form name="test"></form>
<div class="wrap">
  <h2><?php _e('Global Translator ')?>0.7.1</h2>
  <form id="gltr_form" name="form1" method="post" action="<?php echo $location ?>">
  	<input type="hidden" name="stage" value="process" />


  	<fieldset class="options">
  		<legend><?php _e('Choose your translation engine') ?></legend>
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
            value="babelfish">&nbsp;<?php _e('Altavista Babel Fish') ?>
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
  		<legend><?php _e('Base settings') ?></legend>
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
  		<legend><?php _e('Flags bar layout') ?></legend>
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
  		<legend><?php _e('Caching support') ?></legend>
  		<table width="100%" cellpadding="5" class="editform">
  		<tr><td>
        <label><?php _e('Enable caching support') ?>
	        	<input name="gltr_use_cache" type="checkbox" id="gltr_use_cache"  
	        	
	        	<?php if($gltr_use_cache == TRUE) {?> checked="checked" <?php } ?> /><br />
	        	By enabling this option, Global Translator will try to create a directory named named "<b>cache</b>" inside its installation path (<b><?echo dirname(__FILE__)?></b>).<br /> 
	        	If your web server gives you permission problems, try to manually create a "<b>cache</b>" directory and make it writable by the web server (chmod 777).
        </label>
      </td></tr>
  		<tr><td>
        <label><?php _e('Refresh cache every ') ?>
	        	<input name="gltr_cache_timeout" id="gltr_cache_timeout" size="5" maxlength="8"
	        	value="<?php echo $gltr_cache_timeout ?>" /> seconds. <br/>If you leave it empty, the default value <strong>"3600"</strong> (1 hour) will be used.
        </label>
      </td></tr>
  		<tr><td>
        <label>
        <input type="submit" name="gltr_erase_cache" value="<?php _e('Erase cache') ?> &raquo;" />        
        </label>
      </td></tr>
      </table>
    </fieldset>

    <p class="submit">
      <input type="submit" name="gltr_save" value="<?php _e('Update options') ?> &raquo;" />
    </p>
		<fieldset class="options">
			<legend><?php _e('Informations and support') ?></legend>
			<p><?php echo str_replace("%s","<a href=\"http://www.nothing2hide.net/blog/wp-plugins/wordpress-global-translator-plugin/\">http://www.nothing2hide.net/blog/wp-plugins/wordpress-global-translator-plugin/</a>",__("Check %s for updates and comment there if you have any problems / questions / suggestions.")); ?></p>
		</fieldset>
  </form>
</div>

<?php
gltr_build_js_function($gltr_base_lang, $gltr_col_num);
?>