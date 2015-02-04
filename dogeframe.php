<?php
	class DogeFrame{	
		
		function __construct()
		{			
			require "dogecoin.php";
			require "dogeframe_settings.php";
			//connect to the database
			$this->connection = mysqli_connect($this->settings['db_server'] , $this->settings['db_username'], $this->settings['db_password'], $this->settings['db_database']);
			
			$this->dogecoin = new Dogecoin($this->settings['rpc_user'], $this->settings['rpc_password'], $this->settings['rpc_ip'], $this->settings['rpc_port'], $this->settings['rpc_protocol'] );
		}
		
		public function getError()
		{
			return $this->dogecoin -> error;
		}
		
		public function getStatus()
		{
			
			$this->dogecoin->getinfo();
		
			if ($this->dogecoin -> raw_response == "")
			{
				return "OFFLINE";
			}			
			else
			{			
				return $this->dogecoin->status;
			}
		}
	
		public function generateAddress()
		{
			$this->dogecoin -> getnewaddress();
			return $this->dogecoin->response['result'];
		}
		
		public function getBalance($uID)
		{
			$uID = (int)$uID;
			//fetch data from database
			$dogeUserData = mysqli_fetch_assoc(mysqli_query($this->connection, "SELECT `doge_spend`, `doge_received`, `doge_withdraw`, `doge_available`, `doge_deposit`, `doge_address` FROM `".$this->settings['db_userTable']."` WHERE `".$this->settings['db_userIdColumn']."`='".$uID."'"));
			
			//check total received
			$received = $this->dogecoin->getreceivedbyaddress($dogeUserData['doge_address'], $this->settings['minconf']);
			
			//check if there was a new deposit			
			if ($received != $dogeUserData['doge_deposit'])
			{
				//new deposit posted, add transaction
				$depositAmount = $received - $dogeUserData['doge_deposit'];				
				mysqli_query($this->connection, "INSERT INTO `doge_transactions` (`send_user`, `receive_user`, `amount`, `time`, `status`) VALUES ('0', '$uID', '$depositAmount', '".time()."', 'D')");							
			}			
			
			//calculate balance
			$balance = (($received + $dogeUserData['doge_received']) - ($dogeUserData['doge_spend'] + $dogeUserData['doge_withdraw']));
			
			//write new totals
			mysqli_query($this->connection, "UPDATE `".$this->settings['db_userTable']."` SET `doge_deposit`='$received', `doge_available`='$balance' WHERE `".$this->settings['db_userIdColumn']."`='$uID'");	
			
			//return balance
			return $balance;
		}
			
		public function makeTransaction($sendUser, $receiveUser, $amount)
		{
			$sendUser 		= (int)$sendUser;
			$receiveUser 	= (int)$receiveUser;
			$amount			= abs((float)$amount);
			$dogeSenderData = mysqli_fetch_assoc(mysqli_query($this->connection, "SELECT `doge_spend`, `doge_available` FROM `".$this->settings['db_userTable']."` WHERE `".$this->settings['db_userIdColumn']."`='$sendUser'"));
			if ($dogeSenderData['doge_available'] >= $amount)
			{
				//update senders spend and balance field
				mysqli_query($this->connection, "UPDATE `".$this->settings['db_userTable']."` SET `doge_spend`='".($dogeSenderData['doge_spend'] + (float)$amount)."', `doge_available`='".($dogeSenderData['doge_available'] - $amount)."' WHERE `".$this->settings['db_userIdColumn']."`='$sendUser'");
				//update receivers received and balance field
				$dogeReceiverData = mysqli_fetch_assoc(mysqli_query($this->connection, "SELECT `doge_spend`, `doge_available` FROM `".$this->settings['db_userTable']."` WHERE `".$this->settings['db_userIdColumn']."`='".$receiveUser."'"));
				mysqli_query($this->connection, "UPDATE `".$this->settings['db_userTable']."` SET `doge_received`='".($dogeReceiverData['doge_spend'] + $amount)."', `doge_available`='".($dogeReceiverData['doge_available'] + $amount)."' WHERE `".$this->settings['db_userIdColumn']."`='$receiveUser'");
				//add transaction into table
				mysqli_query($this->connection, "INSERT INTO `doge_transactions` (`send_user`, `receive_user`, `amount`, `time`, `status`) VALUES ('$sendUser', '$receiveUser', '$amount', '".time()."', 'T')");
				
				//return new balance of sender to confirm succes
				return ($dogeSenderData['doge_available'] - $amount);
			}
			else
			{
				//balance is insuficient
				return -1;
			}
		}
		
		public function withdrawDoge($uID, $address, $amount)
		{
			$uID		= (int)$uID;
			$address	= mysqli_real_escape_string($this->connection, strip_tags($address));
			$amount		= abs((float)$amount);
			if ((isset($uID))&&(isset($address))&&(isset($amount)))
			{
				$dogeSenderData = mysqli_fetch_assoc(mysqli_query($this->connection, "SELECT `doge_spend`, `doge_available`, `doge_withdraw`  FROM `".$this->settings['db_userTable']."` WHERE `".$this->settings['db_userIdColumn']."`='".$uID."'"));
				if ($dogeSenderData['doge_available'] >= $amount)
				{
					//check the address validity
					if ((strlen($address) == 34) && (substr($address, 0,1)=='D'))
					{
						//do the actual withdrawal, the -1 represents the network-TX fee
						$this->dogecoin->sendtoaddress($address, $amount-1);
						
						//update senders withdraw and balance field
						mysqli_query($this->connection, "UPDATE `".$this->settings['db_userTable']."` SET `doge_withdraw`='".($dogeSenderData['doge_withdraw'] + $amount)."', `doge_available`='".($dogeSenderData['doge_available'] - $amount)."' WHERE `".$this->settings['db_userIdColumn']."`='$uID'");
										
						//add transaction into table
						mysqli_query($this->connection, "INSERT INTO `doge_transactions` (`send_user`, `amount`, `time`, `txID`, `status`) VALUES ('$uID', '$amount', '".time()."', '".$this->dogecoin->response["result"]."', 'W')");	
						
						//return txID of tx to confirm succes
						return $this->dogecoin->response["result"];
					}
					else
					{
						//invalid address
						return -2;
					}
				}
				else
				{
					//balance is insuficient
					return -1;
				}
			}
			else
			{
				return -3;
			}
		}
		
		public function depositAddress($uID)
		{
			$uID	= (int)$uID;
			//this function simply retrieves the deposit address of a user identified by $uID 
			$dogeAddressData = mysqli_fetch_assoc(mysqli_query($this->connection, "SELECT `doge_address` FROM `".$this->settings['db_userTable']."` WHERE `".$this->settings['db_userIdColumn']."`='".$uID."'"));
			return $dogeAddressData['doge_address'];
		}
	}	
?>
