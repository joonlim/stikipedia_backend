<?php
	include ("rpc_server.php");
	include ("functions.php");

	// The file that contains the ip of the message broker in the first line.
	$file = "broker.txt";
	
	// Get queue name
	$queue_rename = "BACK_RENAME";

	// Get RpcServer

	$rpcServer = new RpcServer($file);

	$rpcServer->start($queue_rename, 'receive_rename_msg');
?>