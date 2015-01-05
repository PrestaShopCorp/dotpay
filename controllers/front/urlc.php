<?php

include(dirname(__FILE__).'/../../../../config/config.inc.php');
include(dirname(__FILE__).'/../../dotpay.php');
/*
if (empty(Context::getContext()->link)) {
Context::getContext()->link = new Link();
}
*/
if($_SERVER['REMOTE_ADDR'] == '195.150.9.37' && $_SERVER['REQUEST_METHOD'] == 'POST') 
    {
    if(Dotpay::check_urlc()) 
        {
        $params = $_POST;
        if(Tools::strlen((int) $params['id']) != 6) {
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
        if($params['operation_type']!='payment') 
            {
            echo 'OK';
        } else 
            {
            switch ($params['operation_status']) 
            {
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
            if ($order_id = Order::getOrderByCartId((int)$_POST['control'])) 
                {
                $history = new OrderHistory();
                $history->id_order = (int) $order_id;
                
                $sql = 'SELECT total_paid FROM '._DB_PREFIX_.'orders WHERE id_cart = '.(int) $params['control'].' and id_order = '.$order_id;
                $totalAmount = round(Db::getInstance()->getValue($sql),2);

                $postAmount = round($params["operation_original_amount"],2);
                
                if ($toatalAmount > $postAmount) 
                    {
                    die("INCORRECT AMOUNT $totalAmount > {$params["operation_original_amount"]}");
                }

                if ( OrderHistory::getLastOrderState($order_id) == _PS_OS_PAYMENT_ ) 
                    {
                    die('WRONG STATE');
                } else
                    {
                    $history->changeIdOrderState($actual_state, (int) $order_id);
                    $history->addWithemail(true);
                    echo "OK";
                }	
            } else 
                {
                die('NO MATCHING ORDER');
            }
        } 
       
    } else 
        {
	die('WRONG SIGNATURE - CHECK PIN');
    }
} else 
    {
    Tools::redirect("../");
    exit;
}
