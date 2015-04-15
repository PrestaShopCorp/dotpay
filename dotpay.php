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
		$this->version = '1.4.6';
                $this->author = 'tech@dotpay.pl';

		parent::__construct();

		$this->displayName = $this->l('dotpay');
		$this->description = $this->l('dotpay payment module');
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
        Configuration::updateValue('DP_SSL', false);
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
        Configuration::deleteByName('DP_SSL');
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
            $this->_postProcess();
            $this->context->smarty->assign(array(
                'module_dir' => $this->_path,
                'DOTPAY_CONFIGURATION_OK' => Configuration::get('DOTPAY_CONFIGURATION_OK', false),
                'DP_URLC' => $this->context->link->getModuleLink('dotpay', 'callback', array('ajax' => '1')),
                'DP_MSG' => $this->_dpConfigForm,
                'DP_URI' => $_SERVER['REQUEST_URI']
            ));
            $form_values = $this->getConfigFormValues();
            foreach ($form_values as $key => $value)
                $this->context->smarty->assign($key, $value);
            if (version_compare(_PS_VERSION_, "1.6.0", ">=")) {
                $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
                return $output.$this->renderForm();
            } else 
                return $this->display(__FILE__, 'views/templates/admin/content.tpl');
        } 

	/**
	 * Create the form that will be displayed in the configuration of your module.
	 */
	protected function renderForm()
	{
		$helper = new HelperForm();

		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$helper->module = $this;
		$helper->default_form_language = $this->context->language->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitDotpayModule';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
			.'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		$helper->tpl_vars = array(
                        'fields_value' => $this->getConfigFormValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($this->getConfigForm()));
	}

	/**
	 * Create the structure of your form.
	 */
	protected function getConfigForm()
	{
		return array(
			'form' => array(
				'legend' => array(
				'title' => $this->l('Settings'),
				'icon' => 'icon-cogs',
				),
				'input' => array(
					array(
						'type' => 'switch',
                                           	'label' => $this->l('Test mode'),
						'name' => 'DP_TEST',
						'is_bool' => true,
						'desc' => $this->l('Use this module in test environment'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => true,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => false,
								'label' => $this->l('Disabled')
							)
						),
					),
                                        array(
						'type' => 'switch',
                                           	'label' => $this->l('Use SSL'),
						'name' => 'DP_SSL',
                                                'desc' => $this->l('Secure Sockets Layer cryptographic protocol'),
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => true,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => false,
								'label' => $this->l('Disabled')
							)
						),
					),                                                                        
                                        array(
						'type' => 'switch',
                                           	'label' => $this->l('CHK mode'),
						'name' => 'DP_CHK',
                                                'desc' => $this->l('Secure payment parameters'),
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => true,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => false,
								'label' => $this->l('Disabled')
							)
						),
					),                                    
					array(
						'type' => 'text',
						'name' => 'DP_ID',
						'label' => $this->l('ID'),
					),
					array(
						'type' => 'text',
						'name' => 'DP_PIN',
						'label' => $this->l('PIN'),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				),
			),
		);
	}

	/**
	 * Set values for the inputs.
	 */
	protected function getConfigFormValues()
	{
		return array(
			'DP_TEST' => Configuration::get('DP_TEST', false),
			'DP_CHK'  => Configuration::get('DP_CHK', false),
			'DP_SSL'  => Configuration::get('DP_SSL', false),
			'DP_ID' => Configuration::get('DP_ID'),
			'DP_PIN' => Configuration::get('DP_PIN'),
		);
	}

	/**
	 * Save form data.
	 */
	protected function _postProcess()
	{
            $values = $this->getConfigFormValues();
            if (Tools::getValue("submitDotpayModule", false) && is_numeric($values["DP_ID"]) && !empty($values["DP_PIN"])) {
                foreach (array_keys($values) as $key)
                    $values[$key] = trim(Tools::getValue($key));
                $values["DOTPAY_CONFIGURATION_OK"] = true;
                $values["DP_SSL"] = Configuration::get('PS_SSL_ENABLED') && Tools::getValue("DP_SSL");               
            }
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
