<?php

class DotpayPaymentModuleFrontController extends ModuleFrontController
{
        
    public function initContent()
    {
        if (!Tools::getValue("ajax"))
        {
                $this->display_column_left = false;	
                parent::initContent();
                                
                if (Tools::getIsset('control'))
                {
                    $cart = new Cart((int)Tools::getValue('control'));
                } else {
                    $cart = $this->context->cart;              
                }

                if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
                        Tools::redirect('index.php?controller=order&step=1');
                
                // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
                $authorized = false;
                foreach (Module::getPaymentModules() as $module)
                if ($module['name'] == 'dotpay')
                {
                    $authorized = true;
                    break;
                }
                if (!$authorized)
                    die('This payment method is not available.');                 

                $address = new Address($cart->id_address_invoice);
                $customer = new Customer($cart->id_customer);
                if (!Validate::isLoadedObject($customer))
                    return;
                $currency_info = Currency::getCurrency($cart->id_currency);
                if ($cart->OrderExists() == true) 
                {
                        Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.Order::getOrderByCartId($cart->id).'&key='.$customer->secure_key);                    
                        exit;
                }
                $this->context->smarty->assign(array(
                        'module_dir' => $this->module->getPathUri(),
                        'dp_test' => Configuration::get('DP_TEST'),
                        'dp_id' => Configuration::get('DP_ID'),
                        'dp_control' => (int)$cart->id,
                        'dp_amount' => $cart->getOrderTotal(),
                        'dp_desc' => Configuration::get('PS_SHOP_NAME'), 
                        'customer' => $customer,
                        'address' => $address,
                        'currency' => $currency_info["iso_code"]
                ));
                $this->setTemplate('payment.tpl');
    }}
        
	public function displayAjax()
        {
                if($_SERVER['REMOTE_ADDR'] == '195.150.9.37' && $_SERVER['REQUEST_METHOD'] == 'POST') 
                {
                        if(Dotpay::check_urlc_legacy())
                        {
                                switch (Tools::getValue('t_status'))
                                {
                                    case 1:
                                        $actual_state = Configuration::get('PAYMENT_DOTPAY_NEW_STATUS');
                                        break;
                                    case 2:
                                        $actual_state = _PS_OS_PAYMENT_;
                                        break;
                                    case 3:
                                        $actual_state = _PS_OS_ERROR_;
                                        break;
                                    case 4:
                                        $actual_state = _PS_OS_ERROR_;
                                        break;
                                    case 5:
                                        $actual_state = Configuration::get('PAYMENT_DOTPAY_COMPLAINT_STATUS');   
                                    default:
                                        die ("WRONG TRANSACTION STATUS");
                                }
                                $cart = new Cart((int)Tools::getValue('control'));
                                $address = new Address($cart->id_address_invoice);
                                $customer = new Customer((int)$cart->id_customer);
                                $total = (float)($cart->getOrderTotal(true, Cart::BOTH));

                                if ($cart->OrderExists() == false)
                                {
                                    $this->module->validateOrder($cart->id, Configuration::get('PAYMENT_DOTPAY_NEW_STATUS'), $total, $this->module->displayName, NULL, array(), (int)$cart->id_currency, false, $customer->secure_key);
                                }
                                   
                                if ($order_id = Order::getOrderByCartId((int)Tools::getValue('control'))) 
                                {
                                        $history = new OrderHistory();
                                        $history->id_order = $order_id;
                                        $sql = 'SELECT total_paid FROM '._DB_PREFIX_.'orders WHERE id_cart = '.$cart->id.' and id_order = '.$order_id;
                                        $totalAmount = round(Db::getInstance()->getValue($sql),2);
                                        $postAmount = round(Tools::getValue('original_amount'),2);

                                        if ($toatalAmount > $postAmount) 
                                                die("INCORRECT AMOUNT $totalAmount > ".Tools::getValue('original_amount'));
                                        
                                        if ( OrderHistory::getLastOrderState($order_id) == _PS_OS_PAYMENT_ ) 
                                        {
                                                die('WRONG STATE');
                                        } else {
                                                $history->changeIdOrderState($actual_state, $order_id);
                                                $history->addWithemail(true);
                                                die ("OK");
                                        }
                                } else die('NO MATCHING ORDER');
                        } else die ("LEGACY MD5 ERROR - CHECK PIN");
                } else die("ERROR");
        }
}