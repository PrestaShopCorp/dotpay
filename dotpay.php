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

if (!defined('_PS_VERSION_'))
	exit;

class dotpay extends PaymentModule
{
    	const DOTPAY_PAYMENTS_TEST_CUSTOMER = '701169';
        const DOTPAY_PAYMENTS_TEST_CUSTOMER_PIN = 'CNiqWSUnfMaeEyWT3mwZch8xl2IbKv9U';
	protected $config_form = false;

	public function __construct()
	{
		$this->name = 'dotpay';
		$this->tab = 'payments_gateways';
		$this->version = '0.9.1';
                $this->author = 'tech@dotpay.pl';

		parent::__construct();

		$this->displayName = $this->l('dotpay');
		$this->description = $this->l('dotpay legacy payment module');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall dotpay payment module?');
	}
	
    private function addNewOrderState($state, $names, $color)
    {
            $query='SELECT * FROM `'._DB_PREFIX_.'order_state` os LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$this->context->language->id.') WHERE module_name = "dotpay"';
            if ($result = Db::getInstance()->ExecuteS($query))
                foreach ($result as $row)
                    if ($row["name"] == $names[1] || $row["name"] == $names[0]) {
                        Configuration::updateValue($state, $row["id_order_state"]);
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . 'order_state SET `deleted` = 0 WHERE `id_order_state` = ' . $row["id_order_state"]);       
                    };

            $order_status = new OrderState((int)Configuration::get($state), (int)$this->context->language->id);
            if (Validate::isLoadedObject($order_status)) 
                return true;
            
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
	
        Configuration::updateValue('DP_TEST', true);
        Configuration::updateValue('DP_CHK', false);
        Configuration::updateValue('DP_ID', self::DOTPAY_PAYMENTS_TEST_CUSTOMER);
        Configuration::updateValue('DP_PIN', self::DOTPAY_PAYMENTS_TEST_CUSTOMER_PIN);
        Configuration::updateValue('DOTPAY_CONFIGURATION_OK', true);
        return 
            parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn') &&
            $this->addNewOrderState('PAYMENT_DOTPAY_NEW_STATUS', array('Awaiting payment confirmation', 'Oczekuje potwierdzenia płatności'),'lightblue') &&
            $this->addNewOrderState('PAYMENT_DOTPAY_COMPLAINT_STATUS', array('Complaint', 'Rozpatrzona reklamacja'),'darkred');   
    }
	
    public function uninstall()
    {
        $sql = array(
                "UPDATE " . _DB_PREFIX_ . 'order_state SET `deleted` = 1 WHERE `module_name` = "dotpay"',
                "UPDATE " . _DB_PREFIX_ . "order_state SET `deleted` = 1 WHERE id_order_state = " . pSQL(Configuration::get('PAYMENT_DOTPAY_NEW_STATUS')),
                "UPDATE " . _DB_PREFIX_ . "order_state SET `deleted` = 1 WHERE id_order_state = " . pSQL(Configuration::get('PAYMENT_DOTPAY_COMPLAINT_STATUS'))
            );

        foreach ($sql as $query)
                Db::getInstance()->execute($query);

        Configuration::deleteByName('DP_ID');
        Configuration::deleteByName('DP_PIN');
        Configuration::deleteByName('DP_TEST');
        Configuration::deleteByName('DP_CHK');
        Configuration::deleteByName('PAYMENT_DOTPAY_NEW_STATUS');
        Configuration::deleteByName('PAYMENT_DOTPAY_COMPLAINT_STATUS');
        Configuration::deleteByName('DOTPAY_CONFIGURATION_OK');
        
        return parent::uninstall();
    }	


	
	/**
	 * Load the configuration form
	 */

        public function getContent()
        {
            global $smarty;
            // Checking for incoming configuration data
               $this->_postProcess();      
            // Display of configuration fields
            $form_values = $this->getConfigFormValues();
            foreach ($form_values as $key => $value)
                $smarty->assign($key, $value);
            $smarty->assign('DOTPAY_CONFIGURATION_OK', Configuration::get('DOTPAY_CONFIGURATION_OK', false));
 
/*            $smarty->assign(array(
                'module_dir' => $this->_path,
                'DOTPAY_CONFIGURATION_OK' => Configuration::get('DOTPAY_CONFIGURATION_OK', false),
                'DP_URLC' => $this->context->link->getModuleLink('dotpay', 'callback', array('ajax' => '1')),
                'DP_ID' => Configuration::get('DP_ID'),
                'DP_PIN' => Configuration::get('DP_PIN'),
                'DP_TEST' => Configuration::get('DP_TEST'),
                'DP_MSG' => $this->_dpConfigForm,
                'DP_URI' => $_SERVER['REQUEST_URI']
            ));*/
            return $this->display(__FILE__, 'views/templates/admin/content.tpl');
        } 

	/**
	 * Set values for the inputs.
	 */
	protected function getConfigFormValues()
	{
		return array(
			'DP_TEST' => Configuration::get('DP_TEST', false),
                        'DP_CHK'  => Configuration::get('DP_CHK', false),
			'DP_ID' => Configuration::get('DP_ID'),
			'DP_PIN' => Configuration::get('DP_PIN'),
		);
	}

	/**
	 * Save form data.
	 */
	protected function _postProcess()
	{
            $form_values = $this->getConfigFormValues();
            $values = array();
            foreach (array_keys($form_values) as $key)
                $values[$key] = trim(Tools::getValue($key));
            $values["DOTPAY_CONFIGURATION_OK"] = true;
            if(Tools::getValue("submitDotpayModule", false) 
                    && is_numeric($values["DP_ID"])
                    && !empty($values["DP_PIN"]))
                foreach ($values as $key => $value)
                    Configuration::updateValue($key, $value);
	}

	/**
	* Add the CSS & JavaScript files you want to be loaded in the BO.
	*/
	public function hookBackOfficeHeader()
	{
		$this->context->controller->addJS($this->_path.'js/back.js');
		$this->context->controller->addCSS($this->_path.'css/back.css');
	}

	/**
	 * Add the CSS & JavaScript files you want to be added on the FO.
	 */
	public function hookHeader()
	{
		$this->context->controller->addJS($this->_path.'/js/front.js');
		$this->context->controller->addCSS($this->_path.'/css/front.css');
	}

    public function hookPayment()
    {
        $this->smarty->assign(array('module_dir' => $this->_path));
        if ($this->active && Configuration::get('DOTPAY_CONFIGURATION_OK'))
            return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active)
            return;
        
        $customer = new Customer($params['objOrder']->id_customer);
        if (!Validate::isLoadedObject($customer))
            return;
        
       
        if ((bool)Context::getContext()->customer->is_guest) $form_url=$this->context->link->getPageLink('guest-tracking', true);
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
