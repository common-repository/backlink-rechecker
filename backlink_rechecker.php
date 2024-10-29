<?php
/*
Plugin Name: Backlink Rechecker
Plugin URI: http://www.backlinkrechecker.com
Description: Rechecks your backlinks are live and have not been removed or lost - so you can then use this to BOOST the power of those backlinks with these recommended tools
Version: 1.3.1
Author: ExpertTeam.tv
Author URI: http://www.expertteam.tv
*/

/*  Copyright 2011 www.ExpertTeam.tv - http://www.expertteam.tv
*/



function blinkr_activation_plugin() {
    global $wpdb;

    add_option('blinkr_activated', '0' );

    // Create the new table for plugin.
    if($wpdb->get_var("show tables like 'blinkr_check_links'") != "blinkr_check_links") {

    $sql0 = "CREATE TABLE blinkr_check_links ( ";
    $sql0 .= " id int(11) NOT NULL auto_increment, ";
    $sql0 .= " email varchar(100) NOT NULL default '', ";
    $sql0 .= " search_links text NOT NULL default '', ";
    $sql0 .= " links text NOT NULL default '', ";
    $sql0 .= " parse int(1) NOT NULL default '0', ";
    $sql0 .= " processed int(11) NOT NULL default '0', ";
    $sql0 .= " formatted int(1) NOT NULL default '0', ";
    $sql0 .= " date datetime NOT NULL default '0000-00-00 00:00:00', ";
    $sql0 .= " date_of_change int(11) NOT NULL default '0', ";
    $sql0 .= " UNIQUE KEY id (id) ";
    $sql0 .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";

    $wpdb->query($sql0);
    }

    if($wpdb->get_var("show tables like 'blinkr_check_settings'") != "blinkr_check_settings") {

        $sql0 = "CREATE TABLE blinkr_check_settings ( ";
        $sql0 .= " ID int(11) NOT NULL auto_increment, ";
        $sql0 .= " name varchar(15) NOT NULL default '', ";
        $sql0 .= " value text NOT NULL default '', ";
        $sql0 .= " PRIMARY KEY (ID) ";
        $sql0 .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";

        $wpdb->query($sql0);

        $sql = "INSERT INTO blinkr_check_settings VALUES ('', ";
        $sql .= "'wpcron',";
        $sql .= "'1')";

        $wpdb->query($sql);

        $sql = "INSERT INTO blinkr_check_settings VALUES ('', ";
        $sql .= "'blinkr_email',";
        $sql .= "'')";

        $wpdb->query($sql);

    }


//    wp_schedule_single_event(time()+10, 'blinkr_cron_action');
    wp_schedule_event(time(), "min_2", "blinkr_cron_action");

}


// Hook for activation plugin
register_activation_hook (__FILE__, 'blinkr_activation_plugin');



// function for deactivation plugin
function blinkr_deactivation_plugin() {
    global $wpdb;

    //Delete table of  plugin.
    if($wpdb->get_var("show tables like 'blinkr_check_links'") == "blinkr_check_links") {

        $wpdb->query("DROP TABLE IF EXISTS blinkr_check_links");
    }

    //Delete table of  plugin.
    if($wpdb->get_var("show tables like 'blinkr_check_settings'") == "blinkr_check_settings") {

        $wpdb->query("DROP TABLE IF EXISTS blinkr_check_settings");
    }

    wp_clear_scheduled_hook("blinkr_cron_action");

}

// Hook for deactivation plugin
register_deactivation_hook (__FILE__, 'blinkr_deactivation_plugin');






// Hook for WP-CRON
add_action("blinkr_cron_action", "blinkr_cron");
//run CRON (not WP-CRON)
add_action("wp_ajax_nopriv_blinkr_cron", "blinkr_cron");
add_action("wp_ajax_blinkr_cron", "blinkr_cron");

// function for CRON
function blinkr_cron() {

    include('cron_check_links.php');
}


// function for set 10 min period for CRON
function blinkr_cron_2_min( $schedules ) {
    // add a 'min_2' schedule to the existing set
    $schedules['min_2'] = array(
        'interval' => 300,
        'display' => __('every 5 min')
    );
    return $schedules;
}

// filter schedules
add_filter( 'cron_schedules', 'blinkr_cron_2_min' );

// Hook for adding admin menus
add_action('admin_menu', 'blinkr_add_pages');

// action function for above hook
function blinkr_add_pages() {

    $blinkr_activated = get_option('blinkr_activated');
    if ( '2' == $blinkr_activated ) {
        // Add a new top-level menu:
        $blinkr_page = add_menu_page('Backlink Rechecker', 'Backlink Rechecker', 'administrator', 'backlink-rechecker', '', WP_PLUGIN_URL . '/backlink-rechecker/backlink_rechecker.png' );
        $blinkr_page = add_submenu_page( 'backlink-rechecker', 'Settings', 'Settings', 'manage_options', 'backlink-rechecker', 'blinkr_settings');
        $blinkr_page = add_submenu_page( 'backlink-rechecker', 'Rechecker', 'Rechecker', 'manage_options', 'rechecker', 'blinkr_rechecker');
        $blinkr_page = add_submenu_page( 'backlink-rechecker', 'Rechecker Plus', 'Rechecker Plus', 'manage_options', 'rechecker-plus', 'blinkr_rechecker_plus');

    } else {
        // Add a new top-level menu:
        $blinkr_page = add_menu_page('Backlink Rechecker', 'Backlink Rechecker', 'manage_options', 'backlink-rechecker', 'blinkr_settings');
    }

  wp_register_style('blinkr_Stylesheet', WP_PLUGIN_URL . '/backlink-rechecker/backlink_rechecker_style.css');

  wp_enqueue_style('blinkr_Stylesheet');


}

//
function blinkr_rechecker() {
    include('rechecker.php');

}
//
function blinkr_rechecker_plus() {


    include('rechecker_plus.php');

}
//
function blinkr_settings() {


    include('backlink_settings.php');

}


 add_action('plugins_loaded','backlink_plugins_loaded');


 function backlink_plugins_loaded() {

      if (isset($_GET['page']) &&
          $_GET['page']=='rechecker' &&
          isset($_POST['download'])  &&
          isset($_POST['file'])  &&
          $_POST['download']=='download') {
        header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=" . $_POST['file']);
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile(WP_PLUGIN_DIR ."/backlink-rechecker/files/" . $_POST['file']);
        exit();
      } elseif (isset($_GET['page']) &&
          $_GET['page']=='rechecker-plus' &&
          isset($_POST['download'])  &&
          isset($_POST['file'])  &&
          $_POST['download']=='download') {
        header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=" . $_POST['file']);
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile(WP_PLUGIN_DIR ."/backlink-rechecker/files/" . $_POST['file']);
        exit();
      }

}


//registration
add_action( 'admin_init', 'blinkr_registretion' );
//


function blinkr_set_html_content_type() {
    return 'text/html';
}

function blinkr_registretion() {

    $blinkr_activated = get_option( 'blinkr_activated' );

    if( isset( $_GET['action'] ) && '1' == $blinkr_activated ) {

        $activation_code = md5( get_home_url() . 'backlink-rechecker' );

        if( $activation_code == $_GET['action'] ) {

            update_option( 'blinkr_activated', '2' );

            blinkr_redirect( admin_url() . 'admin.php?page=backlink-rechecker' );

        } else {

            update_option( 'blinkr_activated', '0' );

        }
    }

}


function blinkr_redirect( $location ) {
    ?>
        <script type="text/javascript">
            window.location = '<?php echo $location; ?>';
        </script>
    <?php
    exit;
}



add_action("wp_ajax_nopriv_blinkr_s2", "blinkr_s2" );
add_action("wp_ajax_blinkr_s2", "blinkr_s2" );


function blinkr_s2() {

    if ( !empty( $_REQUEST['k'] ) && wp_verify_nonce( $_REQUEST['k'], get_current_user_id() . 'act' ) ) {

        if( !empty( $_REQUEST['email'] ) ) {

            $blinkr_activated = get_option( 'blinkr_activated' );

            if ( '0' == $blinkr_activated || '1' == $blinkr_activated ) {

                $message = "<p>Hi</p><p>IMPORTANT: Complete your Backlink Rechecker WordPress plugin registration by clicking the link below now.  You will then get full access to the plugin for FREE.  Click here now...</p>";
                $message .= '<p><a title="activate" href="' . admin_url() . 'admin.php?action=' . md5( get_home_url() . 'backlink-rechecker' ) . '">Activate Backlink Rechecker</a></p>';
                $message .= '<p>Click the link above...</p><p>Once that is clicked and activated, you will get full use of the plugin to recheck your backlinks and BOOST your Google position.</p>';
                $message .= "<p>Cheers,</p>";
                $message .= "<p>Duncan Elliott</p>";

                add_filter( 'wp_mail_content_type', 'blinkr_set_html_content_type' );

                wp_mail( $_REQUEST['email'], 'Backlink Rechecker Activation', $message );

                remove_filter( 'wp_mail_content_type', 'blinkr_set_html_content_type' );

                update_option( 'blinkr_activated', '1' );

                echo json_encode( array( 's' => true ) );
                exit;
            }

        }
    }

    echo json_encode( array( 's' => false ) );
    exit;
}
