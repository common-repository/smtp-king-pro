<?php
/*
    Plugin Name: SMTP King Pro
    Plugin URI: http://kingpro.me/plugins/smtp-king-pro/
    Description: Overrides the standard wp_mail function and used SwiftMail via an SMTP connection to send you site mail
    Version: 1.0.2
    Author: Ash Durham
    Author URI: http://durham.net.au/
    License: GPL2

    Copyright 2013  Ash Durham  (email : plugins@kingpro.me)

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

    // INSTALL

    global $smtpkp_db_version;
    $smtpkp_db_version = "1.0.2";

    function smtpkp_install() {
       global $wpdb;
       global $smtpkp_db_version;

       require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
       
       add_option("smtpkp_db_version", $smtpkp_db_version);
    }
    
    // Register hooks at activation
    register_activation_hook(__FILE__,'smtpkp_install');
    
    // END INSTALL
    
    if (get_option("smtpkp_db_version") != $smtpkp_db_version) {
        // Execute your upgrade logic here
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Then update the version value
        update_option("smtpkp_db_version", $smtpkp_db_version);
    }
    
    function smtpkp_settings_link($action_links,$plugin_file){
            if($plugin_file==plugin_basename(__FILE__)){
                    $smtpkp_settings_link = '<a href="admin.php?page=' . str_replace('-', '', dirname(plugin_basename(__FILE__))) . '">' . __("Settings") . '</a>';
                    array_unshift($action_links,$smtpkp_settings_link);
            }
            return $action_links;
    }
    add_filter('plugin_action_links','smtpkp_settings_link',10,2);
    
    function smtpkp_plugin_load_first()
    {
        $path = str_replace( WP_PLUGIN_DIR . '/', '', __FILE__ );
        if ( $plugins = get_option( 'active_plugins' ) ) {
            if ( $key = array_search( $path, $plugins ) ) {
                array_splice( $plugins, $key, 1 );
                array_unshift( $plugins, $path );
                update_option( 'active_plugins', $plugins );
            }
        }
    }
    add_action( 'activated_plugin', 'smtpkp_plugin_load_first' );

    
    require_once plugin_dir_path(__FILE__).'includes/admin_area.php';
    
?>