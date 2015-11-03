<?php
	include ("rpc_server.php");
	include ("functions.php");

	// The file that contains the ip of the message broker in the first line.
	$file = "broker.txt";
	
	// Get queue name
	$queue_get = "BACK_GET";

	// Get RpcServer

	$rpcServer = new RpcServer($file);

	$rpcServer->start($queue_get, 'receive_get_msg');
?>
