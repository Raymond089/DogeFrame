<?php

require "dogeframe.php";

$dogeframe = new Dogeframe();
$balance = $dogeframe->depositAddress(1);
echo $balance . "\n";
?>
