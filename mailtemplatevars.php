<?php
/**
* 2022 dani9
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
*  @author    dani9 <dani.strba@gmail.com>
*  @copyright 2022 dani9
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

use PrestaShop\PrestaShop\Adapter\MailTemplate\MailPartialTemplateRenderer;

require_once _PS_MODULE_DIR_ . 'mailtemplatevars/classes/mailSubject.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class mailTemplateVars extends Module
{
    /** @var MailPartialTemplateRenderer */
    protected $partialRenderer;

    public function __construct()
	{
		$this->name = 'mailtemplatevars';
		$this->tab = 'other';
		$this->version = '1.0.0';
		$this->author = 'dani9';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = [
			'min' => '1.7.7',
			'max' => '1.7.9',
		];
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->trans('Email Template Variables', [], 'Modules.Mailtemplatevars.Settings');
		$this->description = $this->trans('Adding various variables to e-mail templates.', [], 'Modules.Mailtemplatevars.Settings');

		$this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', [], 'Modules.Mailtemplatevars.Settings');
	}

	public function install()
	{
		if (Shop::isFeatureActive()) {
			Shop::setContext(Shop::CONTEXT_ALL);
		}

		$this->createTable();

        return (
			parent::install()
				&& $this->registerHook('sendMailAlterTemplateVars')
                && $this->registerHook('actionEmailSendBefore')
        );
    }

    public function uninstall()
	{
		$this->deleteTable();

	    return (
	        parent::uninstall()
	    );
	}

	public function createTable()
	{
		include(dirname(__FILE__).'/sql/install.php');
	}

	public function deleteTable()
	{
		include(dirname(__FILE__).'/sql/uninstall.php');
	}

	/**
	 * This method handles the module's configuration page
	 * @return string The page's HTML content
	 */
	public function getContent()
	{
	    $output = '';

	    // this part is executed only when the form is submitted
	    if (Tools::isSubmit('submit' . $this->name))
		{
            $update = $this->processSaveMailSubject();

            if (!$update) {
                $output = $this->displayError($this->trans('An error occurred on saving.', [], 'Modules.Mailtemplatevars.Settings'));
            }
        }

		$this->context->smarty->assign([
			'author' => $this->author,
			'module_version' => $this->version,
            'notice' => $this->trans('For this to work you need to make an override of OrderHistory.php sendMail function and make the following changes', [], 'Modules.Mailtemplatevars.Settings'),
            'code' => '33 - SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`, os.`module_name`, os.`id_order_state`, os.`pdf_invoice`, os.`pdf_delivery`<br>33 + SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`, os.`module_name`, os.`id_order_state`, os.`pdf_invoice`, os.`pdf_delivery`, msl.`subject`<br>39 + LEFT JOIN `" . _DB_PREFIX_ . "mailtplvars_subjects_lang` msl ON (os.`id_order_state` = msl.`id_order_state` AND msl.`id_lang` = o.`id_lang`)<br>44 - $topic = $result["osname"];<br>44 + $topic = (isset($result["subject"]) && !empty($result["subject"])) ? $result["subject"] : $result["osname"];'
		]);

		// display any message, then the form
		return $output . $this->display(__FILE__, 'views/templates/admin/header.tpl') . $this->displayForm();
    }

    public function processSaveMailSubject()
    {
        $order_states = $this->getAllOrderStates();
        
        $subj = [];
        foreach ($order_states as $order_state)
        {
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang)
            {
                $subj[$lang['id_lang']] = (string) $_POST['OS-' . $order_state['id_order_state'] . '_' . $lang['id_lang']];
                $saved = true;
                $subject = new mailSubject($order_state['id_order_state']);
                $subject->subject = $subj;
                $saved &= $subject->save();
            }
        }

        return $saved;
    }

    /**
	 * Builds the configuration form
	 * @return string HTML code
	 */
	public function displayForm()
	{
        $default_lang = $this->context->language->id;

        $order_states = $this->getAllOrderStates();
        $inputs = [];
        foreach ($order_states as $order_state) {
            $inputs[] = [
                'type' => 'text',
                'label' => $order_state['name'],
                'name' => 'OS-' . $order_state['id_order_state'],
                'lang' => true,
            ];
		};

        // Init Fields form array
		$form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Main settings', [], 'Modules.Mailtemplatevars.Settings'),
                ],
                'input' => $inputs,
                'submit' => [
                    'title' => $this->trans('Save', [], 'Modules.Mailtemplatevars.Settings'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];
        
		$helper = new HelperForm();
        $helper->module = $this;
		$helper->name_controller = $this->name;
        $helper->table = 'mailtplvars_subjects_lang';
        $helper->identifier = $this->identifier;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
		$helper->submit_action = 'submit' . $this->name;
        $helper->default_form_language = $default_lang;

        foreach (Language::getLanguages(false) as $lang) {
            $helper->languages[] = [
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0),
            ];
        }

        $helper->fields_value = $this->getFormValues();

		return $helper->generateForm($form);
	}

    public function getFormValues()
    {
        $order_states = $this->getAllOrderStates();
        $fields_value = [];
        foreach ($order_states as $order_state)
        {
            $subject = new mailSubject((int)$order_state['id_order_state']);
    
            $fields_value['OS-' . $order_state['id_order_state']] = $subject->subject;
        }

        return $fields_value;
    }

	/**
 	 * Action called prior sending email on when new order is created.
 	 */
    public function hookActionEmailSendBefore($params)
    {
        if (isset($this->context->cart->id)) {
            $id_order = Order::getIdByCartId($this->context->cart->id);
            if ($id_order && ($order = new Order($id_order)) && Validate::isLoadedObject($order) && $params['template'] == 'order_conf') {

                $order_data_tpl = self::getOrderData($params['templateVars']['{order_name}']);
        
                $params['templateVars']['{reference}'] = $order_data_tpl['order']->reference;
                $params['templateVars']['{products}'] = $order_data_tpl['product_list_html'];
                $params['templateVars']['{products_txt}'] = $order_data_tpl['product_list_txt'];
                $params['templateVars']['{discounts}'] = $order_data_tpl['cart_rules_list_html'];
                $params['templateVars']['{discounts_txt}'] = $order_data_tpl['cart_rules_list_txt'];
                $params['templateVars']['{totals}'] = $order_data_tpl['summary'];
                $params['templateVars']['{totals_txt}'] = $order_data_tpl['summary_txt'];
                $params['templateVars']['{payment}'] = $order_data_tpl['order']->payment;
                $params['templateVars']['{order_url}'] = $order_data_tpl['order_url'];
            }
        }

    }
  
    /**
     * @param $param
     * @return string
     */
    public function hooksendMailAlterTemplateVars($params)
    {
        if ($params['template'] != 'order_conf')
        {
            $context = Context::getContext();
            $order_data_tpl = self::getOrderData($params['template_vars']['{order_name}']);

            if ($params['template'] == 'backoffice_order' || $params['template'] == 'order_changed')
            {
                $params['template_vars']['{reference}'] = $order_data_tpl['order']->reference;
                $params['template_vars']['{date}'] = Tools::displayDate($order_data_tpl['order']->date_add, null, 1);
                $params['template_vars']['{products}'] = $order_data_tpl['product_list_html'];
                $params['template_vars']['{products_txt}'] = $order_data_tpl['product_list_txt'];
                $params['template_vars']['{discounts}'] = $order_data_tpl['cart_rules_list_html'];
                $params['template_vars']['{discounts_txt}'] = $order_data_tpl['cart_rules_list_txt'];
                $params['template_vars']['{totals}'] = $order_data_tpl['summary'];
                $params['template_vars']['{totals_txt}'] = $order_data_tpl['summary_txt'];
                $params['template_vars']['{delivery_block_txt}'] = $this->_getFormatedAddress($order_data_tpl['delivery'], AddressFormat::FORMAT_NEW_LINE);
                $params['template_vars']['{invoice_block_txt}'] = $this->_getFormatedAddress($order_data_tpl['invoice'], AddressFormat::FORMAT_NEW_LINE);
                $params['template_vars']['{delivery_block_html}'] = $this->_getFormatedAddress($order_data_tpl['delivery'], '<br />', [
                    'firstname' => '<span style="font-weight:bold;">%s</span>',
                    'lastname' => '<span style="font-weight:bold;">%s</span>',
                ]);
                $params['template_vars']['{invoice_block_html}'] = $this->_getFormatedAddress($order_data_tpl['invoice'], '<br />', [
                    'firstname' => '<span style="font-weight:bold;">%s</span>',
                    'lastname' => '<span style="font-weight:bold;">%s</span>',
                ]);
                $params['template_vars']['{carrier}'] = (!isset($order_data_tpl['carrier']->name)) ? $this->trans('No carrier', [], 'Modules.Mailtemplatevars.Mail') : $order_data_tpl['carrier']->name;
                $params['template_vars']['{payment}'] = $order_data_tpl['order']->payment;
                $params['template_vars']['{order_url}'] = $order_data_tpl['order_url'];
            }
            
            if ($params['template'] == 'bankwire')
            {
                $params['template_vars']['{bankwire_owner}'] = Configuration::get('BANK_WIRE_OWNER');
                $params['template_vars']['{bankwire_details}'] = nl2br(Configuration::get('BANK_WIRE_DETAILS') ?: '');
                $params['template_vars']['{total_paid}'] = self::formatMailPrice($order_data_tpl['order']->total_paid_tax_incl);
                $params['template_vars']['{order_url}'] = $order_data_tpl['order_url'];
            }
    
            if ($params['template'] == 'in_transit')
            {
                $params['template_vars']['{carrier}'] = $order_data_tpl['carrier']->name;
                $params['template_vars']['{tracking_number}'] = $order_data_tpl['tracking_number'];
                $params['template_vars']['{order_url}'] = $order_data_tpl['order_url'];
            }
    
            $order_btn_templates = [
                'custom_case',
                'order_canceled',
                'order_merchant_comment',
                'order_ready_pickup',
                'outofstock',
                'payment',
                'payment_error',
                'preparation',
                'shipped',
            ];
            if (in_array($params['template'], $order_btn_templates))
            {
                $params['template_vars']['{order_url}'] = $order_data_tpl['order_url'];
            }

            // the following lines need to be commented out in classes/Mail.php in order not to send the shop logo as attachment
            // template file (header twig) needs to be adsjusted accordingly by changing {shop_url} with full link url
            // if (isset($logo)) {
            //     $templateVars['{shop_logo}'] = $message->embed(\Swift_Image::fromPath($logo));
            // }
        }
    }

    /**
     *
     * @param string $reference
     *
     * @return mixed data for use in email templates
     */
    protected function getOrderData($reference)
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $query = new DbQuery();
        $query->select('o.id_order, o.reference, o.id_carrier, o.id_lang, o.id_customer, o.id_cart, o.id_address_delivery, o.id_address_invoice, o.payment, (o.codfee + o.codfeetax) as cod');
        $query->from('orders', 'o');
        $query->where('o.reference = "' . pSQL($reference) . '"');
        
        $order_data = $db->getRow($query);

        $order = new Order ($order_data['id_order']);
        // $order_lang = new Language((int)$order->id_lang);
        $delivery = new Address ($order_data['id_address_delivery']);
        $invoice = new Address ($order_data['id_address_invoice']);
        $carrier = $order->id_carrier ? new Carrier($order->id_carrier) : false;
        // $cart = new Cart ($order_data['id_cart']);

        $ProductDetailObject = new OrderDetail;
        $products = $ProductDetailObject->getList($order->id);
        $product_var_tpl_list = [];
        foreach ($products as $product)
        {
            // $product['product_attribute_id']; TODO get attribute name, maybe not even necessary

            $product_var_tpl = [
                'reference' => $product['product_reference'],
                'name' => $product['product_name'],// . (isset($product['attributes']) ? ' - ' . $product['attributes'] : ''),
                'unit_price' => self::formatMailPrice($product['unit_price_tax_incl']),
                'quantity' => $product['product_quantity'],
                'price' => self::formatMailPrice($product['total_price_tax_incl']),
                'customization' => [],
            ];

            $customized_datas = Product::getAllCustomizedDatas((int) $order->id_cart, null, true, null, (int) $product['id_customization']);
            if (isset($customized_datas[$product['product_id']][$product['product_attribute_id']])) {
                $product_var_tpl['customization'] = [];
                foreach ($customized_datas[$product['product_id']][$product['product_attribute_id']][$order->id_address_delivery] as $customization) {
                    $customization_text = '';
                    if (isset($customization['datas'][Product::CUSTOMIZE_TEXTFIELD])) {
                        foreach ($customization['datas'][Product::CUSTOMIZE_TEXTFIELD] as $text) {
                            $customization_text .= '<strong>' . $text['name'] . '</strong>: ' . $text['value'] . '<br />';
                        }
                    }
    
                    if (isset($customization['datas'][Product::CUSTOMIZE_FILE])) {
                        $customization_text .= $this->trans('%d image(s)', [count($customization['datas'][Product::CUSTOMIZE_FILE])], 'Admin.Payment.Notification') . '<br />';
                    }
    
                    $customization_quantity = (int) $customization['quantity'];
    
                    $product_var_tpl['customization'][] = [
                        'customization_text' => $customization_text,
                        'customization_quantity' => $customization_quantity,
                        'quantity' => self::formatMailPrice($customization_quantity * $product['unit_price_tax_incl']),
                    ];
                }
            }

            $product_var_tpl_list[] = $product_var_tpl;
        }

        $product_list_txt = $this->getEmailTemplateContent('order_conf_product_list.txt', Mail::TYPE_TEXT, $product_var_tpl_list);
        $product_list_html = $this->getEmailTemplateContent('order_conf_product_list.tpl', Mail::TYPE_HTML, $product_var_tpl_list);

        $cart_rules = $order->getCartRules();
        $cart_var_tpl_list = [];
        foreach ($cart_rules as $cart_rule)
        {
            $cart_var_tpl_list[] = [
                'voucher_name' => $cart_rule['name'],
                'voucher_reduction' => self::formatMailPrice($cart_rule['value']),
            ];
        }

        $cart_rules_list_txt = $this->getEmailTemplateContent('order_conf_cart_rules.txt', Mail::TYPE_TEXT, $cart_var_tpl_list);
        $cart_rules_list_html = $this->getEmailTemplateContent('order_conf_cart_rules.tpl', Mail::TYPE_HTML, $cart_var_tpl_list);

        $totals = [
            'total_products' => self::formatMailPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ? $order->total_products : $order->total_products_wt),
            'total_discountsraw' => $order->total_discounts_tax_incl,
            'total_discounts' => self::formatMailPrice($order->total_discounts_tax_incl),
            'total_wrappingraw' => $order->total_wrapping_tax_incl,
            'total_wrapping' => self::formatMailPrice($order->total_wrapping_tax_incl),
            'total_shipping' => self::formatMailPrice($order->total_shipping_tax_incl),
            'codfeeraw' => $order_data['cod'],
            'codfee' => self::formatMailPrice($order_data['cod']),
            'total_tax_paid' => self::formatMailPrice($order->total_paid_tax_incl - $order->total_paid_tax_excl), // TODO check if this calculation suffices
            'total_paid' => self::formatMailPrice($order->total_paid_tax_incl),
        ];
        $summary = $this->getEmailTemplateContent('summary_list.tpl', Mail::TYPE_HTML, $totals);
        $summary_txt = $this->getEmailTemplateContent('summary_list.txt', Mail::TYPE_TEXT, $totals);

        $base_uri = Context::getContext()->shop->getBaseURL(true);
        $order_url = $base_uri . "?controller=order-detail&id_order=" . $order->id;

        return [
            'order' => $order,
            'delivery' => $delivery,
            'invoice' => $invoice,
            'carrier' => $carrier,
            'product_list_txt' => $product_list_txt,
            'product_list_html' => $product_list_html,
            'cart_rules_list_txt' => $cart_rules_list_txt,
            'cart_rules_list_html' => $cart_rules_list_html,
            'summary' => $summary,
            'summary_txt' => $summary_txt,
            'tracking_number' => $order->getWsShippingNumber(),
            'order_url' => $order_url,
        ];
    }

    /**
     *
     * @param mixed $price
     *
     * @return float formatted price according to lang preferences
     */
    protected function formatMailPrice($price)
    {
        return Tools::getContextLocale($this->context)->formatPrice($price, $this->context->currency->iso_code);
    }

    /**
     * @return MailPartialTemplateRenderer
     */
    protected function getPartialRenderer()
    {
        if (!$this->partialRenderer) {
            $this->partialRenderer = new MailPartialTemplateRenderer($this->context->smarty);
        }

        return $this->partialRenderer;
    }

    protected function getAllOrderStates()
    {
        $order_states = new OrderState();
		$order_states = $order_states->getOrderStates(Context::getContext()->language->id);

        return $order_states;
    }

    /**
     * Fetch the content of $template_name inside the folder
     * current_theme/mails/current_iso_lang/ if found, otherwise in
     * mails/current_iso_lang.
     *
     * @param string $template_name template name with extension
     * @param int $mail_type Mail::TYPE_HTML or Mail::TYPE_TEXT
     * @param array $var sent to smarty as 'list'
     *
     * @return string
     */
    protected function getEmailTemplateContent($template_name, $mail_type, $var)
    {
        $email_configuration = Configuration::get('PS_MAIL_TYPE');
        if ($email_configuration != $mail_type && $email_configuration != Mail::TYPE_BOTH) {
            return '';
        }

        return $this->getPartialRenderer()->render($template_name, $this->context->language, $var);
    }

    /**
     * @param object Address $the_address that needs to be txt formated
     *
     * @return string the txt formated address block
     */
    protected function _getFormatedAddress(Address $the_address, $line_sep, $fields_style = [])
    {
        return AddressFormat::generateAddress($the_address, ['avoid' => []], $line_sep, ' ', $fields_style);
    }

    /**
	 * Using new translation system.
	 */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }
}