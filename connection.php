<?php

	/**
	 * Singleton Database class
	 */
	class Database {

		private static $instance;

		private $collection;
		
		private $mongoDB;

		public function find($array) {
			$docs = $this->collection->find($array);
			return $docs;
		}
		
		public function getCollection() {
			return $this->collection;
		}
		
		public function getMongoDB() {
			return $this->mongoDB;	
		}

		public function insert($document) {
			$this->collection->insert($document);
			return True;
		}

		public function update($title, $document) {
			$this->collection->update($title, $document);
			return True;
		}

		/** Get singleton */
		public static function get_instance() {

			if (!isset(Database::$instance)) {
				Database::$instance = new Database();
			}

			return Database::$instance;
		}

		// private constructor
		final private function __construct() {

			$mongo = new MongoClient("130.245.168.236:27020/admin");
			#$mongo = new MongoClient(); // local

			$db = $mongo->stiki_db;
			
			$this->mongoDB = $db;
			
			$db = $mongo->selectDB("stiki_db");
			
			$this->collection = $db->stiki_db;
			
			//print_r("colllll: " . $this->collection . "<br><br>");
			
			//$this->collection->createIndex(array('title'=>'text'));

			$docs = $this->collection->find();

		}

		// We do not want clone to be implementable.
		final private function __clone() {}

	}

	/**
	 * Utilities class for Regular Expression replacement.
	 */
	class RegExUtilities {

		/**
		 * Replaces the underscores from a string with spaces.
		 */
		public static function replace_underscores($string) {

			return preg_replace("(_)", " ", $string);
		}

		/**
		 * Replaces the spaces from a string with underscores.
		 */
		public static function replace_spaces($string) {

			return preg_replace("/\s+/", "_", $string);
		}

		/**
		 * Replaces the backticks with single quotes.
		 */
		public static function replace_backticks($string) {

			return preg_replace("(`)", "'", $string);
		}

		/**
		 * Replaces the single quotes with backtaicks.
		 */
		public static function replace_singlequotes($string) {

			return preg_replace("(')", "`", $string);
		}		

		/**
		 * Replaces the single quotes with backtaicks.
		 */
		public static function replace_leftbrackets($string) {

			return preg_replace("/\[\[(\w+)\[\]/", "", $string);
		}
	}

	/**
	 * Manages the articles of the database.
	 */
	class DataManager {

		private static $instance;

		private $db;

		/**
		* This function updates the body of the string argument passed in
		*/
		public function exists($title) {

			// Make sure the title passed in has spaces instead of underscores
			$refined_title = RegExUtilities::replace_underscores($title);

			$exists = $this->check_title($refined_title);
			return $exists;
		}

		/**
		* Checks if title exists in DB
		*/
		private function check_title($title) {

			$document = array( 
				"title" => $title
			);

			$result = $this->db->find($document);

			return $result->count();
		}
		
		/**
		 * Create a list of results using the mongodb indexes
		 */
		public function mongo_search($search_word) {
				
				// Creates a wild card index for fast searching of title and body content
				$this->db->getCollection()->createIndex(array('$**' => 'text'));
				
				$result_cursor = $this->db->find(
				  ['$text' => ['$search' => $search_word]]
				);
				
				$article_array = array();
				
				foreach ($result_cursor as $doc) {
					
					$title = $doc['title'];
					$body = $doc['body'];
					array_push($article_array, $title);
					//print_r("<br>");
					//print_r($article_array);
					//print_r("<br>");
				}
				$results = $this->make_list($article_array);
				return $results;
		}
	
		/**
		 * Create a list of articles and return it in an array
		 */
		private function make_list($article_array) {
			 
			$result = array();
			$address_prefix = "~/stikipedia/search_test.php?title=";
				foreach($article_array as $article){
					$url_title = RegExUtilities::replace_spaces($article);
					array_push($result, $url_title);
				 	echo '<li><a href= "'.$url_title.'">'.$url_title.'</a></li>';
				}
			return $result;
		}
		
		/**
		 * Is this key a substring of the string value?
		 */
		private function is_substring($string,$key) {

			if (stripos($string,$key) !== false) 
    			return true;
			else 
				return false;
		}

		/**
		 * Gets the body of matching the title of an article, if it exists.
		 */
		public function get_body($title) {

			// replace '_'s with spaces in the title
			$refined_title = RegExUtilities::replace_underscores($title);

			$body = $this->get_raw_content($refined_title);

			// Remove back ticks, which were included to serve as single quotes so the MySQL server would not complain.
			$body = RegExUtilities::replace_backticks($body); 

			return $body;
		}

		/**
		* Gets the body of an article from its corresponding title from the database.
		*/
		private function get_raw_content($title) {

			$document = array( 
				"title" => $title
			);

			$result = $this->db->find($document);

			if ($result->count() > 0) {
			    $result->next();
			    $first = $result->current();
			    return $first['body'];
			} 

			return NULL;
		}

		/** 
		 * Insert a new article with a title and body into the database.
		 */
		public function add_new_article($title, $new_body) {

			$body = RegExUtilities::replace_singlequotes($new_body);

			$document = array( 
			      "title" => $title,
			      "body" => $body
			   );

			$result = $this->db->insert($document);

			// Return a message saying whether or not this query succeeded.
			if ($result) {
				$status = "CREATED";
			} else {
				$status = "FAILED";
			}

			return $status;
		}

		/**
		* This function updates the body of the string argument passed in.
		* This function will create a new article if it does not exist.
		* 
		* Returns "UPDATED", "CREATED", OR "FAILED"
		*/
		public function set_content($title, $new_body) {

			// Replace single quotes with back quotes for SQL server.
			$body = RegExUtilities::replace_singlequotes($new_body);

			// Check to see that title is valid
			if (!$this->check_valid_title($title)) {
				// Signal that this function has failed.
				$status = "0";
				return $status;
			}

			if (!$this->exists($title))
				$status = $this->add_new_article($title, $body); // creating a new article
			else
				$status = $this->update_content($title, $body);

			return $status;
		}

		/**
		 * Check if the given title only contains valid characters.
		 */
		private function check_valid_title($title) {

			$regex = '/^[a-zA-Z0-9 :\-\(\)]+$/i';

			return preg_match($regex, $title);
		}

		/**
		 * Update an existing article and give it a new body.
		 */
		private function update_content($title, $new_body) {

			$title_array = array(
				"title" => $title
			);

			$document = array( 
				"title" => $title,
			    "body" => $new_body
			);

			$result = $this->db->update($title_array, $document);

			// Return a message saying whether or not this query succeeded.	
			if($result) {
				$status = "UPDATED";
			}
			else {
				$status = "FAILED";	
			}

			return $status;
		}

		/** Get singleton */
		public static function get_instance() {

			if (!isset(DataManager::$instance)) {
				DataManager::$instance = new DataManager();
			}

			return DataManager::$instance;
		}

		// private constructor
		final private function __construct() {

			$this->db = Database::get_instance();

		}

		// We do not want clone to be implementable.
		final private function __clone() {}
	}
	
	?>
