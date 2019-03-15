<?php
/*
Plugin Name: TLC Shortcodes
Plugin URI:
Description: Custom shortcodes to be used with thelotuscentre.ca's WordPress theme built by Jonathan Howard
Author: Jonathan Howard
Author URI:
 */

/**
 * @classname TLC_Shortcodes
 * @author remi (exomel.com)
 * @version 20100119
 */
class TLC_Shortcodes
{

    /**
     * @constructor
     */
    public function TLC_Shortcodes()
    {
        $this->set_hooks();
    }

    /**
     * Set actions and filters
     * @return void
     */
    public function set_hooks()
    {
        add_action('init', __NAMESPACE__ . '\\register_shortcodes');
    }

    public function register_shortcodes()
    {
        add_shortcode('full-width-section', __NAMESPACE__ . '\\full_width_section');
    }

    public function full_width_section($atts)
    {
        // $a = shortcode_atts( array(
        //     'flag' => false
        // ), $atts );

        $html = 'Test';

        return $html;
    }

    /**
     * A sample action
     * @return WP_Query
     */
    // function parse_request( $wp ) {
    //     return $wp;
    // }

}

$TLC_Shortcodes = new TLC_Shortcodes();
