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
        add_action('init', 'register_shortcodes');
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

function register_shortcodes()
{
    add_shortcode('full-width-section', 'full_width_section');
}

function full_width_section($atts)
{
    $a = shortcode_atts(array(
        'page_id' => 0,
        'background_color' => '#ffffff',
    ), $atts);

    if ($a['page_id'] === 0) {
        return;
    }

    $content;
    $title;

    $query = new WP_Query([
        'p' => $a['page_id'],
        'post_type' => 'any',
    ]);

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            $title = $p->post_title;
            $content = apply_filters('the_content', $p->post_content);
        }
    }

    wp_reset_query();

    $html = '
      <section class="full-width-section" style="background-color: ' . $a['background_color'] . '">
        <div class="container">
          <h2 class="text-center">' . $title . '</h2>
          ' . $content . '
        </div>
      </section>';

    return $html;
}
