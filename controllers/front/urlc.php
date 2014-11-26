<?php

include(dirname(__FILE__).'/../../../../config/config.inc.php');
include(dirname(__FILE__).'/../../dotpay.php');

if (empty(Context::getContext()->link)) {
Context::getContext()->link = new Link();
}

if($_SERVER['REMOTE_ADDR'] == '195.150.9.37' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if(Dotpay::check_urlc()) {
        $params = $_POST;
        if(strlen(intval($params['id'])) != 6) {
//---------------------legacy  
            
switch ($params['t_status'])
{
case 1:
$params['operation_status'] = "new";
$params['operation_type']='payment';
break;
case 2:
$params['operation_status'] = "completed";
$params['operation_type']='payment';
break;
case 3:
$params['operation_status'] = "rejected";
$params['operation_type']='payment';
break;
case 4:
$params['operation_status'] = "4";
$params['operation_type']='payment';
break;
case 5:
$params['operation_status'] = "5";
$params['operation_type']='payment';    
}

$params['operation_original_amount'] = $params['orginal_amount'];

//--------------------------------end legacy
        }
        if($params['operation_type']!='payment') {
            echo 'OK';
        } else {
            switch ($params['operation_status']) {
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
                case 4:
                    $actual_state = _PS_OS_CANCELED_;
                    break;
                case 5:
                    $actual_state = Configuration::get('PAYMENT_DOTPAY_COMPLAINT_STATUS');
                default:
                    die('WRONG TRANSACTION STATUS');
            }
            //$totalShop = Db::getInstance()->getValue($sql);
            if ($order_id = Order::getOrderByCartId(intval($_POST['control']))) {
                $history = new OrderHistory();
                $history->id_order = intval($order_id);
                
                $sql = 'SELECT total_paid FROM '._DB_PREFIX_.'orders WHERE id_cart = '.intval($params['control']).' and id_order = '.$order_id;
                $totalAmount = Db::getInstance()->getValue($sql);
                
                $totalAmount = round($totalAmount,2);
                $postAmount = round($params["operation_original_amount"],2);
                
                if ($toatalAmount > $postAmount) {
                    die("INCORRECT AMOUNT $totalAmount > {$params["operation_original_amount"]}");
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
