<?php

require "dogecoin.php";

$settings = [
	//wallet connection data (RPC)
	"rpc_ip" 		=> "localhost",		
	"rpc_port"		=> 44555,
	"rpc_user" 		=> "dogecoinrpc",
	"rpc_password" 		=> "password",
	"rpc_protocol"		=> "https",

	//database information
	"db_server" 		=> 'localhost',
	"db_username"		=> "root",
	"db_password" 		=> "",
	"db_database" 		=> "DogeFrame"
];
	
$mysqli = mysqli_connect($settings['db_server'] , $settings['db_username'], $settings['db_password'], $settings['db_database']);
$dogecoin = new Dogecoin($settings['rpc_user'], $settings['rpc_password'], $settings['rpc_ip'], $settings['rpc_port'], $settings['rpc_protocol'] );
$address = $dogecoin->getnewaddress('DogeFrame');
if ($stmt = $mysqli->prepare('INSERT IGNORE INTO usertable (doge_address) VALUES (?)')) {
	$stmt->bind_param("s", $address);
	if (!$stmt->execute()) {
		throw new Exception($mysql->error);
	}
	$stmt->close();
}
?>
