<?php
if ( !defined( '_PS_VERSION_' ) )
exit('nie zdefioniowanych \n');

class dotpay extends PaymentModule {
		
    private $urlc_param = array();
    private $_dpConfigForm;
	
    public function __construct()
    {
		$this->name = 'dotpay';
		$this->tab = 'payments_gateways';
                $this->version = '0.9';
                $this->author = 'dotpay.pl';
                $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
		$this->currencies = true;
		parent::__construct();
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('dotpay.pl');
		$this->description = $this->l('dotpay.pl on-line payment');
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
                 global $smarty;
		 // Checking for incoming configuration data
                 // TODO Security checks
		if(isset($_POST['Save_DP']))
                    {
			Configuration::updateValue('DP_ID', intval($_POST['dp_id']));
			Configuration::updateValue('DP_PIN', $_POST['dp_pin']);
			Configuration::updateValue('DP_TEST', $_POST['dp_test']);
			$this->_dpConfigForm = 'OK';
                    }
		
		// Display of configuration fields
		$smarty->assign(array(
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
        if (!$this->active) {
            return;
        }
        $this->smarty->assign(array('module_dir' => $this->_path));
	return $this->display(__FILE__, 'payment.tpl');
    }
    
    // Some hooks
    public function hookPaymentReturn($params)
    {
        $this->smarty->assign('reference', $params['objOrder']->reference);
        $customer = new Customer((int)$params['objOrder']->id_customer);
        $this->smarty->assign('email',$customer->email);
        return $this->display(__FILE__, 'payment_return.tpl');
    }
    
    static public function check_urlc() {
        if(strlen(intval(Configuration::get('DP_ID'))) == 6) {
            return Dotpay::check_urlc_dev();
	} else {
            return Dotpay::check_urlc_legacy();
	}
    }
		
	// Payment confirmation
    static public function check_urlc_dev() {
                $signature=
			Configuration::get('DP_PIN').
			Configuration::get('DP_ID'). 
                        (isset($_POST['operation_number'])?$_POST['operation_number']:'').
			(isset($_POST['operation_type'])?$_POST['operation_type']:'').
			(isset($_POST['operation_status'])?$_POST['operation_status']:'').
			(isset($_POST['operation_amount'])?$_POST['operation_amount']:'').
			(isset($_POST['operation_currency'])?$_POST['operation_currency']:'').
			(isset($_POST['operation_original_amount'])?$_POST['operation_original_amount']:'').
			(isset($_POST['operation_original_currency'])?$_POST['operation_original_currency']:'').
			(isset($_POST['operation_datetime'])?$_POST['operation_datetime']:'').
			(isset($_POST['operation_related_number'])?$_POST['operation_related_number']:'').
			(isset($_POST['control'])?$_POST['control']:'').
			(isset($_POST['description'])?$_POST['description']:'').
			(isset($_POST['email'])?$_POST['email']:'').
			(isset($_POST['p_info'])?$_POST['p_info']:'').
			(isset($_POST['p_email'])?$_POST['p_email']:'').
			(isset($_POST['channel'])?$_POST['channel']:'');
	$signature=hash('sha256', $signature);
        return ($_POST['signature'] == $signature);
    }
	
    static public function check_urlc_legacy() {
		$signature =
			Configuration::get('DP_PIN').":".
			Configuration::get('DP_ID').":".
			$_POST['control'].":".
			$_POST['t_id'].":".
			$_POST['amount'].":". 
			$_POST['email'].":".
			$_POST['service'].":".  
			$_POST['code'].":".
			$_POST['username'].":".
			$_POST['password'].":".
			$_POST['t_status'];
	$signature=hash('md5', $signature);
	return ($_POST['md5'] == $signature);
    }
}




















