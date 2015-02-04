<?php

require "dogeframe.php";

$dogeframe = new Dogeframe();
$balance = $dogeframe->getBalance(1);
echo $balance . "\n";
?>
