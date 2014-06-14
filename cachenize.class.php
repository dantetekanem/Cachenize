<?php

	/**
	* Cachezine by Dante.leonardo
	* Alpha release 1
	* May 31 2014
	**/

	class Cachenize {

		private $version;
		private $path;
		private $time;
		private $_name;
		private $_contents;
		private $_lastCacheName;

		public function __construct() {
			$this->version 	= "1.alpha";
			$this->path 		= "tmp/";
			$this->time 		= 1000 * 60 * 60; // 1 hour
		}

		public function getPath() {
			return $this->path;
		}

		public function setPath($path) {
			$this->path 		= $path;
			return $this->time;
		}

		public function getTime() {
			return $this->time;
		}

		public function setTime($time) {
			$this->time 		= $time;
			return $this->time;
		}

		public function getVersion() {
			return $this->version;
		}

		public function getLastName() {
			return $this->_lastCacheName;
		}

		//

		public function createCache($name, $time=null) {
			if($time == null) {
				$time = time() + $this->getTime();
			} else {
				$time = time() + intval($time);
			}
			$this->_name = $name;
			if(!$this->cacheExists($name)) {
				$content 		= $time . "--" . $name . "--" . "\n";
				file_put_contents($this->fullPath($name), $content, FILE_APPEND | LOCK_EX);
				$this->_lastCacheName 	= $this->_name;
			} else {
				// exists
			}
		}

		public function assertContent() {
			file_put_contents($this->fullPath($this->_name), $this->_cacheContent, FILE_APPEND | LOCK_EX);
		}

		public function assertData($data) {
			file_put_contents($this->fullPath($this->_name), $this->encodeData($data), FILE_APPEND | LOCK_EX);
		}

		public function getData($name) {
			if($this->cacheExists($name)) {
				list($timestamp, $contents) = explode("--".$name."--", file_get_contents($this->fullPath($name)));
				if(time() > $timestamp) {
					$this->clearCache($name);
				}
				//
				return $this->decodeData($contents);
			} else {
				return false;
			}
		}

		public function startCaching() {
			ob_start();
		}

		public function stopCaching() {
			$this->_cacheContent 		= ob_get_contents();
			ob_end_clean();

			$this->assertContent();
			$this->clearContents();
		}

		public function clearContents() {
			$this->_name 					= '';
			$this->_cacheContent 	= '';
		}

		public function clearCache($name) {
			return @unlink($this->fullPath($name));
		}

		public function clearAllCache() {

		}

		// For assert data content
		private function encodeData($content) {
			return base64_encode(json_encode($content));
		}

		private function decodeData($content) {
			return json_decode(base64_decode($content));
		}
		// -----------------------

		public function cacheExists($name) {
			return is_file( $this->fullPath($name) );
		}

		private function fullPath($name) {
			return $this->getPath() . md5($name) . '.cache';
		}

		public function getCache($name) {
			if($this->cacheExists($name)) {
				list($timestamp, $contents) = explode("--".$name."--", file_get_contents($this->fullPath($name)));
				if(time() > $timestamp) {
					$this->clearCache($name);
				}
				//
				return $contents;
			} else {
				return false;
			}
		}

		public function getLastCache() {
			return $this->getCache($this->getLastName());
		}

	}

	$Cachenize 		= new Cachenize();

	/* Magic Methods */

	/*
		How to use:
		cachenize('block_name', function() {
			// as anonymous function
			$news 		= AlySQL::Connect('table')->Order('id DESC')->Limit(20)->FindAll();
			foreach($news as $item) {
				echo $item->title.'<br />';
			}
		}, 5000);

		This will always output the returned html content.
	*/
	function cachenize($name, $func, $time=null) {
		global $Cachenize;

		if($Cachenize->cacheExists($name)) {
			echo $Cachenize->getCache($name);
			return;
		}

		$Cachenize->createCache($name, $time);
		$Cachenize->startCaching();
		$func();
		$Cachenize->stopCaching();

		echo $Cachenize->getCache($name);
		return;
	}

	/*
		How to use:
		$news = cachenize_asserted_data('home.news', AlySQL::Connect('News')->FindAll());
		or 
		$news = cad('home.news', AlySQL::Connect('table')->FindAll());

		This will always return the cache data as an object (or array) instead of a string content.
	*/
	function cachenize_assert_data($name, $data, $time=null) {
		global $Cachenize;
		$name .= "-data";

		if($Cachenize->cacheExists($name)) {
			return $Cachenize->getData($name);
		}

		$Cachenize->createCache($name, $time);
		$Cachenize->assertData($data);

		return $Cachenize->getData($name);
	}

	/*
		Alias for cachenize_assert_data()
	*/
	function cad($name, $data, $time=null) { return cachenize_assert_data($name, $data, $time); }


	/*
		Used for views content, you can open a cache tag and close it.
		open -> cachenize_start('block.name');
		your html code and content
		close -> cachenize_end();
	*/
	function cachenize_start($name, $time=null) {
		global $Cachenize;

		if($Cachenize->cacheExists($name)) {
			echo $Cachenize->getCache($name);
			return;
		}

		$Cachenize->createCache($name, $time);
		$Cachenize->startCaching();
	}

	function cachenize_end() {
		global $Cachenize;
		if($Cachenize->cacheExists($Cachenize->getLastName())) {
			return;
		}

		$Cachenize->stopCaching();
		echo $Cachenize->getLastCache();
		return;
	}