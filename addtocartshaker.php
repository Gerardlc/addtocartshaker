<?php
/**
* 2007-2021 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Addtocartshaker extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'addtocartshaker';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Gerard Luque';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('"Add to Cart" Shaker');
        $this->description = $this->l('Prestashop module to animate the "Add to Cart" button');

        $this->confirmUninstall = $this->l('Are you sure?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('ADDTOCARTSHAKER_EFFECT', 'bounce');
        Configuration::updateValue('ADDTOCARTSHAKER_VISUAL_SETTINGS', 'hover');


        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('ADDTOCARTSHAKER_EFFECT');
        Configuration::deleteByName('ADDTOCARTSHAKER_VISUAL_SETTINGS');
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitAddtocartshakerModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

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
        $helper->submit_action = 'submitAddtocartshakerModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of form.
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
                    [
                        'type' => 'select',
                        'label' => $this->l('Animation Type'),
                        'desc' => $this->l('Select the animation type'),
                        'name' => 'ADDTOCARTSHAKER_EFFECT',
                        'required' => true,
                        'default_value' => 'bounce',
                        'options' => array(
                            'query' => array(
                                array('id' => 'bounce', 'name' => 'bounce'),
                                array('id' => 'flash', 'name' => 'flash'),
                                array('id' => 'pulse', 'name' => 'pulse'),
                                array('id' => 'rubberBand', 'name' => 'rubberBand'),
                                array('id' => 'shakeX', 'name' => 'shakeX'),
                                array('id' => 'shakeY', 'name' => 'shakeY'),
                                array('id' => 'headShake', 'name' => 'headShake'),
                                array('id' => 'swing', 'name' => 'swing'),
                                array('id' => 'tada', 'name' => 'tada'),
                                array('id' => 'wobble', 'name' => 'wobble'),
                                array('id' => 'jello', 'name' => 'jello'),
                                array('id' => 'heartBeat', 'name' => 'heartBeat'),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ],
                    [
                        'type' => 'radio',
                        'label' => $this->l('Visual settings'),
                        'name' => 'ADDTOCARTSHAKER_VISUAL_SETTINGS',
                        'default_value' => 'hover',
                        'required' => true,
                        'values' => [
                            [
                                'id' => 'visual_settings',
                                'value' => 'hover',
                                'label' => $this->l('On Hover'),
                            ],
                            [
                                'id' => 'visual_settings',
                                'value' => 'hover-auto',
                                'label' => $this->l('On Hover and every 10 seconds'),
                            ],
                            [
                                'id' => 'visual_settings',
                                'value' => 'auto',
                                'label' => $this->l('Animate every 10 seconds'),
                            ],
                        ],
                    ],
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
            'ADDTOCARTSHAKER_EFFECT' => Configuration::get('ADDTOCARTSHAKER_EFFECT', null),
            'ADDTOCARTSHAKER_VISUAL_SETTINGS' => Configuration::get('ADDTOCARTSHAKER_VISUAL_SETTINGS', null),

        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookBackOfficeHeader()
    {

        if (Tools::getValue('configure') == $this->name) {
            Media::addJsDef([
                'effect' => Configuration::get('ADDTOCARTSHAKER_EFFECT'),
                'visual_settings' => Configuration::get('ADDTOCARTSHAKER_VISUAL_SETTINGS'),
                'seconds' => 3000
            ]);
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'node_modules/animate.css/animate.css');
        }
    }

    public function hookHeader()
    {
        if ($this->context->controller instanceof ProductControllerCore) {
            Media::addJsDef([
                'effect' => Configuration::get('ADDTOCARTSHAKER_EFFECT'),
                'visual_settings' => Configuration::get('ADDTOCARTSHAKER_VISUAL_SETTINGS'),
                'seconds' => 10000
            ]);
            $this->context->controller->addJS($this->_path . '/views/js/front.js');
            $this->context->controller->addCSS($this->_path . 'node_modules/animate.css/animate.css');
        }
    }
}
