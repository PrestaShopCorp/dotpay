<?php

include(dirname(__FILE__).'/../../../../config/config.inc.php');
include(dirname(__FILE__).'/../../dotpay.php');

if(/*$_SERVER['REMOTE_ADDR'] == '195.150.9.37' && *//*$_SERVER['REQUEST_METHOD'] == 'POST'*/1 == 1) {
    if(Dotpay::check_urlc()) {
        if(strlen(intval($_POST['id'])) == 6) {
         //---------------------dev
                    if($_POST['operation_type']!='payment') {
            echo 'OK';
        } else {
            switch ($_POST['operation_status']) {
                case "new":
                    $actual_state = Configuration::get('PAYMENT_DOTPAY_NEW_STATUS');
                    break;
                case "processing":
                    $actual_state = Configuration::get('PAYMENT_DOTPAY_NEW_STATUS');
                    break;
                case "completed":
                    $actual_state = _PS_OS_PAYMENT_;
                    break;
                case "rejected":
                    $actual_state = _PS_OS_ERROR_;
                    break;
                /*case 4:
                    $actual_state = _PS_OS_CANCELED_;
                    break;*/
                /*case 5:
                    $actual_state = Configuration::get('PAYMENT_DOTPAY_COMPLAINT_STATUS');*/
                default:
                    die('WRONG TRANSACTION STATUS');
            }
            //$totalShop = Db::getInstance()->getValue($sql);
            if ($order_id = Order::getOrderByCartId(intval($_POST['control']))) {
                $history = new OrderHistory();
                $history->id_order = intval($order_id);
                
                $sql = 'SELECT total_paid FROM '._DB_PREFIX_.'orders WHERE id_cart = '.intval($_POST['control']).' and id_order = '.$order_id;
                $totalAmount = Db::getInstance()->getValue($sql);
                
                $totalAmount = round($totalAmount,2);
                $postAmount = round($_POST["operation_original_amount"],2);
                
                if ($toatalAmount > $postAmount) {
                    die("INCORRECT AMOUNT $totalAmount > {$_POST["operation_original_amount"]}");
                }

                if ( OrderHistory::getLastOrderState($order_id) == _PS_OS_PAYMENT_ ) {
                    die('WRONG STATE');
                }else{
                    $history->changeIdOrderState($actual_state, intval($order_id));
                    $history->addWithemail(true);
                    echo "OK";
                }	
            } else {
                die('NO MATCHING ORDER');
            }
        } 
        } else {
            //---------------------------------legacy
            	switch ($_POST['t_status'])
{
case 1:
$actual_state = Configuration::get('PAYMENT_DOTPAY_NEW_STATUS');
break;
case 2:
$actual_state = _PS_OS_PAYMENT_;
break;
case 3:
$trans_state = _PS_OS_ERROR_;
break;
case 4:
$actual_state = _PS_OS_CANCELED_;
break;
case 5:
$actual_state = Configuration::get('PAYMENT_DOTPAY_COMPLAINT_STATUS');
default:
die('WRONG TRANSACTION STATUS');
}
$totalShop = Db::getInstance()->getValue($sql);
if ($order_id = Order::getOrderByCartId(intval($_POST['control'])))
{
$history = new OrderHistory();
$history->id_order = intval($order_id);
$amountAndCurrency = explode(" ",$_POST["orginal_amount"]);
$sql = 'SELECT total_paid FROM '._DB_PREFIX_.'orders WHERE id_cart = '.intval($_POST['control']).' and id_order = '.$order_id;
$totalAmount = Db::getInstance()->getValue($sql);
$totalAmount = round($totalAmount,2);
$postAmount = round($amountAndCurrency[0],2);
if ($toatalAmount > $postAmount) {
die("INCORRECT AMOUNT $totalAmount > {$_POST['orginal_amount']}");
}
if ( OrderHistory::getLastOrderState($order_id) == _PS_OS_PAYMENT_ ) {
die('WRONG STATE');
}else{
$history->changeIdOrderState($actual_state, intval($order_id));
$history->addWithemail(true);
echo "OK";
}
}else{
die('NO MATCHING ORDER');
}

        }
    } else {
	die('WRONG SIGNATURE - CHECK PIN');
    }
} else {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
						
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
						
    header("Location: ../");
    exit;
}
