<?php

class DotpayPaymentModuleFrontController extends ModuleFrontController
{   
    public function initContent()
    {
                $this->display_column_left = false;	
                parent::initContent();
                                
               /* if (Tools::getIsset('control'))
                {
                    $cart = new Cart((int)Tools::getValue('control'));
                } else {
                    $cart = $this->context->cart;              
                }*/

                $cart = $this->context->cart; 
                
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

                
		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');
                
                $address = new Address($cart->id_address_invoice);

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
                        'dp_amount' => (float)$cart->getOrderTotal(true, Cart::BOTH),
                        'dp_desc' => Configuration::get('PS_SHOP_NAME'), 
                        'customer' => $customer,
                        'address' => $address,
                        'currency' => Currency::getCurrency($cart->id_currency)["iso_code"]
                ));
                $this->setTemplate('payment.tpl');
    }
}