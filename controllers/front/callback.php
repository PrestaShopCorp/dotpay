<?php
/**
*
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Piotr Karecki <tech@dotpay.pl>
*  @copyright dotpay
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*/

class dotpaycallbackModuleFrontController extends ModuleFrontController
{
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
                                //$address = new Address($cart->id_address_invoice);
                                $customer = new Customer((int)$cart->id_customer);
                                $total = (float)($cart->getOrderTotal(true, Cart::BOTH));

                                if ($cart->OrderExists() == false)
                                    $this->module->validateOrder($cart->id, Configuration::get('PAYMENT_DOTPAY_NEW_STATUS'), (float)($cart->getOrderTotal(true, Cart::BOTH)), $this->module->displayName, NULL, array(), (int)$cart->id_currency, false, $customer->secure_key);
                                   
                                if ($order_id = Order::getOrderByCartId((int)Tools::getValue('control'))) 
                                {
                                        $sql = 'SELECT total_paid FROM '._DB_PREFIX_.'orders WHERE id_cart = '.$cart->id.' and id_order = '.$order_id;
                                        $totalAmount = round(Db::getInstance()->getValue($sql),2);
                                        $dotpay_amount = round(Tools::getValue('orginal_amount'),2);
                                        if ($totalAmount <> $dotpay_amount) 
                                                die('INCORRECT AMOUNT '.$totalAmount.' <> '.$dotpay_amount);                                        
                                      
                                        if (strpos($totalAmount, ".") == false)
                                                $totalAmount .= ".00";
                                        $currency = Currency::getCurrency($cart->id_currency);
                                        $totalAmount .= " ".$currency["iso_code"];
                                        $orginal_amount = trim(Tools::getValue('orginal_amount'));
                                        if ($totalAmount <> $orginal_amount) 
                                                die('INCORRECT ORG. AMOUNT '.$totalAmount.' <> '.$orginal_amount);

                                        $history = new OrderHistory();
                                        $history->id_order = $order_id;
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