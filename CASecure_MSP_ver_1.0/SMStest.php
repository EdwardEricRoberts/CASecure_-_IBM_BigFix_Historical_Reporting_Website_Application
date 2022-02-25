<?php

$to = "9705682034@vtext.com";
$from = "scoon@cassevern.com";
$message = "This is a sample text message\nfrom Steve Coon using PHP for the MSP project";
$headers = "From: $from\n";
ini_set('sendmail_from','scoon@cassevern.com');
mail($to, '', $message, $headers);

?>