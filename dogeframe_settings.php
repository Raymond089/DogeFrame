<?php

	$this->settings = [
		//wallet connection data (RPC)
		"rpc_ip" 			=> "localhost",		
		"rpc_port"			=> 22555,
		"rpc_user" 			=> "user",
		"rpc_password" 		=> "password",
		"rpc_protocol"		=> "https"			//if, for some reason, you can't use https to connect to the wallet, enter "http" here
		
		//database information
		"db_server" 		=> "localhost",
		"db_username"		=> "root",
		"db_password" 		=> "",
		"db_database" 		=> "your_database",
		"db_userTable" 		=> "usertable", 	//name of the table storing user data
		"db_userIdColumn" 	=> "user_id", 		//name of the column storing unique user ID (used for fetching users address and acount data)
		
		//general settings
		"minconf" 			=> 3,				//minimum confirmations required to count a deposit as valid
	];
?>
