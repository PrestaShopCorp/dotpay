<?php
if ( !defined( '_PS_VERSION_' ) )
exit('nie zdefioniowanych \n');

class dotpay extends PaymentModule {
		
    private $_dpConfigForm;
	
    public function __construct()
    {
		$this->name = 'dotpay';
		$this->tab = 'payments_gateways';
                $this->version = '1.0.9';
                $this->author = 'tech@dotpay.pl';
		//Removed due to bug in PrestaShop 1.5
                //$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
                $this->currencies = true;
		parent::__construct();
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('dotpay');
		$this->description = $this->l('Dotpay payment module');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall dotpay payment module?');
    }

    public function install()
    {
		if (!parent::install()
                            || !$this->registerHook('payment') 
                            || !$this->registerHook('paymentReturn') 
                            || !Configuration::updateValue('DP_ID', '') 
                            || !Configuration::updateValue('DP_PIN', '') 
                            || !Configuration::updateValue('DP_TEST', '')) {
			return false;   
                }
                
		if (Validate::isInt(Configuration::get('PAYMENT_DOTPAY_NEW_STATUS')) XOR (Validate::isLoadedObject($order_state_new = new OrderState(Configuration::get('PAYMENT_DOTPAY_NEW_STATUS')))))
                    {
			$order_state_new = new OrderState();
                        foreach (Language::getLanguages(false) as $language) 
                            $order_state_new->name[$language['id_lang']] = "Awaiting payment confirmation";
                        $order_state_new->name[Language::getIdByIso("pl")] = "Oczekuje potwierdzenia platnosci";
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
                        foreach (Language::getLanguages(false) as $language)
                            $order_state_new->name[$language['id_lang']] = "Complaint";
                        $order_state_new->name[Language::getIdByIso("pl")] = "Rozpatrzona reklamacja";
			$order_state_new->send_email = false;
			$order_state_new->invoice = false;
			$order_state_new->unremovable = false;
			$order_state_new->color = "darkred";
			if (!$order_state_new->add())
				return false;
			if(!Configuration::updateValue('PAYMENT_DOTPAY_COMPLAINT_STATUS', $order_state_new->id))
				return false;
                    }
        return true;        
    }
	
    public function uninstall()
    {
		if (!Configuration::deleteByName('DP_ID')
				|| !Configuration::deleteByName('DP_PIN')
				|| !Configuration::deleteByName('DP_TEST')
				|| !parent::uninstall())
			return false;
		return true;
    }	
	// Function for display cinfiguration in back-office
    public function getContent()
    {
		 // Checking for incoming configuration data
                 // TODO Security checks
		if(Tools::getIsset('Save_DP'))
                    {
			Configuration::updateValue('DP_ID', (int) Tools::getValue('dp_id'));
			Configuration::updateValue('DP_PIN', Tools::getValue('dp_pin'));
			Configuration::updateValue('DP_TEST', Tools::getValue('dp_test'));
			$this->_dpConfigForm = 'OK';
                    }
		
		// Display of configuration fields
		$this->smarty->assign(array(
			'DP_ID' => Configuration::get('DP_ID'),
			'DP_PIN' => Configuration::get('DP_PIN'),
			'DP_TEST' => Configuration::get('DP_TEST'),
			'DP_MSG' => $this->_dpConfigForm,
			'DP_URI' => $_SERVER['REQUEST_URI']
		));
                return $this->display(__FILE__, 'views/templates/admin/content.tpl');
    }
    // Some hooks
    public function hookPayment()
    {
        if (!$this->active)
            return;
        $this->smarty->assign(array('module_dir' => $this->_path));
	return $this->display(__FILE__, 'payment.tpl');
    }
    
    // Some hooks
    public function hookPaymentReturn($params)
    {
        if (!$this->active)
            return;
        $this->smarty->assign('reference', $params['objOrder']->reference);
        $customer = new Customer((int)$params['objOrder']->id_customer);
        if (!Validate::isLoadedObject($customer))
            return;
        $this->smarty->assign('email',$customer->email);
        return $this->display(__FILE__, 'payment_return.tpl');
    }
    
    static public function check_urlc() 
            {
        if(Tools::strlen((int) Configuration::get('DP_ID')) == 6) {
            return Dotpay::check_urlc_dev();
	} else 
            {
            return Dotpay::check_urlc_legacy();
	}
    }
		
    
	// Payment confirmation
    static public function check_urlc_dev() 
            {
                $signature=
			Configuration::get('DP_PIN').
			Configuration::get('DP_ID'). 
                        Tools::getValue('operation_number').
			Tools::getValue('operation_type').
			Tools::getValue('operation_status').
			Tools::getValue('operation_amount').
			Tools::getValue('operation_currency').
			Tools::getValue('operation_original_amount').
			Tools::getValue('operation_original_currency').
			Tools::getValue('operation_datetime').
			Tools::getValue('operation_related_number').
			Tools::getValue('control').
			Tools::getValue('description').
			Tools::getValue('email').
			Tools::getValue('p_info').
			Tools::getValue('p_email').
			Tools::getValue('channel');
	$signature=hash('sha256', $signature);
        return (Tools::getValue('signature') == $signature);
    }
	
    static public function check_urlc_legacy() 
            {
		$signature =
			Configuration::get('DP_PIN').":".
			Configuration::get('DP_ID').":".
			Tools::getValue('control').":".
			Tools::getValue('t_id').":".
			Tools::getValue('amount').":". 
			Tools::getValue('email').":".
			Tools::getValue('service').":".  
			Tools::getValue('code').":".
			Tools::getValue('username').":".
			Tools::getValue('password').":".
			Tools::getValue('t_status');
	$signature=hash('md5', $signature);
	return (Tools::getValue('md5') == $signature);
    }    
     
}




















