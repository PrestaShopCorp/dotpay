<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/dotpay.php');


$dp = new Dotpay();
echo $dp->execPayment($cart);


include_once(dirname(__FILE__).'/../../footer.php');

?>