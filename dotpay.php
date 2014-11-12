<?php
if ( !defined( '_PS_VERSION_' ) )
exit('nie zdefioniowanych \n');

class Dotpay extends PaymentModule {
		
	private $urlc_param = array();
	private $_dpConfigForm;
	
	public function __construct()
	{
		$this->name = 'dotpay';
		$this->tab = 'payments_gateways';
		$this->version = '0.75';
		$this->currencies = true;
		parent::__construct();
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('dotpay');
		$this->description = $this->l('Dotpay.pl on-line payment');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall dotpay payment module?');
	}
	

	public function install()
	{
		parent::install();
		if(!$this->registerHook('payment') || !$this->registerHook('paymentReturn') || !Configuration::updateValue('DP_ID', '') || !Configuration::updateValue('DP_PIN', ''))
			return false;
		
		if (Validate::isInt(Configuration::get('PAYMENT_DOTPAY_NEW_STATUS')) XOR (Validate::isLoadedObject($order_state_new = new OrderState(Configuration::get('PAYMENT_DOTPAY_NEW_STATUS')))))
		{
			$order_state_new = new OrderState();
			$order_state_new->name[Language::getIdByIso("pl")] = "Oczekuje potwierdzenia platnosci";
			$order_state_new->name[Language::getIdByIso("en")] = "Awaiting payment confirmation";
			$order_state_new->send_email = false;
			$order_state_new->invoice = false;
			$order_state_new->unremovable = false;
			$order_state_new->color = "lightblue";
			if (!$order_state_new->add())
				return false;
			if(!Configuration::updateValue('PAYMENT_DOTPAY_NEW_STATUS', $order_state_new->id))
				return false;
		}
		
		if (Validate::isInt(Configuration::get('PAYMENT_DOTPAY_COMPLAINT_STATUS')) XOR (Validate::isLoadedObject($order_state_new = new OrderState(Configuration::get('PAYMENT_DOTPAY_COMPLAINT_STATUS')))))
		{
			$order_state_new = new OrderState();
			$order_state_new->name[Language::getIdByIso("pl")] = "Rozpatorzna reklamacja";
			$order_state_new->name[Language::getIdByIso("en")] = "Complaint";
			$order_state_new->send_email = false;
			$order_state_new->invoice = false;
			$order_state_new->unremovable = false;
			$order_state_new->color = "darkred";
			if (!$order_state_new->add())
				return false;
			if(!Configuration::updateValue('PAYMENT_DOTPAY_COMPLAINT_STATUS', $order_state_new->id))
				return false;
		}
	}
	
	public function uninstall()
	{
		if (!Configuration::deleteByName('DP_ID') OR !Configuration::deleteByName('DP_PIN'))
			return false;
		return true;
	} 
	
	public function getContent()
	{
		if(isset($_POST['Save_DP'])){
			Configuration::updateValue('DP_ID', intval($_POST['dp_id']));
			Configuration::updateValue('DP_PIN', $_POST['dp_pin']);
			$this->_dpConfigForm .= 'OK';
		}
		
		$this->_dpConfigForm .= "<h2>Dotpay</h2>
			<form action='".$_SERVER['REQUEST_URI']."' method='post'>
			ID : <input type='text' name='dp_id' value='".Configuration::get('DP_ID')."'/>
			PIN : <input type='text' name='dp_pin' value='".Configuration::get('DP_PIN')."'/> </br>
			<input type='submit' name='Save_DP' value='Save' />
		";
		
		
		return $this->_dpConfigForm;
	}
	
	public function hookPayment($params)
	{
		global $smarty;
		
		return $this->display(__FILE__, 'dotpay.tpl');
	}
	
	
	public function hookPaymentReturn($params)
	{
		global $smarty;
		
		return $this->display(__FILE__, 'confirmation.tpl');
	}
	
	public function execPayment($cart)
	{
		global $smarty;
		$address = new Address(intval($cart->id_address_invoice));
		$customer = new Customer(intval($cart->id_customer));
		
		
		$this->validateOrder((int)$cart->id, Configuration::get('PAYMENT_DOTPAY_NEW_STATUS'), $cart->getOrderTotal(), $this->displayName, NULL, array(), NULL, false, $customer->secure_key);
		$order = new Order((int)$this->currentOrder);

		$currency_id = $cart->id_currency;
		$currency_info = Currency::getCurrency($currency_id);
		
		$smarty->assign(array(
			'dp_id' => Configuration::get('DP_ID'),
			'dp_control' => intval($cart->id),
			'dp_amount' => $cart->getOrderTotal(),
			'dp_desc' => Configuration::get('PS_SHOP_NAME'), 
			'dp_url' => 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/confirmation.php',
			'dp_urlc' => 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/urlc.php',
			'customer' => $customer,
			'address' => $address,
			'currency' => $currency_info["iso_code"]
		));
		return $this->display(__FILE__, 'dp_pay.tpl');
	}

	
	public function check_urlc($params){
        $md5 = md5(Configuration::get('DP_PIN').":".Configuration::get('DP_ID').":".$params['control'].":".$params['t_id'].":".$params['amount'].":".$params['email'].":".$params['service'].":".$params['code'].":".$params['username'].":".$params['password'].":".$params['t_status']);
        
        return ($params['md5'] == $md5);
    }
}
?>
