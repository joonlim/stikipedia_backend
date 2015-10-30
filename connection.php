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

        public function remove($document) {
            $this->collection->remove($document);
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

		    $mongo = new MongoClient("130.245.168.182:27020/admin");
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
        private function exists_and_has_body($title) {

            // Make sure the title passed in has spaces instead of underscores
            $title = RegExUtilities::replace_underscores($title);

            $record = $this->get_article_record($title);

            if (!$record)
                return false;

            if (!$record['body'] || trim($record['body']) == "")
                return false;

            return true;
        }

		/**
		* Checks if title exists in DB
		*/
		private function check_title($title) {

            $record = $this->get_article_record($title);

            if (!$record)
                return false;

            return true;
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

			$record = $this->get_article_record($title);

			if ($record)
				return $record['body'];

			return NULL;
		}

		/**
		 * Gets the list of articles that contain links to this one.
		 */
		private function get_from_links($title) {

			$record = $this->get_article_record($title);

			if ($record)
				return $record['from_links'];

			return NULL;
		}

		/**
		 * Given a title, returns record of article in array form:
		 * {
		 * 		"title" : "Title",
		 * 		"body" : "Body",
		 * 		"from_links" : ["item1", "item2" ...]
		 * }
		 */
		private function get_article_record($title) {

			$document = array(
				"title" => $title
			);

			$result = $this->db->find($document);

			if ($result->count() > 0) {
				$result->next();
				$first = $result->current();

				$body = $first['body'];
				$from_links = $first['from_links'];

				$record = array(
					"title" => $title,
					"body" => $body,
					"from_links" => $from_links
				);

				return $record;
			}

			return NULL;
		}

		/** 
		 * Insert a new article with a title and body into the database.
		 */
		public function add_new_article($title, $new_body, $from_links) {

			$new_body = RegExUtilities::replace_singlequotes($new_body);

			if (!$new_body) {
				$new_body = $this->get_raw_content($title);
				if (!$new_body)
					$new_body = "";
			}

			if (!$from_links) {
				$from_links = $this->get_from_links($title);
				if (!$from_links)
					$from_links = array();
			}

			$document = array(
				"title" => $title,
				"body" => $new_body,
				"from_links" => $from_links
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
         * Delete a record with this title
         */
        private function remove_article($title) {

            $document = array("title" => $title);

            return $this->db->remove($document);
        }

		/**
		* This function updates the body of the string argument passed in.
		* This function will create a new article if it does not exist.
		* 
		* Returns "UPDATED", "CREATED", OR "FAILED"
		*/
		public function set_body($title, $new_body) {

			// Replace single quotes with back quotes for SQL server.
			$body = RegExUtilities::replace_singlequotes($new_body);

			// Check to see that title is valid
			if (!$this->check_valid_title($title)) {
				// Signal that this function has failed.
				$status = "0";
				return $status;
			}

			// array of links that the body contains
			$to_links = $this->get_to_links_from_body($new_body);
			if (!$this->exists($title)){
                $status = $this->add_new_article($title, $body, NULL); // creating a new article
            }

			else {
                $status = $this->update_content($title, $body, NULL);
            }


			// Iterate through each article in $to_links
			foreach ($to_links as $link) {
				// Get record of $link from db

				$link = ucwords(strtolower($link));

				$link_record = $this->get_article_record($link); //r

				if ($link_record) {
					// exists already
					$from_links = $link_record['from_links'];
                    if ((!$from_links))
                        $from_links = array();

					// Does the from_links contain the new/udated article already?
					if (!(in_array($title, $from_links))) {
						// nope. add it to the list and update record in db.
						array_push($from_links, $title);
					}

					$this->update_content($link, NULL, $from_links);
				} else {
					// create a stub of this article with just from_links
					$from_links = array();

					array_push($from_links, $title);

					$this->add_new_article($link, NULL, $from_links);

				}
			}

			return $status;
		}

		public function rename_article($old_title, $new_title) {

			// fail if old title does not exist
			if (!($this->exists_and_has_body($old_title)))
				return '{"status" : FAILED", "reason" : "Old title does not exist."}';

			// old title exists.
			// fail if new title exists
			if ($this->exists_and_has_body($new_title))
				return '{"status" : FAILED", "reason" : "New title already exists."}';

            // Now we can rename.
            // First, get the record of both $old_title
            $old_record = $this->get_article_record($old_title); // r
            $body = $old_record['body'];
            $from_links = $old_record['from_links'];

            // And get from_links of $new_title, if they exist.
            $new_from_links = $this->get_from_links($new_title);

            if (count($new_from_links) > 0) {
                // new title has from_links
                // Merge $from_links and $new_from_links
                $from_links = array_merge($from_links, $new_from_links);

                // Remove duplicates
                $from_links = array_unique($from_links);

                // Update previously existing record (no body though)
                $this->update_content($new_title, $body, $from_links);
            }
            else {
                // Leave $from_links alone
                // Create new record
                $this->add_new_article($new_title, $body, $from_links);
            }

            // Delete old title
            $this->remove_article($old_title);

            $to_links = $this->get_to_links_from_body($body);

            // Iterate through each article in $to_links and update all its from_links
            // by replacing $old_title with $new_title.
            $this->update_from_links_with_new_title($to_links, $old_title, $new_title);

            // Iterate through every article in $from_links and update its body
            // by replacing $old_title with $new_title
            $this->update_bodies_with_new_title($from_links, $old_title, $new_title);

			return "SUCCESS";
		}

        /**
         * Iterate through all articles in a given list and update all the from_links
         * by replacing $old_title with $new_title.
         */
        private function update_from_links_with_new_title($list_of_articles, $old_title, $new_title) {
            foreach ($list_of_articles as $article) {

                // Get record from db
                $article_name = ucwords(strtolower($article));

                $article_record = $this->get_article_record($article_name); //r

                // redundant checking?
                if ($article_record) {
                    $from_links = $article_record['from_links'];

                    // more redundant checking
                    if (($index = array_search($old_title, $from_links)) !== false) {
                        unset($list_of_articles[$index]); // remove old title
                    }
                    array_push($from_links, $new_title); // add new title

                    $article_body = $article_record['body'];

                    $this->update_content($article_name, $article_body, $from_links);
                }
            }
        }

        /**
        * Iterate through all articles in a given list and update all the bodies by
        * replacing $old_title with $new_title.
        */
        private function update_bodies_with_new_title($list_of_articles, $old_title, $new_title) {
            foreach ($list_of_articles as $article) {

                // Get record from db
                $article_name = ucwords(strtolower($article));

                $article_record = $this->get_article_record($article_name); //r

                $article_body = $article_record['body'];

                // replace all links to $old_title with links to $new_title
                $body = $this->replace_old_links_in_body($article_body, $old_title, $new_title);

                $from_links = $article_record['from_links'];

                $this->update_content($article_name, $body, $from_links);
            }
        }

        /**
         * Update article body by replacing all links to $old_title with $new_title and
         * return the result.
         */
        private function replace_old_links_in_body($body, $old_title, $new_title) {

            // Case: [[Title]]
            $pattern = "(\[\[[ ]*($old_title)[ ]*[\]]{2})";
            $fixed_body = preg_replace($pattern, "[[$new_title]]", $body);

            // Case: [[Title#
            $pattern = "(\[\[[ ]*($old_title)[ ]*[#])";
            $fixed_body = preg_replace($pattern, "[[$new_title#", $fixed_body);

            // Case: [[Title|
            $pattern = "(\[\[[ ]*($old_title)[ ]*[|])";
            $fixed_body = preg_replace($pattern, "[[$new_title|", $fixed_body);
            return $fixed_body;

        }

        /**
		 * Check if the given title only contains valid characters.
		 */
		private function check_valid_title($title) {

			$regex = '/^[a-zA-Z0-9 :\-\(\)]+$/i';

			return preg_match($regex, $title);
		}

		# == GET EVERYTHING IN BRACKETS ==
		private function get_to_links_from_body($string) {

			// Pattern: matches all strings of the form ([[STRING]), ([[STRING|), or ([[STRING#)
			$pattern = "(\[\[([^\]\]]*?)[\]#|])";

			// Stores all strings that match the pattern in array $matches
			preg_match_all($pattern, $string, $matches);

			return $matches[1];
		}

		/**
		 * Update an existing article and give it a new body.
		 */
		private function update_content($title, $new_body, $from_links) {

			$title_array = array(
				"title" => $title
			);

			if (!($new_body)) {
				$new_body = $this->get_raw_content($title);
				if (!($new_body))
					$new_body = "";
			}

			if (!($from_links)) {
				$from_links = $this->get_from_links($title);
				if (!($from_links))
					$from_links = array();
			}

			$document = array( 
				"title" => $title,
			    "body" => $new_body,
				"from_links" => $from_links
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
