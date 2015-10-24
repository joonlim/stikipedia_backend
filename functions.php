<?php
	include ("connection.php");
	
	/**
	 * Function called when message is received from producer.
	 */
	function receive_get_msg($title) {
		
		$title = ucwords($title);

		$db_manager = DataManager::get_instance();
		$result = $db_manager->get_body($title);

		if($result)
			echo "Body is being returned:\n $result\n";
		else
		{
			$result = "NULL";
			echo "$title does not exist.\n";
		}

		return $result;
	}

	/**
	 * Function called when message is received from producer.
	 */
	function receive_search_msg($search_term) {
		
		$db_manager = DataManager::get_instance();
		$result = $db_manager->mongo_search($search_term);

		$str = "<br/>";
		if (sizeof($result) > 0) {
			$str = implode("\n", $result);
			echo "Search results are being returned:\n $str\n";
		}
		else {
			echo "No results found.\n";
		}

		return $str;
	}

	/**
	 * Function called when message is received from producer.
	 */
	function receive_modify_msg($arr) {
		
		$title = $arr['title'];
		$body = $arr['body'];

		$db_manager = DataManager::get_instance();
		$status = $db_manager->set_content($title, $body);
		
		return $status;
	}

	function receive_rename_msg($arr) {


	}
?>
