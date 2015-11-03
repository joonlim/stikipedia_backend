<?php
	include ("rpc_server.php");
	include ("functions.php");

	// The file that contains the ip of the message broker in the first line.
	$file = "broker.txt";
	
	// Get queue name
	$queue_modify = "BACK_MODIFY";

	// Get RpcServer

	$rpcServer = new RpcServer($file);

	$rpcServer->start($queue_modify, 'receive_modify_msg');
?>