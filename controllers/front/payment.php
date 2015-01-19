<?php

class dotpaypaymentModuleFrontController extends ModuleFrontController
{   
    public function initContent()
    {
                $this->display_column_left = false;	
                parent::initContent();
                $control=(int)Tools::getValue('control');
                $cart = $this->context->cart;              
                if (!empty($control))
                    $cart = new Cart($control);

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
                $params = null;
                $template = "payment_return";
                if ($cart->OrderExists() == true) 
                    Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.Order::getOrderByCartId($cart->id).'&key='.$customer->secure_key);                    
                elseif (Tools::getValue("status") == "OK")
                    $form_url= $this->context->link->getModuleLink('dotpay', 'payment').'?control='.$cart->id.'&status=OK';
                else {
                    $template = "payment";
                    $form_url="https://ssl.dotpay.pl/";
                    if (Configuration::get('DP_TEST')==1) $form_url.="test_payment/";
                    $params = array(
                            'id' => Configuration::get('DP_ID'),
                            'amount' => (float)$cart->getOrderTotal(true, Cart::BOTH),
                            'currency' => Currency::getCurrency($cart->id_currency)["iso_code"],
                            'description' => Configuration::get('PS_SHOP_NAME'), 
                            'url' => $this->context->link->getModuleLink('dotpay', 'payment').'?control='.$cart->id,                            
                            'type' => 0,                        
                            'urlc' => $this->context->link->getModuleLink('dotpay', 'callback').'?ajax=1',
                            'control' => $cart->id,
                            'firstname' => $customer->firstname,
                            'lastname' => $customer->lastname,                        
                            'email' => $customer->email,
                            'street' => $address->address1,
                            'city' => $address->city,
                            'postcode'=> $address->postcode,
                            'api_version' => 'legacy'
                    );
                }
                $this->context->smarty->assign(array(
                            'params' => $params,
                            'module_dir' => $this->module->getPathUri(),
                            'form_url' => $form_url,
                            'return' => $return
                            ));
                $this->setTemplate($template.".tpl");
                
 
    }
}