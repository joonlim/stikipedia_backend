<?php
	include ("rpc_server.php");
	include ("functions.php");

	// The file that contains the ip of the message broker in the first line.
	$file = "broker.txt";
	
	// Get queue name
	$queue_search = "back_search";

	// Get RpcServer

	$rpcServer = new RpcServer($file);

	$rpcServer->start($queue_search, 'receive_search_msg');
?>