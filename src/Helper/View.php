<?php

namespace Webbmaffian\MVC\Helper;

class View {
	private $_view = '';
	private $_data = array(
		'title' => '',
		'header' => '',
		'footer' => '',
		'breadcrumb' => array(),
		'html_class' => array(),
		'body_class' => array('fix-header', 'fix-sidebar', 'card-no-border')
	);


	public function __construct($view, $shared = false) {
		$this->_view = self::path($view, $shared);

		if(Auth::get_lang()) {
			$this->_data['body_class'][] = 'lang-' . Auth::get_lang();
		}
		
		$this->_data['body_class'][] = Helper::constant('ENDPOINT');
	}


	public function set($key, $value) {
		$this->_data[$key] = $value;

		return $this;
	}
	
	
	public function add($key, $value) {
		if(!isset($this->_data[$key])) {
			$this->_data[$key] = array();
		}
		elseif(!is_array($this->_data[$key])) {
			throw new Problem('Tried to add element to non-variable.');
		}
		
		$this->_data[$key][] = $value;
		
		return $this;
	}


	public function clear($key) {
		unset($this->_data[$key]);

		return $this;
	}


	public function get($key) {
		if(!isset($this->_data[$key])) return false;

		return $this->_data[$key];
	}


	public function get_html($wrap = true) {
		ob_start();
		$this->output($wrap);
		return ob_get_clean();
	}


	public function output($wrap = true) {
		Lang::setup();
		array_unshift($this->_data['breadcrumb'], array(
			'url' => '/',
			'label' => Lang::get_string('Home')
		));
		
		extract($this->_data);

		if($wrap) include(self::path('header', true));
		include($this->_view);
		if($wrap) include(self::path('footer', true));
	}


	public function template($view, $shared = false) {
		$path = self::path($view, $shared);

		extract($this->_data);

		include($path);
	}


	static public function path($filename = '', $shared = false) {
		if(!defined('ENDPOINT')) {
			throw new Problem('ENDPOINT is undefined.');
		}

		if($shared) {
			$base = Helper::root_dir() . '/app/views/shared';	
		} else {
			$base = Helper::root_dir() . '/app/views/' . Helper::constant('ENDPOINT');
		}

		if(empty($filename)) return $base;

		if(strpos($filename, '.php') === false) {
			$filename .= '.php';
		}

		if($filename[0] !== '/') {
			$filename = '/' . $filename;
		}

		return $base . $filename;
	}


	static public function error($code, $message = '') {
		$path = self::path($code, true);
		
		if(!file_exists($path)) {
			$path = self::path('404', true);
		}
		
		http_response_code($code);
		include($path);
		exit;
	}
}