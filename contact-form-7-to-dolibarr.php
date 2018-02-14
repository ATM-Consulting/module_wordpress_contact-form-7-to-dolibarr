<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */



/*
 Plugin Name: Contact Form 7 - Dolibarr sync
 Plugin URI: https://github.com/ATM-Consulting/module_wordpress_contact-form-7-to-dolibarr
 Description: Addon for WordPress Contact Form
 Author: Jean-François Ferry <jfefe@aternatik.fr>
 Version: 1.0
 */

define( 'WPCF7_DOLIBARR_VERSION', '4.3.1' );


define( 'WPCF7_DOLIBARR_PLUGIN', __FILE__ );

define( 'WPCF7_DOLIBARR_PLUGIN_BASENAME', plugin_basename( WPCF7_DOLIBARR_PLUGIN ) );

define( 'WPCF7_DOLIBARR_PLUGIN_NAME', trim( dirname( WPCF7_DOLIBARR_PLUGIN_BASENAME ), '/' ) );

define( 'WPCF7_DOLIBARR_PLUGIN_DIR', untrailingslashit( dirname( WPCF7_DOLIBARR_PLUGIN ) ) );


require_once WPCF7_DOLIBARR_PLUGIN_DIR . '/lib/dolibarr_sync.php';
require_once WPCF7_DOLIBARR_PLUGIN_DIR . '/lib/restclient.php';


class Wpcf7_to_Dolibarr_Plugin  {
    
    /**
     * Constructor: setup callbacks and plugin-specific options
     *
     * @author James Inman
     */
    public function __construct() {
        
        // Set the plugin's Clockwork SMS menu to load the contact forms
        //$this->plugin_callback = array( $this, 'wpcf7' );
        $this->plugin_dir = basename( dirname( __FILE__ ) );
        
        // Setup options for each Contact Form 7 form
        //add_action( 'wpcf7_admin_after_form', array( &$this, 'setup_form_options' ) );
        add_action( 'wpcf7_after_save', array( &$this, 'save_form' ) );
        add_action( "wpcf7_before_send_mail", array(&$this, "sync_into_dolibarr") );
        add_filter( 'wpcf7_editor_panels' , array(&$this, 'new_panel'));
        
        add_action( 'admin_init', array( &$this, 'setup_admin_init' ) );
        add_action( 'admin_menu', array( &$this, 'setup_admin_navigation' ) );
        
    }
    
    /**
     * Register global Clockwork settings for API keys
     *
     * @return void
     * @author James Inman
     */
    public function setup_admin_init() {
        register_setting( 'wpcf7_dolibarr_options', 'wpcf7_dolibarr_options', array( $this, 'dolibarr_options_validate' ) );
       
        add_settings_section('dolibarr_api_params', 'API parameters', array( $this, 'settings_default_text'), 'wpcf7_dolibarr');
        add_settings_field( 'dolibarr_api_key', 'Your API Key', array( $this, 'settings_api_key_input' ), 'wpcf7_dolibarr', 'dolibarr_api_params' );        
        add_settings_field( 'dolibarr_api_url', "API URL", array( $this, 'settings_api_url_input' ), 'wpcf7_dolibarr', 'dolibarr_api_params' );
            
        add_settings_section('event_params', 'Event parameters', array( $this, 'settings_event_text'), 'wpcf7_dolibarr');
        add_settings_field( 'userownerid', "User owner ID", array( $this, 'settings_userownerid' ), 'wpcf7_dolibarr', 'event_params' );
        add_settings_field( 'action_code', "Action code", array( $this, 'settings_action_code' ), 'wpcf7_dolibarr', 'event_params' );
        
        
    }
    
    /**
     * Introductory text for the API keys part of the form
     *
     * @return void
     * @author James Inman
     */
    public function settings_api_key_text() {
        echo '<p>You need an API key to use the WPCF7 - Dolibarr sync plugin.</p>';
    }
    
    /**
     * Introductory text for the default part of the form
     *
     * @return void
     * @author James Inman
     */
    public function settings_default_text() {
        echo '<p>Default settings apply to WPCF7 - Dolibarr sync plugin.</p>';
    }
    
    /**
     * Input box for the API key
     *
     * @return void
     * @author James Inman
     */
    public function settings_api_key_input() {
        $options = get_option( 'wpcf7_dolibarr_options' );
        
        if( isset( $options['api_key'] ) ) {
            echo "<input id='dolibarr_api_key' name='wpcf7_dolibarr_options[api_key]' size='40' type='text' value='".$options['api_key']."' />";
        } else {
            echo "<input id='dolibarr_api_key' name='wpcf7_dolibarr_options[api_key]' size='40' type='text' value='' />";
        }
    }
    
    /**
     * Input box for the API URL
     *
     * @return void
     * @author James Inman
     */
    public function settings_api_url_input() {
        $options = get_option( 'wpcf7_dolibarr_options' );
        if( isset( $options['api_url'] ) ) {
            echo "<input id='dolibarr_api_url' name='wpcf7_dolibarr_options[api_url]' size='40' type='text' value='{$options['api_url']}' />";
        } else {
            echo "<input id='dolibarr_api_url' name='wpcf7_dolibarr_options[api_url]' size='40' type='text' value='' />";
        }
        echo "<p>Enter default Dolibarr API URL.</p>";
    }
    
    /**
     * Introductory text for the event settings
     *
     * @return void
     * @author jfefe
     */
    public function settings_event_text() {
        echo '<p>Specify user ID for event creation.</p>';
    }
     
    
    /**
     * Input box for the owner ID of event
     *
     * @return void
     * @author James Inman
     */
    public function settings_userownerid() {
        $options = get_option( 'wpcf7_dolibarr_options' );
        if( isset( $options['userownerid'] ) ) {
            echo "<input id='userownerid' name='wpcf7_dolibarr_options[userownerid]' size='40' type='text' value='{$options['userownerid']}' />";
        } else {
            echo "<input id='userownerid' name='wpcf7_dolibarr_options[userownerid]' size='40' type='text' value='' />";
        }
        echo "<p>Enter ID of the user for event creation.</p>";
    }
    
    /**
     * Input box for the action code
     *
     * @return void
     * @author James Inman
     */
    public function settings_action_code() {
        $options = get_option( 'wpcf7_dolibarr_options' );
        if( isset( $options['action_code'] ) ) {
            echo "<input id='action_code' name='wpcf7_dolibarr_options[action_code]' size='40' type='text' value='{$options['action_code']}' />";
        } else {
            echo "<input id='action_code' name='wpcf7_dolibarr_options[action_code]' size='40' type='text' value='' />";
        }
        echo "<p>Enter the action code for event creation.</p>";
    }
    
    /**
     * Validation for the API key
     *
     * @return void
     * @author James Inman
     */
    public function dolibarr_options_validate( $val ) {
        
        
        // API key checking
        try {
            $key = trim( $val['api_key'] );
            if( $key ) {
                add_settings_error( 'wpcf7_dolibarr_options', 'wpcf7_dolibarr_options', 'Your settings were saved! You can now start using WPCF7 - Dolibarr plugin.', 'updated' );
                return $val;
            }
        } catch( Exception $ex ) {
            add_settings_error( 'wpcf7_dolibarr_options', 'wpcf7_dolibarr_options', 'Your API key was incorrect. Please enter it again.', 'error' );
            return false;
        }
        
        return $val;
    }
    
    /**
     * Render the main Dolibarr options page
     *
     * @return void
     * @author James Inman
     */
    public function wpcf7_dolibarr_options() {
        include( WP_PLUGIN_DIR . '/' . $this->plugin_dir . '/templates/dolibarr-options.php');
        //$this->render_template( 'dolibarr-options' );
    }
    
    
    /**
     * Show tab on each contacct form
     * 
     * @param unknown $panels
     * @return unknown
     */
    public function new_panel ($panels) {
        $panels['dolibarr-panel'] = array(
            'title' => 'Dolibarr sync',
            'callback' => array(&$this, 'display_panel')
        );
        return $panels;
    }
    
    public function display_panel($form) {
        if ( wpcf7_admin_has_edit_cap() ) {
            $options = get_option( 'wpcf7_dolibarr_' . (method_exists($form, 'id') ? $form->id() : $form->id) );
            $optionsDefault = get_option( 'wpcf7_dolibarr_options' );
            if( empty( $options ) || !is_array( $options ) ) {
                $options = array( 
                    'api_url' => '',
                    'api_key' => '',
                    'category_id' => '',
                    'field_company' => '',
                    'field_email' => '',
                    'field_lastname' => '',
                    'field_firstname' => '',
                );
            }
            if( is_array($options)) {
                if ( empty($options['api_url']) ) {
                    $options['api_url'] = $optionsDefault['api_url'];
                }
                if ( empty($options['api_key']) ) {
                    $options['api_key'] = $optionsDefault['api_key'];
                }
            }
            $options['form'] = $form;
            $data = $options;
            include( WP_PLUGIN_DIR . '/' . $this->plugin_dir . '/templates/form-options.php');
        }
    }
    
    
    
    /**
     * Setup the admin navigation
     *
     * @return void
     * @author James Inman / jfefe
     */
    public function setup_admin_navigation() {
        global $menu;
        
        $menu_exists = false;
        foreach( $menu as $k => $item ) {
            if( $item[0] == "WPCF7 - Dolibarr" ) {
                $menu_exists = true;
                break;
            }
        }
        
        // Setup global Clockwork options
        if( !$menu_exists ) {
            add_menu_page( __( 'WPCF - Dolibarr', $this->language_string ), __( 'WPCF7 - Dolibarr', $this->language_string ), 'manage_options', 'wpcf7_dolibarr_options', array( $this, 'wpcf7_dolibarr_options' ), plugins_url( 'images/logo_16px_16px.png', dirname( __FILE__ ) ) );
            add_submenu_page( 'wpcf7_dolibarr_options', __( 'WPCF7 - Dolibarr Options', $this->language_string ), __( 'WPCF7 - Dolibarr Options', $this->language_string ), 'manage_options', 'wpcf7_dolibarr_options', array( $this, 'wpcf7_dolibarr_options' ) );
            //add_submenu_page( NULL, 'Test', 'Test', 'manage_options', 'dolibarr_test_message', array( $this, 'dolibarr_test_message' ) );
        }
        
        // Setup options for this plugin
        add_submenu_page( 'dolibarr_options', __( $this->plugin_name, $this->language_string ), __( $this->plugin_name, $this->language_string ), 'manage_options', $this->plugin_callback[1], $this->plugin_callback );
    }
    
    /**
     * Setup HTML for the admin <head>
     *
     * @return void
     * @author James Inman
     */
    public function setup_admin_head() {
        //echo '<link rel="stylesheet" type="text/css" href="' . plugins_url( 'css/dolibarr.css', __FILE__ ) . '">';
    }
    
    /**
     * Empty function to provide a callback for the main plugin action page
     *
     * @return void
     * @author James Inman
     */
    public function wpcf7() {}
    
    
    /**
     * Save WPCF7 - Dolibarr options when contact form is saved
     *
     * @param object $cf Contact form
     * @return void
     * @author jfefe
     */
    public function save_form( $form ) {
        update_option( 'wpcf7_dolibarr_' . (method_exists($form, 'id') ? $form->id() : $form->id), $_POST['wpcf7-dolibarr'] );
    }
    
    /**
     * Save form datas through Dolibarr API
     * 
     * @param unknown $form
     * @return unknown
     */
    public function sync_into_dolibarr ($form) {
        $companyId = 0;
        $options = array_merge( get_option( 'wpcf7_dolibarr_options' ), get_option( 'wpcf7_dolibarr_' . (method_exists($form, 'id') ? $form->id() : $form->id) ) );
        $properties = $form->get_properties();
       
        // Replace tags
        $options['field_company'] = wpcf7_mail_replace_tags($options['field_company'], array());
        $options['field_firstname'] =  wpcf7_mail_replace_tags($options['field_firstname'], array());
        $options['field_lastname'] = wpcf7_mail_replace_tags($options['field_lastname'], array());
        $options['field_email'] = wpcf7_mail_replace_tags($options['field_email'], array());        
        $options['subject'] = wpcf7_mail_replace_tags($properties['mail']['subject'], array());
        $options['message'] = wpcf7_mail_replace_tags($properties['mail']['body'], array());
        
        $dolibarrSync = new Wpcf7_dolibarr_sync($options);
        
        $searchCompany = $dolibarrSync->searchCompany($options['field_email']);
        
        // Company not found
        if ($searchCompany === 0) {
            // Create company
            $companyId = $dolibarrSync->saveCompany();
            
            // Save company into category
            $dolibarrSync->saveCompanyCategory($options['category_id']);
            
            // Save contact
            $contactId = $dolibarrSync->saveContact();
       
        }
        // Company found
        if ($searchCompany > 0) {
            $companyId = $searchCompany;
            $dolibarrSync->setCompanyId($companyId);
        }
        
        if($companyId > 0) {  
            
            $dolibarrSync->saveMessage();
        }
        
        return $form;
    }
}

$cp = new Wpcf7_to_Dolibarr_Plugin();


