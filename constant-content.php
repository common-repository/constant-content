<?php

/*
  Plugin Name: Constant Content
  Plugin URI: http://www.constant-content.com
  Description: Connect your constant-content.com account with your WordPress dashboard to access purchased content, hire writers and more.
  Author: Constant-Content
  Version: 1.0.15

  Copyright 2016 Support (support@constant-content.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if (!defined('CONSTANTCONTENT_DIR')) {
    define('CONSTANTCONTENT_DIR', dirname(__FILE__));
}
if (!defined('CONSTANTCONTENT_ASSET_DIR')) {
    define('CONSTANTCONTENT_ASSET_DIR', plugins_url('/assets/', __FILE__));
}
if (!defined('CONSTANTCONTENT_INCLUDE_DIR')) {
    define('CONSTANTCONTENT_INCLUDE_DIR', dirname(__FILE__) . '/include/');
}
if (!defined('CONSTANTCONTENT_ADDRESS')) {
    define('CONSTANTCONTENT_ADDRESS', 'https://www.constant-content.com/api/');
}

require_once(CONSTANTCONTENT_INCLUDE_DIR . 'dataParser.php');

class ConstantContent {
    /* --------------------------------------------*
     * Constants
     * -------------------------------------------- */

    const name = 'Constant Content';
    const slug = 'constant-content';
    const version = '1.0.15';

    /**
     * Constructor
     */
    function __construct() {
        add_action('init', array($this, 'init_constant_content'));
        register_activation_hook(__FILE__, array('ConstantContent', 'activate'));
        register_deactivation_hook(__FILE__, array('ConstantContent', 'deactivate'));
        register_uninstall_hook(__FILE__, array('ConstantContent', 'uninstall'));
    }

    /**
     * Runs when the plugin is initialized
     */
    function init_constant_content() {
        if (is_admin()) {
            // Load JavaScript and stylesheets
            $this->register_scripts_and_styles();

            add_action('admin_menu', array('ConstantContent', 'admin_menu'));
            add_Action('wp_ajax_' . self::slug . '-ajax-data', array('ConstantContent', 'AjaxData'));
            add_Action('wp_ajax_' . self::slug . '-docdetails', array('ConstantContent', 'docdetails'));
            add_Action('wp_ajax_' . self::slug . '-requestedit', array('ConstantContent', 'requestedit'));
            add_Action('wp_ajax_' . self::slug . '-requestnew', array('ConstantContent', 'requestnew'));
            add_Action('wp_ajax_' . self::slug . '-signup', array('ConstantContent', 'signup'));
            add_Action('wp_ajax_' . self::slug . '-revision-edit', array('ConstantContent', 'revisionedit'));

            if (!empty($_GET['page']) && strpos($_GET['page'], self::slug) !== false && $_GET['page'] !== self::slug . '-settings') {
                $valid_key = ConstantContent::get('valid_key');
                if ($valid_key === true) {
                    self::cacheItems();
                } else {
                    ob_clean();
                    ob_start();
                }
            }
        }
    }

    public function activate() {
        $account_email = ConstantContent::get('account_email');
        $account_key = ConstantContent::get('account_key');
        if (!empty($account_email) && !empty($account_key)) {
            ConstantContent::validateKey($account_email, $account_key);
        } else {
            ConstantContent::save('valid_key', false);
        }
        ConstantContent::clearCache();
    }

    public function deactivate() {
        ConstantContent::clearCache();
        ConstantContent::save('valid_key', false);
    }

    public function uninstall() {
        ConstantContent::clearCache();
        ConstantContent::save('account_email', null);
        ConstantContent::save('account_key', null);
        ConstantContent::save('valid_key', false);
    }

    /**
     * Registers and enqueues stylesheets for the administration panel and the
     * public facing site.
     */
    private function register_scripts_and_styles() {
        if (!empty($_GET['page']) && strpos($_GET['page'], self::slug) === false) {
            return false;
        }
        if (is_admin()) {
            wp_enqueue_script('datatables', CONSTANTCONTENT_ASSET_DIR . 'js/jquery.dataTables.min.js', array(
                'jquery'
            ));
            wp_enqueue_script('dataTables.fnReloadAjax', CONSTANTCONTENT_ASSET_DIR . 'js/jquery.dataTables.fnReloadAjax.js', array(
                'datatables'
            ));
            wp_enqueue_script('jquery-timepicker', CONSTANTCONTENT_ASSET_DIR . 'js/jquery-ui-timepicker-addon.js', array(
                'jquery-ui-datepicker',
                'jquery-ui-sliderAccess'
            ));
            wp_enqueue_script('jquery-ui-sliderAccess', CONSTANTCONTENT_ASSET_DIR . 'js/jquery-ui-sliderAccess.js', array(
                'jquery-ui-slider',
                'jquery-ui-core',
                'jquery-ui-button',
                'jquery-ui-widget',
                'jquery-ui-mouse',
                'jquery-ui-dialog',
                'jquery'
            ));
            wp_enqueue_script('bootstrap', CONSTANTCONTENT_ASSET_DIR . 'js/bootstrap.min.js');
            wp_enqueue_script('jquery-validate', CONSTANTCONTENT_ASSET_DIR . 'js/jquery.validate.min.js', array(
                'jquery'
            ));
            wp_enqueue_script('select2', CONSTANTCONTENT_ASSET_DIR . 'js/select2.full.min.js', array(
                'jquery',
                'lodash'
            ));
            wp_enqueue_script('lodash', CONSTANTCONTENT_ASSET_DIR . 'js/lodash.min.js');
            wp_enqueue_script(self::slug . '-admin-script', CONSTANTCONTENT_ASSET_DIR . 'js/admin.js');

            wp_enqueue_style('datatables', CONSTANTCONTENT_ASSET_DIR . 'css/jquery.dataTables.min.css');
            wp_enqueue_style('bootstrap', CONSTANTCONTENT_ASSET_DIR . 'css/bootstrap.min.css');
            wp_enqueue_style('cc-timepicker', CONSTANTCONTENT_ASSET_DIR . 'css/jquery-ui.css');
            wp_enqueue_style('select2', CONSTANTCONTENT_ASSET_DIR . 'css/select2.min.css');
            wp_enqueue_style(self::slug . '-admin-style', CONSTANTCONTENT_ASSET_DIR . 'css/admin.css');
        }
    }

    /**
     * Puts a new menu item under Settings.
     */
    public static function admin_menu() {
        add_menu_page(
                __('Constant Content Settings', self::slug), __('Constant Content', self::slug), 'manage_options', self::slug . '-dashboard', array('ConstantContent', 'dashboard'), CONSTANTCONTENT_ASSET_DIR . 'images/cc-logo-small-16.png'
        );
        add_submenu_page(
                self::slug . '-dashboard', __('Constant Content Dashboard', self::slug), __('Dashboard', self::slug), 'manage_options', self::slug . '-dashboard', array('ConstantContent', 'dashboard')
        );
        add_submenu_page(
                self::slug . '-dashboard', __('Constant Content Catalog', self::slug), __('Catalog', self::slug), 'manage_options', self::slug . '-catalog', array('ConstantContent', 'catalog')
        );
        add_submenu_page(
                self::slug . '-dashboard', __('Constant Content Content', self::slug), __('My Content', self::slug), 'manage_options', self::slug . '-content', array('ConstantContent', 'content')
        );
        add_submenu_page(
                self::slug . '-dashboard', __('Constant Content Notifications', self::slug), __('Notifications', self::slug), 'manage_options', self::slug . '-notifications', array('ConstantContent', 'notifications')
        );
        add_submenu_page(
                self::slug . '-dashboard', __('Constant Content Orders', self::slug), __('My Orders', self::slug), 'manage_options', self::slug . '-orders', array('ConstantContent', 'orders')
        );
        add_submenu_page(
                self::slug . '-dashboard', __('Constant Content Requests', self::slug), __('My Requests', self::slug), 'manage_options', self::slug . '-requests', array('ConstantContent', 'requests')
        );
        add_submenu_page(
                self::slug . '-requests', __('Constant Content Archived Requests', self::slug), __('Archived Requests', self::slug), 'manage_options', self::slug . '-archived-requests', array('ConstantContent', 'archivedrequests')
        );
        add_submenu_page(
                self::slug . '-dashboard', __('Constant Content Revisions', self::slug), __('My Revisions', self::slug), 'manage_options', self::slug . '-revisions', array('ConstantContent', 'revisions')
        );
        add_submenu_page(
                self::slug . '-revisions', __('Constant Content Revison', self::slug), __('Revision Editor', self::slug), 'manage_options', self::slug . '-revision-edit', array('ConstantContent', 'revisionedit')
        );
        add_submenu_page(
                self::slug . '-dashboard', __('Constant Content Settings', self::slug), __('Settings', self::slug), 'manage_options', self::slug . '-settings', array('ConstantContent', 'settings')
        );
        add_submenu_page(
                null, __('Constant Content Document Details', self::slug), __('Settings', self::slug), 'manage_options', self::slug . '-docdetails', array('ConstantContent', 'docdetails')
        );
        add_submenu_page(
                null, __('Constant Content New Request', self::slug), __('Settings', self::slug), 'manage_options', self::slug . '-requestnew', array('ConstantContent', 'requestnew')
        );
        add_submenu_page(
                null, __('Constant Content Update Request Details', self::slug), __('Settings', self::slug), 'manage_options', self::slug . '-requestedit', array('ConstantContent', 'requestedit')
        );
        add_submenu_page(
                null, __('Constant Content Signup', self::slug), __('Settings', self::slug), 'manage_options', self::slug . '-signup', array('ConstantContent', 'signup')
        );
        add_submenu_page(
                null, __('Constant Content Error', self::slug), __('Settings', self::slug), 'manage_options', self::slug . '-error', array('ConstantContent', 'error')
        );
    }

    public static function dashboard() {
        global $constantURL;
        $constantURL = self::slug . '-dashboard';
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'dashboard.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function notifications() {
        global $constantURL;
        $constantURL = self::slug . '-notifications';
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'notifications.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function catalog() {
        global $constantURL;
        $constantURL = self::slug . '-catalog';
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'catalog.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function ajaxData() {
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'ajaxData.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function archivedrequests() {
        global $constantURL, $archived;
        $constantURL = self::slug . '-archived-requests';
        $archived = true;
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'requests.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function requests() {
        global $constantURL, $archived;
        $constantURL = self::slug . '-requests';
        $archived = false;
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'requests.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function revisions() {
        global $constantURL;
        $constantURL = self::slug . '-revisions';
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'revisions.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function orders() {
        global $constantURL;
        $constantURL = self::slug . '-orders';
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'orders.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function content() {
        global $constantURL;
        $constantURL = self::slug . '-content';
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'content.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function error() {
        global $constantURL;
        $constantURL = self::slug . '-error';
        require_once(CONSTANTCONTENT_INCLUDE_DIR . 'error.php');
    }

    public static function settings() {
        global $constantURL;
        $constantURL = self::slug . '-settings';
        require_once(CONSTANTCONTENT_INCLUDE_DIR . 'settings.php');
    }

    public static function docdetails() {
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'docdetails.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function revisionedit() {
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'revisionedit.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function requestnew() {
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'requestnew.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function requestedit() {
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key === true) {
            require_once(CONSTANTCONTENT_INCLUDE_DIR . 'requestedit.php');
        } else {
            wp_redirect(admin_url('admin.php?page=constant-content-settings'));
            exit;
        }
    }

    public static function signup() {
        require_once(CONSTANTCONTENT_INCLUDE_DIR . 'signup.php');
    }

    public static function versionCheck() {
        $result = self::accessAPI('index', 'get', array(), 'version/' . self::version . '/wordpress/' . get_bloginfo('version') . '/');
        return $result;
    }

    public static function validateKey($account_email, $account_key) {
        global $connectFailMessage;
        if (empty($account_email) || !is_email($account_email)) {
            return false;
        }
        if (empty($account_key)) {
            return false;
        }
        $doUpdate = false;

        $current_email = ConstantContent::get('account_email');
        $current_key = ConstantContent::get('account_key');
        ConstantContent::save('account_email', $account_email);
        ConstantContent::save('account_key', $account_key);

        try {
            $result = self::versionCheck();
            if (empty($result) || strpos($result, '<response>') !== 0) {
                $connectFailMessage .= 'Unable to connect to ' . CONSTANTCONTENT_ADDRESS . ' please check your wordpress settings.';
            } else {
                $doUpdate = self::checkXMLItem($result, 'success', 'true');
                if (!$doUpdate) {
                    $connectFailMessage .= 'Invalid Account Email and Site Key combination.';
                }
            }
        } catch (Exception $e) {
            $doUpdate = false;
        }

        self::clearCache();
        if ($doUpdate) {
            ConstantContent::save('valid_key', true);
            self::cacheItems();
        } else {
            ConstantContent::save('account_key', $current_key);
            ConstantContent::save('account_email', $current_email);
        }
        return $doUpdate;
    }

    public static function checkXMLItem($xml, $nodeName, $value) {
        if (strpos($xml, '<response>') !== 0) {
            return false;
        }
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        return ConstantContent::checkElementValue($doc, $nodeName, $value);
    }

    public static function checkElementValue($element, $nodeName, $value) {
        if (!is_a($element, 'DOMElement') && !is_a($element, 'DOMDocument')) {
            return false;
        }
        $found = ConstantContent::findFirstNode($element, $nodeName);
        if (!is_a($found, 'DOMElement')) {
            return false;
        }
        if (strtolower($found->nodeValue) === strtolower($value)) {
            return true;
        }
        return false;
    }

    public static function accessAPI($api, $method = 'get', $post = array(), $url_extras = '') {
        global $connectFailMessage;
        $account_email = ConstantContent::get('account_email');
        $account_key = ConstantContent::get('account_key');

        if ($method === 'get') {
            if (empty($url_extras)) {
                $cachedCopy = ConstantContent::cacheAPIGet($api);
                if (!empty($cachedCopy)) {
                    return $cachedCopy;
                }
            }
        } else {
            ConstantContent::cacheAPIClean($api);
        }
        $toReturn = '<response><success>FALSE</success></response>';
        if (self::_is_curl_installed()) {
            $cookies = Constantcontent::get('cookies');
            $ch = curl_init(CONSTANTCONTENT_ADDRESS . $api . '/' . $url_extras);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            if (!empty($post)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
            }
            if (!empty($cookies)) {
                curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'customer_email: ' . $account_email,
                'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/48.0',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Content-type: application/x-www-form-urlencoded',
                'api_key: ' . $account_key,
            ));
            $result = curl_exec($ch);
            list($header, $toReturn) = explode("\r\n\r\n", $result, 2);
            $headerParts = explode("\n", $header);
            foreach ($headerParts as $part) {
                if (strpos($part, 'Set-Cookie:') !== false) {
                    list($cookie, $details) = explode(';', trim(str_replace('Set-Cookie:', '', $part)));
                    list($cookieKey, $cookieValue) = explode("=", $cookie);
                    $cookies[$cookieKey] = $cookie;
                }
            }
            if (!empty($cookies)) {
                ConstantContent::save('cookies', $cookies);
            }
        } elseif (self::_is_https_installed()) {
            $postdata = http_build_query($post);
            $opts = array('http' =>
                array(
                    'method' => strtoupper($method),
                    'header' => "Content-type: application/x-www-form-urlencoded"
                    . "\r\ncustomer_email: " . $account_email . ""
                    . "\r\napi_key: " . $account_key . ""
                    . "\r\nConnection: close"
                    . "\r\n",
                    'content' => $postdata
                )
            );

            $context = stream_context_create($opts);

            $toReturn = trim(file_get_contents(CONSTANTCONTENT_ADDRESS . $api . '/' . $url_extras, false, $context));
        }
        $doc = new DOMDocument();
        $doc->loadXML($toReturn);
        $result = ConstantContent::checkElementValue($doc, 'success', 'true');
        if ($result) {
            if ($method === 'get' && empty($url_extras)) {
                ConstantContent::cacheAPISave($api, $toReturn);
            }
        } else {
            $invalidLogin = ConstantContent::checkElementValue($doc, 'message', 'Authentication failed.');
            if ($invalidLogin && $_REQUEST['page'] !== 'constant-content-settings') {
                ConstantContent::save('valid_key', false);
                wp_redirect(admin_url('admin.php?page=constant-content-settings'));
                exit;
            }
        }
        return $toReturn;
    }

    public static function cacheAPIGet($callName) {
        $cache = ConstantContent::get('api_cache');
        if (empty($cache[$callName])) {
            return false;
        }
        $dateCache = $cache[$callName]['time'];
        $dateNow = new DateTime();
        $dateCache->modify('+30 minutes');
        if ($dateCache < $dateNow) {
            return false;
        }
        return $cache[$callName]['item'];
    }

    public static function cacheAPIClean($callName = '') {
        $cache = ConstantContent::get('api_cache');
        if (empty($cache)) {
            return false;
        }
        if (!empty($callName) && !empty($cache[$callName])) {
            unset($cache[$callName]);
        }
        $dateCache = new DateTime();

        $dateCache->modify('+30 minutes');

        foreach ($cache as $cacheName => $cacheItem) {
            if (!empty($cacheItem)) {
                if ($cacheItem['time'] > $dateCache) {
                    unset($cache[$cacheName]);
                }
            }
        }
        ConstantContent::save('api_cache', $cache);
        return true;
    }

    public static function cacheAPISave($callName, $item) {
        ConstantContent::cacheAPIClean();
        $dateNow = new DateTime();
        $cache = ConstantContent::get('api_cache');
        $cache[$callName]['time'] = $dateNow;
        $cache[$callName]['item'] = $item;
        ConstantContent::save('api_cache', $cache);
    }

    public static function cacheItems() {
        $valid_key = ConstantContent::get('valid_key');
        if ($valid_key !== true) {
            return false;
        }
        $cache_time = ConstantContent::get('cache_time');
        $dateNow = new DateTime();
        if (!empty($cache_time)) {
            if (!is_a($cache_time, 'DateTime')) {
                $cache_time = new DateTime($cache_time);
            }
            $cache_time->modify('+3 day');
            if ($cache_time > $dateNow) {
                return false;
            }
        }
        ConstantContent::save('cache_time', $dateNow);

        $categoryList = self::accessAPI('list', 'get', array(), 'categories');
        $xml = new DOMDocument();
        $xml->loadXML($categoryList);
        $categories = array();
        foreach ($xml->getElementsByTagName('category') as $catXML) {
            $id = $catXML->getAttribute('id');
            $name = $catXML->getAttribute('name');
            $request_code = $catXML->getAttribute('request_code');
            $categories[$id]['id'] = $id;
            $categories[$id]['name'] = $name;
            $categories[$id]['code'] = $request_code;
            foreach ($catXML->getElementsByTagName('subcategory') as $catChildXML) {
                $childId = $catChildXML->getAttribute('id');
                $childName = $catChildXML->getAttribute('name');
                $childRequest_code = $catChildXML->getAttribute('request_code');
                $categories[$id]['subcat'][$childId]['id'] = $childId;
                $categories[$id]['subcat'][$childId]['name'] = $childName;
                $categories[$id]['subcat'][$childId]['code'] = $childRequest_code;
            }
        }
        ConstantContent::save('category_list', $categories);

        $countryList = self::accessAPI('list', 'get', array(), 'countries');
        $xml = new DOMDocument();
        $xml->loadXML($countryList);
        $countries = array();
        foreach ($xml->getElementsByTagName('country') as $catXML) {
            $id = $catXML->getAttribute('id');
            $name = $catXML->getAttribute('name');
            $request_code = $catXML->getAttribute('request_code');
            $countries[$id]['id'] = $id;
            $countries[$id]['name'] = $name;
            $countries[$id]['code'] = $request_code;
        }
        ConstantContent::save('country_list', $countries);

        $educationList = self::accessAPI('list', 'get', array(), 'education');
        $xml = new DOMDocument();
        $xml->loadXML($educationList);
        $educations = array();
        foreach ($xml->getElementsByTagName('education') as $catXML) {
            $id = $catXML->getAttribute('id');
            $name = $catXML->getAttribute('name');
            $request_code = $catXML->getAttribute('request_code');
            $educations[$id]['id'] = $id;
            $educations[$id]['name'] = $name;
            $educations[$id]['code'] = $request_code;
        }
        ConstantContent::save('education_list', $educations);

        $certificationList = self::accessAPI('list', 'get', array(), 'certification');
        $xml = new DOMDocument();
        $xml->loadXML($certificationList);
        $certifications = array();
        foreach ($xml->getElementsByTagName('certification') as $catXML) {
            $id = $catXML->getAttribute('id');
            $name = $catXML->getAttribute('name');
            $request_code = $catXML->getAttribute('request_code');
            $certifications[$id]['id'] = $id;
            $certifications[$id]['name'] = $name;
            $certifications[$id]['code'] = $request_code;
        }
        ConstantContent::save('certification_list', $certifications);

        $expertList = self::accessAPI('list', 'get', array(), 'expert');
        $xml = new DOMDocument();
        $xml->loadXML($expertList);
        $experts = array();
        foreach ($xml->getElementsByTagName('expert') as $catXML) {
            $id = $catXML->getAttribute('id');
            $name = $catXML->getAttribute('name');
            $request_code = $catXML->getAttribute('request_code');
            $experts[$id]['id'] = $id;
            $experts[$id]['name'] = $name;
            $experts[$id]['code'] = $request_code;
        }
        ConstantContent::save('expert_list', $experts);

        $studyList = self::accessAPI('list', 'get', array(), 'study');
        $xml = new DOMDocument();
        $xml->loadXML($studyList);
        $studies = array();
        foreach ($xml->getElementsByTagName('study') as $catXML) {
            $id = $catXML->getAttribute('id');
            $name = $catXML->getAttribute('name');
            $request_code = $catXML->getAttribute('request_code');
            $studies[$id]['id'] = $id;
            $studies[$id]['name'] = $name;
            $studies[$id]['code'] = $request_code;
        }
        ConstantContent::save('study_list', $studies);

        $teamList = self::accessAPI('list', 'get', array(), 'team');
        $xml = new DOMDocument();
        $xml->loadXML($teamList);
        $teams = array();
        foreach ($xml->getElementsByTagName('team') as $catXML) {
            $id = $catXML->getAttribute('id');
            $name = $catXML->getAttribute('name');
            $request_code = $catXML->getAttribute('request_code');
            $teams[$id]['id'] = $id;
            $teams[$id]['name'] = $name;
            $teams[$id]['code'] = $request_code;
        }
        ConstantContent::save('teams_list', $teams);

        $writerList = self::accessAPI('list', 'get', array(), 'writers');
        $xml = new DOMDocument();
        $xml->loadXML($writerList);
        $writers = array();
        foreach ($xml->getElementsByTagName('writer') as $catXML) {
            $id = $catXML->getAttribute('id');
            $penname = $catXML->getAttribute('penname');
            $documents = $catXML->getAttribute('documents');
            $writers[$id]['id'] = $id;
            $writers[$id]['penname'] = $penname;
            $writers[$id]['documents'] = $documents;
        }
        ConstantContent::save('writers_list', $writers);

        $contentList = self::accessAPI('list', 'get', array(), 'content');
        $xml = new DOMDocument();
        $xml->loadXML($contentList);
        $content_types = array();
        foreach ($xml->getElementsByTagName('content') as $catXML) {
            $id = $catXML->getAttribute('id');
            $name = $catXML->getAttribute('name');
            $content_types[$id] = $name;
        }
        ConstantContent::save('content_type_list', $content_types);

        $priceList = self::accessAPI('list', 'get', array(), 'creditprices');
        $xml = new DOMDocument();
        $xml->loadXML($priceList);
        $prices = array();
        foreach ($xml->getElementsByTagName('price') as $catXML) {
            $value = $catXML->getAttribute('value');
            $prices[$value] = $value;
        }
        ConstantContent::save('price_list', $prices);

        $requestPriceList = self::accessAPI('list', 'get', array(), 'requestprices');
        $xml = new DOMDocument();
        $xml->loadXML($requestPriceList);
        $requestPrices = array();
        foreach ($xml->getElementsByTagName('price') as $catXML) {
            $value = $catXML->getAttribute('value');
            $requestPrices[$value] = $value;
        }
        ConstantContent::save('request_price_list', $requestPrices);
    }

    public static function get($item) {
        if (empty($item)) {
            return false;
        }
        return maybe_unserialize(get_option(ConstantContent::slug . '_' . $item));
    }

    public static function save($item, $value) {
        if (empty($item)) {
            return false;
        }
        if (empty($value)) {
            return delete_option(ConstantContent::slug . '_' . $item);
        }
        return update_option(ConstantContent::slug . '_' . $item, serialize($value));
    }

    public static function getTextNode($xmlElement, $nodeName, $maxDepth = 2) {
        if (!is_a($xmlElement, 'DOMElement') && !is_a($xmlElement, 'DOMDocument')) {
            return '';
        }
        $baseDepth = substr_count($xmlElement->getNodePath(), '/');

        $toReturn = array();

        $elements = $xmlElement->getElementsByTagName($nodeName);
        foreach ($elements as $element) {
            $elementDepth = substr_count($element->getNodePath(), '/');
            $showDepth = $elementDepth - $baseDepth;
            if ($showDepth <= $maxDepth) {
                $toReturn[] = $element->nodeValue;
            }
        }
        return implode("\n<br/>", $toReturn);
    }

    public static function findFirstNode($xmlElement, $nodeName) {
        if (empty($xmlElement)) {
            return null;
        }
        $elements = $xmlElement->getElementsByTagName($nodeName);
        foreach ($elements as $element) {
            return $element;
        }
        return null;
    }

    public static function create($docID, $title, $body, $type = 'post', $status = 'draft', $redirect = true) {
        if (empty($title) || empty($body) || empty($docID)) {
            return false;
        }

        $publicationDates = ConstantContent::get('publishedDocs');

        $postDetails = array(
            'comment_status' => 'open', //[ 'closed' | 'open' ] // 'closed' means no comments.
            'ping_status' => 'open', //[ 'closed' |  ] // 'closed' means pingbacks or trackbacks turned off
            'post_status' => $status, //'future', //[ 'draft' | 'publish' | 'pending'| 'future' | 'private' ] //Set the status of the new post.
            'post_type' => $type, //[ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] //You may want to insert a regular post, page, link, a menu item or some custom post type
            'post_title' => $title, //[ <the title> ] //The title of your post.
            'post_content' => $body, // [ <the text of the post> ] //The full text of the post.
        );

        kses_remove_filters();
        $postID = wp_insert_post($postDetails);
        $toReturn = false;
        if ($postID > 0) {
            $publicationDates[$docID]['lastPublished'] = time();
            $publicationStatus = get_post_statuses();
            $publicationDates[$docID]['type'] = ucwords($type) . ' ' . ucwords($publicationStatus[$status]);
            $publicationDates[$docID]['post_id'][$postID] = $postID;
            ConstantContent::save('publishedDocs', $publicationDates);
            if ($redirect) {
                if (function_exists('admin_url')) {
                    $toReturn = admin_url('post.php?post=' . $postID . '&action=edit');
                } else {
                    $toReturn = get_option('siteurl') . '/wp-admin/post.php?post=' . $postID . '&action=edit';
                }
            } else {
                $toReturn = true;
            }
        }
        return $toReturn;
    }

    public static function addToOrder($documentID, $license) {
        if (!in_array($license, array('use', 'unique', 'fullrights', 'revision'))) {
            return false;
        }
        $orderXML = ConstantContent::accessAPI('order', 'get', array(), 'state/unpaid');
        $doc = new DOMDocument();
        $doc->loadXML($orderXML);
        $orders = ConstantContent::findFirstNode($doc, 'orders');
        $count = $orders->getAttribute('count');
        $purchaseArray = array($license => $documentID);
        if ($count == 0) {
            $toReturn = ConstantContent::accessAPI('order', 'post', $purchaseArray);
        } else {
            $orderItem = ConstantContent::findFirstNode($orders, 'order');
            $orderID = ConstantContent::getTextNode($orderItem, 'order_id');
            $purchaseArray['id'] = $orderID;
            $toReturn = ConstantContent::accessAPI('order', 'post', $purchaseArray);
        }
        return $toReturn;
    }

    public static function _is_curl_installed() {
        return extension_loaded('openssl');
    }

    public static function _is_https_installed() {
        $w = stream_get_wrappers();
        return in_array('https', $w);
    }

    public static function getMinPrice($prices, $minprice) {
        $toReturn = 100;
        foreach ($prices as $price) {
            if ($minprice <= $price) {
                $toReturn = $price;
                break;
            }
        }
        return $toReturn;
    }

    public static function formatJS($string) {
        return str_replace("\n", " ' + \n    '", str_replace("'", "\'", $string));
    }

    public static function clearCache() {
        ConstantContent::save('content_type_list', null);
        ConstantContent::save('study_list', null);
        ConstantContent::save('expert_list', null);
        ConstantContent::save('certification_list', null);
        ConstantContent::save('education_list', null);
        ConstantContent::save('request_price_list', null);
        ConstantContent::save('teams_list', null);
        ConstantContent::save('price_list', null);
        ConstantContent::save('writers_list', null);
        ConstantContent::save('country_list', null);
        ConstantContent::save('category_list', null);
        ConstantContent::save('cache_time', null);
        ConstantContent::save('api_cache', null);
    }

}

new ConstantContent();
