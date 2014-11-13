<?php


class DotpayPaymentModuleFrontController extends ModuleFrontController
{
    	public function init()
	{
		$this->display_column_left = false;
		parent::init();
	}
        
	public function initContent()
	{
		parent::initContent();

		$cart = $this->context->cart;
                
		global $smarty;
		$address = new Address(intval($cart->id_address_invoice));
		$customer = new Customer(intval($cart->id_customer));
		$this->module->validateOrder((int)$cart->id, Configuration::get('PAYMENT_DOTPAY_NEW_STATUS'), $cart->getOrderTotal(), $this->module->displayName, NULL, array(), NULL, false, $customer->secure_key);
		$order = new Order($this->module->currentOrder);
		$currency_id = $cart->id_currency;
		$currency_info = Currency::getCurrency($currency_id);
		$smarty->assign(array(
                        'module_dir' => $this->module->getPathUri(),
			'dp_test' => Configuration::get('DP_TEST'),
			'dp_id' => Configuration::get('DP_ID'),
			'dp_control' => intval($cart->id),
			'dp_amount' => $cart->getOrderTotal(),
			'dp_desc' => Configuration::get('PS_SHOP_NAME'), 
			//'dp_url' => 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/controllers/front/confirmation.php',
                        //'index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key)
                        'dp_url' => 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key,
                        'dp_urlc' => 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->module->name.'/controllers/front/urlc.php',
			'customer' => $customer,
			'address' => $address,
			'currency' => $currency_info["iso_code"]
		));

		$this->setTemplate('payment.tpl');
	}
}
