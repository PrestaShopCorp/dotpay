<?php
if (!defined('_PS_VERSION_'))
    exit;
function upgrade_module_0_8($object)
{
        // Process Module upgrade to 0.8
	unlink(__FILE__.'/../confirmation.php');
	unlink(__FILE__.'/../confirmation.tpl');
	unlink(__FILE__.'/../dotpay.jpg');
	unlink(__FILE__.'/../dotpay.tpl');
	unlink(__FILE__.'/../dp_pay.tpl');
	unlink(__FILE__.'/../payment.php');
	unlink(__FILE__.'/../pl.php');
	unlink(__FILE__.'/../urlc.php');
	return true; 
}