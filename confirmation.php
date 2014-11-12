<?php

include('../../config/config.inc.php');
include(_PS_ROOT_DIR_.'/header.php');
include('./dotpay.php');


$dp = new Dotpay();
$smarty->assign(array(	'HOOK_PAYMENT_RETURN' => $dp->hookPaymentReturn()));

$smarty->display(_PS_THEME_DIR_.'order-confirmation.tpl');

include(_PS_ROOT_DIR_.'/footer.php');

?>