<?php
/*
Plugin Name: LinkGist - Affiliate Marketing Tool
Plugin URI: https://www.linkgist.com/
Description: Linkgist helps you easily monetize content by converting product links in your post into their equivalent affiliate links on-the-fly.
Author: LinkGist
Author URI: https://www.linkgist.com/
Version: 1.0
License: GPLv2 or later
Text Domain: linkgist

Copyright 2015 LinkGist B.V. (https://www.linkgist.com/)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/* -----------------------------------------------------------------------------
:: Register the Class Auto Loader
----------------------------------------------------------------------------- */

spl_autoload_register('LinkGist::autoload');


/* -----------------------------------------------------------------------------
:: Boot up the Plugin
----------------------------------------------------------------------------- */

/**
 * Init Singleton Class.
 *
 * @author LinkGist B.V.
 */
class LinkGist
{
    /** Hold plugin instance */
    private static $instance = false;

    /** Setup plugin constants */
    const MIN_PHP_VERSION     = '5.2.4';
    const MIN_WP_VERSION      = '3.8';
    const TEXT_DOMAIN         = 'linkgist';
    const OPTION_KEY          = 'linkgist';
    const FILE                = __FILE__;

    /**
     * Singleton class
     *
     * @return LinkGist
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Create a new LinkGist instance.
     *
     * @return void
     */
    private function __construct()
    {
        if (!$this->testHost()) {
            return;
        }

        add_action('init', array($this, 'textDomain'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

        // Ignite Classes!
        new LinkGist_Admin;
        new LinkGist_MetaBox;
        new LinkGist_Frontend;
    }

    /**
     * PSR-0 compliant autoloader to load classes as needed.
     *
     * @param  string  $classname  The name of the class
     * @return null    Return early if the class name does not start with the
     *                 correct prefix
     */
    public static function autoload($className)
    {
        if (__CLASS__ !== mb_substr($className, 0, strlen(__CLASS__))) {
            return;
        }
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
            $fileName .= DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, 'src_'.$className);
        $fileName .='.php';

        require $fileName;
    }

    /**
     * Loads the plugin text domain for translation
     */
    public function textDomain()
    {
        $domain = self::TEXT_DOMAIN;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
        load_textdomain(
            $domain,
            WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo'
        );
        load_plugin_textdomain(
            $domain,
            false,
            dirname(plugin_basename(__FILE__)).'/lang/'
        );
    }

    /**
     * Fired when the plugin is uninstalled.
     */
    public function uninstall()
    {
        delete_option(self::OPTION_KEY);
    }


    /* -------------------------------------------------------------------------
    :: Environment Checks
    ------------------------------------------------------------------------- */

    /**
     * Checks PHP and WordPress versions.
     */
    private function testHost()
    {
        // Check if PHP is too old
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            // Display notice
            add_action('admin_notices', array(&$this, 'phpVersionError'));
            return false;
        }

        // Check if WordPress is too old
        global $wp_version;
        if (version_compare($wp_version, self::MIN_WP_VERSION, '<')) {
            add_action('admin_notices', array(&$this, 'wpVersionError'));
            return false;
        }
        return true;
    }

    /**
     * Displays a warning when installed on an old PHP version.
     */
    public function phpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %3$s requires PHP version %1$s or greater.<br/>'.
            'Your installed PHP version: %2$s',
            self::MIN_PHP_VERSION,
            PHP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }

    /**
     * Displays a warning when installed in an old Wordpress version.
     */
    public function wpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %2$s requires WordPress version %1$s or greater.',
            self::MIN_WP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }

    /**
     * Get the name of this plugin.
     *
     * @return string The plugin name.
     */
    private function getPluginName()
    {
        $data = get_plugin_data(self::FILE);
        return $data['Name'];
    }
}

add_action('plugins_loaded', array('LinkGist', 'getInstance'));
