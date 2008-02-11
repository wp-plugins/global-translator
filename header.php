<?php
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
	"/href=\"[^\"]*u=(.*?)&amp;prev=\/language_tools[^\"]*\"/",
	"href=\"\\1\" ",
	array(
  'it'    => array( 'it'=>'Italiano',
                    'en'=>'Inglese'),
  'ko'    => array( 'ko'=>'Korean',
                    'en'=>'English'),
  'zh-CN' => array( 'zh-CN'=>'Chinese',
                    'en'=>'English'),
  'pt'    => array( 'pt'=>'Portugues',
                    'en'=>'Ingles'),
  'en'    => array( 'en'=>'English',
                    'it'=>'Italian',
                    'de'=>'German',
                    'es'=>'Spanish',
                    'fr'=>'French',
                    'pt'=>'Portuguese',
                    'ja'=>'Japanese',
                    'ko'=>'Korean',
                    'zh-CN'=>'Chinese',
                    'ar'=>'Arabic',
                    'ru'=>'Russian',
                    'el'=>'Greek',
                    'nl'=>'Dutch'),
  'de'    => array( 'de'=>'Deutsch',
                    'en'=>'Englisch',
                    'fr'=>'Franzosisch'),
  'fr'    => array( 'fr'=>'Francais',
                    'en'=>'Anglais',
                    'de'=>'Allemand'),
  'es'    => array( 'es'=>'Espanol',
                    'en'=>'Ingles'),
  'ja'    => array( 'ja'=>'Japanese',
                    'en'=>'English'),
  'ar'    => array( 'ar'=>'Arabic',
                    'en'=>'English'),
  'ru'    => array( 'ru'=>'Russian',
                    'en'=>'English'),
  'el'    => array( 'el'=>'Greek',
                    'en'=>'English'),
  'nl'    => array( 'nl'=>'Dutch',
                    'en'=>'English'),
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
	  'nl'		=> 'Dutch'
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
  'http://www.online-translator.com/url/tran_url.asp?url=${URL}&direction=${SRCLANG}${DESTLANG}&cp1=UTF-8&cp2=UTF-8&autotranslate=on',
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
                    'pt'=>'Russian', //
                    'es'=>'Espagnol'),
  'es'    => array( 'es'=>'Espanol',
                    'en'=>'Russian',
                    'ru'=>'Ingles',
                    'de'=>'French',
                    'es'=>'German'
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
                    'ru'=>'Ingles',
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

$gltr_available_engines = array();
$gltr_available_engines['google'] = $googleEngine;
$gltr_available_engines['promt'] = $promtEngine;
$gltr_available_engines['babelfish'] = $babelfishEngine;
$gltr_available_engines['freetransl'] = $freetranslationEngine;
//At now it is now working...

?>