<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/dotpay.php');

if($_SERVER['REMOTE_ADDR'] == '195.150.9.37' && $_SERVER['REQUEST_METHOD'] == 'POST')
{
	if(Dotpay::check_urlc($_POST))
	{
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
			
	}else{
		die('WRONG PARAMETERS');
	}
	
}else{
	die("WRONG IP OR REQUEST METHOD ADDR: {$_SERVER['REMOTE_ADDR']} METHOD: {$_SERVER['REQUEST_METHOD']}");
}
?>
