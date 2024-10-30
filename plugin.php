<?php
/*
Plugin Name: Buggyman.io for Wordpress
Plugin URI: http://buggyman.io/
Description: Integrates Wordpress and Buggyman.io
Version: 1.3.1
Author: Buggyman.io
Author URI: http://buggyman.io
*/


add_action('init', 'buggyman_init');


function buggyman_init()
{
    $phpToken = get_option('buggyman_php_token');
    $jsToken = get_option('buggyman_js_token');
    if ($phpToken && $jsToken) {
        require_once("Buggyman.php");
        $buggyman = new Buggyman();
        $buggyman->setErrorLevel((int)get_option('buggyman_error'));
        $buggyman->setToken($phpToken);
        $buggyman->init();

        if (!$_SERVER['HTTPS']) {
            wp_enqueue_script( 'buggyman-js', 'http://cdn.buggyman.io/v1/js/' . $jsToken . '/collector.js', array(), '1.0.0', false );
        }


    } else {
        add_action('admin_head', 'buggyman_not_configured');
    }
}

function buggyman_not_configured()
{
    echo "<div class='updated'><p>Buggyman.io not configured. Please, <a href='/wp-admin/admin.php?page=buggyman'>configure it right now</a></p></div>";
}


add_action('admin_menu', 'buggyman_menu');

function buggyman_menu()
{
    add_menu_page("Buggyman", "Buggyman", "manage_options", "buggyman", "buggyman_admin_options_page");
}

function buggyman_admin_options_page()
{

    ?>
    <div class="wrap">
        <h2>Buggyman.io &mdash; <a href="http://buggyman.io/?utm_source=buggyman_plugin">get an API key for free</a></h2>
        <form method="post" action="options.php">
            <?php settings_fields('buggyman'); ?>
            <?php do_settings_sections('buggyman'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}


add_action('admin_init', 'buggyman_admin_init');

function buggyman_admin_init()
{
    register_setting('buggyman', 'buggyman_php_token');
    register_setting('buggyman', 'buggyman_js_token');
    register_setting('buggyman', 'buggyman_error');
    add_settings_section('buggyman', 'Settings', '', 'buggyman');
    add_settings_field('buggyman_php_token', 'Buggyman.io PHP token', 'buggyman_admin_field_token_php', 'buggyman', 'buggyman');
    add_settings_field('buggyman_php_js', 'Buggyman.io JS token', 'buggyman_admin_field_token_js', 'buggyman', 'buggyman');
    add_settings_field('buggyman_error', 'PHP error level', 'buggyman_admin_field_error', 'buggyman', 'buggyman');
}

function buggyman_admin_field_token_php()
{
    echo "<input size='50' type='text' name='buggyman_php_token' value='" . esc_attr(get_option('buggyman_php_token')) . "' />";
}

function buggyman_admin_field_token_js()
{
    echo "<input size='50' type='text' name='buggyman_js_token' value='" . esc_attr(get_option('buggyman_js_token')) . "' />";
}

function buggyman_admin_field_error()
{

    $variants = array(
        'E_ALL',
        'E_ALL ^ E_DEPRECATED',
        'E_ALL ^ E_NOTICE',
        'E_ERROR & E_WARNING',
        'E_ERROR & E_WARNING & E_NOTICE',
    );

    echo "<select name='buggyman_error'>";
    foreach ($variants as $variant) {
        echo "<option" . (get_option('buggyman_error') == $variant ? " selected" : "") . ">{$variant}</option>";
    }
    echo "</select>";
}