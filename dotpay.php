<?php
if ( !defined( '_PS_VERSION_' ) )
exit('nie zdefioniowanych \n');

class dotpay extends PaymentModule {
		
    private $_dpConfigForm;
	
    public function __construct()
    {
		$this->name = 'dotpay';
		$this->tab = 'payments_gateways';
                $this->version = '1.2.2';
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

    private function addNewOrderState($state, $names, $color)
    {
            if (!empty((int)Configuration::get($state))) return true;
            $order_state = new OrderState();
            $order_state->name = array();
            foreach (Language::getLanguages() as $language)
            {
                if (Tools::strtolower($language['iso_code']) == 'pl') $order_state->name[$language['id_lang']] = $names[1];
                else $order_state->name[$language['id_lang']] = $names[0];
            }
            $order_state->send_email = false;
            $order_state->invoice = false;
            $order_state->unremovable = false;
            $order_state->color = $color;
            $order_state->module_name = $this->name;
            
            if ($order_state->add() || Configuration::updateValue($state, $order_state->id)) return true;
                else return false;
    }    
    
    public function install()
    {
        return (
                parent::install() &&
                Configuration::updateValue('DP_ID', '') &&
                Configuration::updateValue('DP_PIN', '') &&
                Configuration::updateValue('DP_TEST', '') &&
                $this->registerHook('payment') &&
                $this->registerHook('paymentReturn') &&
                $this->addNewOrderState('PAYMENT_DOTPAY_NEW_STATUS', array('Awaiting payment confirmation', 'Oczekuje potwierdzenia płatności'),'lightblue') &&
                $this->addNewOrderState('PAYMENT_DOTPAY_COMPLAINT_STATUS', array('Complaint', 'Rozpatrzona reklamacja'),'darkred')
        );   
    }
    
    public function uninstall()
    {
        return(
            Db::getInstance()->Execute("DELETE FROM " . _DB_PREFIX_ . "order_state WHERE id_order_state = " . Configuration::get('PAYMENT_DOTPAY_NEW_STATUS')) &&
            Db::getInstance()->Execute("DELETE FROM " . _DB_PREFIX_ . "order_state_lang WHERE id_order_state = " . Configuration::get('PAYMENT_DOTPAY_NEW_STATUS')) &&
            Db::getInstance()->Execute("DELETE FROM " . _DB_PREFIX_ . "order_state WHERE id_order_state = " . Configuration::get('PAYMENT_DOTPAY_COMPLAINT_STATUS')) &&
            Db::getInstance()->Execute("DELETE FROM " . _DB_PREFIX_ . "order_state_lang WHERE id_order_state =  " . Configuration::get('PAYMENT_DOTPAY_COMPLAINT_STATUS')) &&
            Configuration::deleteByName('DP_ID') &&
            Configuration::deleteByName('DP_PIN') &&
            Configuration::deleteByName('DP_TEST') &&
            Configuration::deleteByName('PAYMENT_DOTPAY_NEW_STATUS') &&
            Configuration::deleteByName('PAYMENT_DOTPAY_COMPLAINT_STATUS') &&                
            Configuration::deleteByName('DOTPAY_CONFIGURATION_OK') &&                
            parent::uninstall()
        );
    }	
	// Function for display cinfiguration in back-office
    public function getContent()
    {
		 // Checking for incoming configuration data
                 // TODO Security checks
		if(Tools::getIsset('Save_DP'))
                    {
			Configuration::updateValue('DP_ID', (int)Tools::getValue('dp_id'));
			Configuration::updateValue('DP_PIN', Tools::getValue('dp_pin'));
			Configuration::updateValue('DP_TEST', Tools::getValue('dp_test'));
                        Configuration::updateValue('DOTPAY_CONFIGURATION_OK', true);
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
        if (!$this->active) return;
        if (empty((int)Configuration::get('DP_ID'))) return;
        $this->smarty->assign(array('module_dir' => $this->_path));
	return $this->display(__FILE__, 'payment.tpl');
    }
    
    // Some hooks
    public function hookPaymentReturn($params)
    {
        if (!$this->active)
            return;
        
        $customer = new Customer((int)$params['objOrder']->id_customer);
        if (!Validate::isLoadedObject($customer))
            return;
        
        if ($is_guest) $form_url=$this->context->link->getPageLink('guest-tracking', true);
        else $form_url=$this->context->link->getPageLink('history', true);
   
        $param = array(
            'order_reference' => $params['objOrder']->reference,
            'email' => $customer->email,
            'submitGuestTracking' => 1       
        );
        $this->smarty->assign(array(
            'params' => $param,
            'module_dir' => $this->getPathUri(),
            'form_url' => $form_url,
        ));
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




















