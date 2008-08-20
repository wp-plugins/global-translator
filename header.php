<?php
function gltr_sitemap_plugin_detected(){
	if (class_exists('GoogleSitemapGenerator')){
		$generatorObject = &GoogleSitemapGenerator::GetInstance();
		return($generatorObject!=null);
	} else {
		return false;
	}
}

function gltr_create_file($datafile){
	$success = true;
	if (!file_exists($datafile)){
      if (($handle = @fopen($datafile, "wb")) === false) return false;
	    if ((@fwrite($handle, '')) === false) return false;
      @fclose($handle);
	} 
	return true;
}

if (!function_exists('file_get_contents')) {
	function file_get_contents($filename, $incpath = false, $resource_context = null) {
		if (false === $handle = fopen($filename, 'rb', $incpath)) {
			return false;
		}
		if ($fsize = @filesize($filename)) {
			$buf = fread($handle, $fsize);
		} else {
			$buf = '';
			while (!feof($handle)) {
				$buf .= fread($handle, 8192);
			}
		}
		fclose($handle);
		return $buf;
	}	
}

if(!class_exists("gltr_translation_engine")) {
	class gltr_translation_engine {
		var $_name;

		var	$_base_url;

		var $_links_pattern;

		var $_links_replacement;

		var $_languages_matrix;

		var $_available_languages;

		function gltr_translation_engine(
			$name,
			$base_url,
			$links_pattern,
			$links_replacement,
			$languages_matrix,
			$available_languages) {
	      $this->set_name($name);
        $this->set_base_url($base_url);
        $this->set_links_pattern($links_pattern);
        $this->set_links_replacement($links_replacement);
        $this->set_languages_matrix($languages_matrix);
        $this->set_available_languages($available_languages);
		}

    function set_name($name){
    	$this->_name = (string)$name;
    }

		function set_base_url($base_url){
    	$this->_base_url = (string)$base_url;
    }

		function set_links_pattern($links_pattern){
    	$this->_links_pattern = (string)$links_pattern;
    }

		function set_links_replacement($links_replacement){
    	$this->_links_replacement = (string)$links_replacement;
    }

		function set_languages_matrix($languages_matrix){
    	$this->_languages_matrix = (array)$languages_matrix;
    }

		function set_available_languages($available_languages){
    	$this->_available_languages = (array)$available_languages;
    }

    function get_name(){
    	return $this->_name;
    }

		function get_base_url(){
    	return $this->_base_url;
    }

		function get_links_pattern(){
    	return $this->_links_pattern;
    }

		function get_links_replacement(){
    	return $this->_links_replacement;
    }

		function get_languages_matrix(){
    	return $this->_languages_matrix;
    }

		function get_available_languages(){
    	return $this->_available_languages;
    }

    function decode_lang_code($res)
    {
      if ($this->_name == 'promt') {
        if ($res == 'es') $res = 's';
        else if ($res == 'de') $res = 'g';
        else $res = substr($res, 0, 1);
      } else if ($this->_name == 'freetransl') {
        $map = array(
      	  'en'    => 'English',
      	  'es'    => 'Spanish',
      	  'fr'    => 'French',
      	  'de'    => 'German',
      	  'it'    => 'Italian',
      	  'nl'    => 'Dutch',
      	  'pt'    => 'Portuguese',
      	  'no'    => 'Norwegian');
     	  $res = $map[$res];
      }
      return $res;
    }

	}
}

$googleEngine = new gltr_translation_engine(
	'google',
	'http://translate.google.com/translate_c?hl=en&prev=/language_tools&ie=UTF-8&oe=UTF-8&u=${URL}&langpair=${SRCLANG}|${DESTLANG}',
	"/href=['\"]{1}[^\"']*u=(.*?)&amp;prev=\/language_tools[^\"']*['\"]{1}/",
	"href=\"\\1\" ",
	array(
  'it'    => array( 'it'=>'Italiano',
                    'en'=>'Inglese',
                    'de'=>'Tedesco',
                    'es'=>'Spagnolo',
                    'fr'=>'Francese',
                    'pt'=>'Portoghese',
                    'ja'=>'Giapponese',
                    'ko'=>'Coreano',
                    'zh-CN'=>'Cinese',
                    'ar'=>'Arabo',
                    'ru'=>'Russo',
                    'el'=>'Greco',
                    'nl'=>'Olandese',
                    'bg'=>'Bulgaro',
                    'cs'=>'Ceco',
                    'hr'=>'Croato',
                    'da'=>'Danese',
                    'fi'=>'Finlandese',
                    'hi'=>'Hindi',
                    'pl'=>'Polacco',
                    'ro'=>'Rumeno',
                    'sv'=>'Svedese',
                    'el'=>'Greco',
    								'no'		=>'Norvegese'
                    ),
  'ko'    => array( 'ko'    => 'Korean',
									  'it'    => 'Italian',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'zh-CN' => array( 'zh-CN' => 'Chinese (Simplified)',
									  'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'pt'    => array( 'pt'    => 'Portuguese',
									  'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'en'    => array( 'en'    => 'English',
									  'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'de'    => array( 'de'    => 'German',
									  'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
									),
  'fr'    => array( 'fr'    => 'French',
									  'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'es'    => array( 'es'    => 'Spanish',
									  'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'ja'    => array( 'ja'    => 'Japanese',
									  'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'ar'    => array( 'ar'    => 'Arabic',
									  'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'ru'    => array( 'ru'		=> 'Russian',
									  'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'el'    => array( 'el'		=>'Greek',
    								'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'no'		=>'Norwegian'
                    ),
  'nl'    => array( 'nl'		=> 'Dutch',
								    'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'bg'    => array( 'bg'		=>'Bulgarian',
								    'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'cs'    => array( 'cs'		=>'Czech',
								    'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'hr'    => array( 'hr'		=>'Croat',
								    'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'da'    => array( 'da'		=>'Danish',
								    'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'fi'    => array( 'fi'		=>'Finnish',
								    'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'hi'    => array( 'hi'		=>'Hindi',
								    'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'pl'    => array( 'pl'		=>'Polish',
								    'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'ro'		=>'Rumanian',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'ro'    => array( 'ro'		=>'Rumanian',
								    'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'sv'		=>'Swedish',
								    'el'		=>'Greek',
    								'no'		=>'Norwegian'
                    ),
  'sv'    => array( 'sv'		=>'Swedish',
    								'it'    => 'Italian',
									  'ko'    => 'Korean',
									  'zh-CN' => 'Chinese (Simplified)',
									  'pt'    => 'Portuguese',
									  'en'    => 'English',
									  'de'    => 'German',
									  'fr'    => 'French',
									  'es'    => 'Spanish',
									  'ja'    => 'Japanese',
									  'ar'    => 'Arabic',
									  'ru'		=> 'Russian',
									  'el'    => 'Greek',
									  'nl'		=> 'Dutch',
								    'bg'		=>'Bulgarian',
								    'cs'		=>'Czech',
								    'hr'		=>'Croat',
								    'da'		=>'Danish',
								    'fi'		=>'Finnish',
								    'hi'		=>'Hindi',
								    'pl'		=>'Polish',
								    'ro'		=>'Rumanian',
								    'no'		=>'Norwegian'
                    ),
  ),

	array(
	  'it'    => 'Italian',
	  'ko'    => 'Korean',
	  'zh-CN' => 'Chinese (Simplified)',
	  'pt'    => 'Portuguese',
	  'en'    => 'English',
	  'de'    => 'German',
	  'fr'    => 'French',
	  'es'    => 'Spanish',
	  'ja'    => 'Japanese',
	  'ar'    => 'Arabic',
	  'ru'		=> 'Russian',
	  'el'    => 'Greek',
	  'nl'		=> 'Dutch',
    'bg'		=> 'Bulgarian',
    'cs'		=> 'Czech',
    'hr'		=> 'Croat',
    'da'		=> 'Danish',
    'fi'		=> 'Finnish',
    'hi'		=> 'Hindi',
    'pl'		=> 'Polish',
    'ro'		=> 'Rumanian',
    'sv'		=> 'Swedish',
    'no'		=> 'Norwegian'
	  )

	);


$babelfishEngine = new gltr_translation_engine(
	'babelfish',
	'http://babelfish.altavista.com/babelfish/trurl_pagecontent?lp=${SRCLANG}_${DESTLANG}&url=${URL}',
	"/<a(.*?)href=\"(.*?)url=(.*?)\"([\s|>]{1})/i",
	"<a href=\"\\3\" \\4",
	array(
  'it'    => array( 'it'=>'Italiano',
                    'en'=>'Inglese',
                    'fr'=>'Francese'),
  'ko'    => array( 'ko'=>'Korean',
                    'en'=>'English'),
  'zh' 		=> array( 'zh'=>'Chinese (Simplified)',
                    'en'=>'English'),
  'zt' 		=> array( 'zt'=>'Chinese (Traditional)',
                    'en'=>'English'),
  'pt'    => array( 'pt'=>'Portugues',
                    'en'=>'Ingles',
                    'fr'=>'Francais'),//to be verified
  'en'    => array( 'en'=>'English',
                    'zh'=>'Chinese (Simplified)',
                    'zt'=>'Chinese (Traditional)',
                    'nl'=>'Dutch',
                    'fr'=>'French',
                    'de'=>'German',
                    'el'=>'Greek',
                    'it'=>'Italian',
                    'ja'=>'Japanese',
                    'ko'=>'Korean',
                    'pt'=>'Portuguese',
                    'ru'=>'Russian',
                    'es'=>'Spanish'),
  'nl'    => array( 'nl'=>'Dutch',
  									'en'=>'English',
  									'fr'=>'French'),
  'de'    => array( 'de'=>'Deutsch',
                    'en'=>'Englisch',
                    'fr'=>'Franzosisch'),
  'fr'    => array( 'fr'=>'Francais',
                    'en'=>'Anglais',
                    'de'=>'Allemand',
                    'el'=>'Grec',
                    'it'=>'Italien',
                    'pt'=>'Portugais',
                    'es'=>'Espagnol',
                    'nl'=>'Hollandais'),
  'el'    => array( 'el'=>'Greek',
  									'en'=>'English',
  									'fr'=>'French'),
  'es'    => array( 'es'=>'Espanol',
                    'en'=>'Ingles'),
  'ja'    => array( 'ja'=>'Japanese',
                    'en'=>'English'),
  'ru'    => array( 'ru'=>'Russian',
                    'en'=>'English')
  ),

	array(
	  'it'    => 'Italian',
	  'ko'    => 'Korean',
	  'zh' 		=> 'Chinese (Simplified)',
	  'zt' 		=> 'Chinese (Traditional)',
	  'pt'    => 'Portuguese',
	  'en'    => 'English',
	  'el'    => 'Greek',
	  'nl'    => 'Dutch',
	  'de'    => 'German',
	  'fr'    => 'French',
	  'es'    => 'Spanish',
	  'ja'    => 'Japanese',
	  'ru'		=> 'Russian'
	  )
	);

$promtEngine = new gltr_translation_engine(
  'promt',
  'http://beta.translate.ru/url/translation.aspx?autotranslate=on&sourceURL=${URL}&direction=${SRCLANG}${DESTLANG}',
  //old version 'http://beta.online-translator.com/url/tran_url.asp?prmtlang=en&autotranslate=on&url=${URL}&direction=${SRCLANG}${DESTLANG}',
  //old version 'http://www.online-translator.com/url/tran_url.asp?url=${URL}&direction=${SRCLANG}${DESTLANG}&cp1=UTF-8&cp2=UTF-8&autotranslate=on',
  "/href=\"(.*?)url=(.*?)\"([\s|>]{1})/i",
  "href=\"\\2\" \\3",
  array(
  'it'    => array( 'it'=>'Italiano',
                    'en'=>'Inglese',
                    'ru'=>'Russo'),
  'pt'    => array( 'pt'=>'Portugues',
                    'en'=>'Ingles'),//to be verified
  'en'    => array( 'en'=>'English',
                    'fr'=>'French',
                    'de'=>'German',
                    //'it'=>'Italian',
                    'pt'=>'Portuguese',
                    'ru'=>'Russian',
                    'es'=>'Spanish'),
  'de'    => array( 'de'=>'Deutsch',
                    'en'=>'Englisch',
                    'fr'=>'Franzosisch',
                    'es'=>'Spanish',
                    'ru'=>'Russian'
                    ),
  'fr'    => array( 'fr'=>'Francais',
                    'en'=>'Anglais',
                    'de'=>'Allemand',
                    'ru'=>'Russian', //
                    'es'=>'Espagnol'),
  'es'    => array( 'es'=>'Espanol',
                    'en'=>'Ingles',
                    'ru'=>'Russian',
                    'de'=>'German',
                    'fr'=>'French'
                    ),
  'ru'    => array( 'ru'=>'Russian',
                    'en'=>'English',
                    'fr'=>'French',
                    'de'=>'German',
                    'es'=>'Spanish'
                    )
  ),



  array(
    'it'    => 'Italian',
    'pt'    => 'Portuguese',
    'en'    => 'English',
    'de'    => 'German',
    'fr'    => 'French',
    'es'    => 'Spanish',
    'ru'    => 'Russian'
    )
  );

$freetranslationEngine = new gltr_translation_engine(
  'freetransl',
  'http://fets5.freetranslation.com/?sequence=core&language=${SRCLANG}/${DESTLANG}&url=${URL}',
  "/href=\"([^\"]*)\"/i",
  "href=\"\\1\"", 
  array(
  'it'    => array( 'it'=>'Italiano',
                    'en'=>'Inglese'),
  'pt'    => array( 'pt'=>'Portugues',
                    'en'=>'Ingles'),//to be verified
  'en'    => array( 'en'=>'English',
                    'es'=>'Spanish',
                    'fr'=>'French',
                    'de'=>'German',
                    'it'=>'Italian',
                    'nl'=>'Dutch',
                    'pt'=>'Portuguese',
                    'no'=> 'Norwegian'
                    ),
  'de'    => array( 'de'=>'Deutsch',
                    'en'=>'Englisch'
                    ),
  'fr'    => array( 'fr'=>'Francais',
                    'en'=>'Anglais'
                    ),
  'es'    => array( 'es'=>'Espanol',
                    'en'=>'Ingles',
                    ),
  'nl'    => array( 'nl'=>'Dutch',
                    'en'=>'English'
                    )
  ),

  array(
    'it'    => 'Italian',
    'pt'    => 'Portuguese',
    'en'    => 'English',
    'de'    => 'German',
    'fr'    => 'French',
    'es'    => 'Spanish',
    'no'    => 'Norwegian',
    'nl'    => 'Dutch',
    )
  );

$well_known_extensions =  array('swf','gif','jpg','jpeg','bmp','gz','zip','rar','tar','png','xls','doc','ppt','tiff','avi','mpeg','mp3','mov','mp4');

$gltr_available_engines = array();
$gltr_available_engines['google'] = $googleEngine;
$gltr_available_engines['promt'] = $promtEngine;
$gltr_available_engines['babelfish'] = $babelfishEngine;
$gltr_available_engines['freetransl'] = $freetranslationEngine;

$gltr_VERSION='1.0.4';
?>