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
	protected $config_form = false;

	public function __construct()
	{
		$this->name = 'dotpay';
		$this->tab = 'payments_gateways';
		$this->version = '1.3.1';
                $this->author = 'tech@dotpay.pl';

		parent::__construct();

		$this->displayName = $this->l('dotpay');
		$this->description = $this->l('dotpay payment module');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall dotpay payment module?');
	}
	
    private function addNewOrderState($state, $names, $color)
    {
            $order_status = new OrderState((int)Configuration::get($state), (int)$this->context->language->id);
            if (Validate::isLoadedObject($order_status)) return true;
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
	
        Configuration::updateValue('DP_TEST', false);
        Configuration::updateValue('DP_ID', '');
        Configuration::updateValue('DP_PIN', '');
        Configuration::updateValue('DOTPAY_CONFIGURATION_OK', false);
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
        $sql = 'SELECT `id_order_state` FROM '._DB_PREFIX_.'order_state WHERE `module_name` = "dotpay"';
        if (!$result = Db::getInstance()->ExecuteS($sql))
            return false;
        
        $sql=array();
        foreach ($result as $query) 
        {
            $sql[] = "DELETE FROM " . _DB_PREFIX_ . "order_state WHERE id_order_state = " . $query["id_order_state"];
            $sql[] = "DELETE FROM " . _DB_PREFIX_ . "order_state_lang WHERE id_order_state = " . $query["id_order_state"];
        }   
        foreach ($sql as $query)
            if (Db::getInstance()->execute($query) == false)
                return false;
        
        return    
            Configuration::deleteByName('DP_ID') &&
            Configuration::deleteByName('DP_PIN') &&
            Configuration::deleteByName('DP_TEST') &&
            Configuration::deleteByName('PAYMENT_DOTPAY_NEW_STATUS') &&
            Configuration::deleteByName('PAYMENT_DOTPAY_COMPLAINT_STATUS') &&                
            Configuration::deleteByName('DOTPAY_CONFIGURATION_OK') &&                
            parent::uninstall();
    }	


	
	/**
	 * Load the configuration form
	 */
	public function getContent()
	{
		/**
		 * If values have been submitted in the form, process.
		 */
		$this->_postProcess();

		$this->context->smarty->assign(array(
                    'module_dir' => $this->_path,
                    'DP_URLC' => $this->context->link->getModuleLink('dotpay', 'callback').'?ajax=1'
                        ));

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

		return $output.$this->renderForm();
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
			'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
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
                Configuration::updateValue('DOTPAY_CONFIGURATION_OK', true);
		foreach (array_keys($form_values) as $key)
			Configuration::updateValue($key, Tools::getValue($key));
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
        if (!$this->active) return;
        if (empty((int)Configuration::get('DP_ID'))) return;
        $this->smarty->assign(array('module_dir' => $this->_path));
	return $this->display(__FILE__, 'payment.tpl');
    }

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
