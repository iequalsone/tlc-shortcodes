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

function register_shortcodes()
{
    add_shortcode('full-width-section', 'full_width_section');
    add_shortcode('featured-category', 'featured_category');
}

function full_width_section($atts)
{
    $a = shortcode_atts([
        'page_id' => 0,
        'background_color' => '#ffffff',
    ], $atts);

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

function featured_category($atts) {
  $a = shortcode_atts([
    'taxonomy' => '',
    'term' => '',
    'background-color' => '#ffffff',
  ], $atts);

  if(empty($a['taxonomy'] || empty($a['term']))) {
    return;
  }

  $output;
  $term = get_term_by('slug', $a['term'], $a['taxonomy']);
  $taxonomy = $a['taxonomy'];
  $slug = $a['term'];
  $name = $term->name;
  $desc = $term->description;
  $count = $term->count;

  if($count > 0){
    $query = new \WP_Query([
      'post_type' => 'services',
      'orderby' => 'menu_order',
      'order' => 'ASC',
      'tax_query' => [
        [
          'taxonomy' => $taxonomy,
          'field'    => 'slug',
          'terms'    => $slug,
        ],
      ],
    ]);

    if($query->have_posts()){
      $output .= '<div class="featured-category row card-scroll" style="background-color: '.$a['background-color'].';"><div class="container">';
      $output .= '<h2 class="cat-title text-center">'.$name.'</h2>';
      $output .= '
        <div class="row">
          <div class="col-md-8 offset-md-2"><p class="cat-description text-center">'.$desc.'</p></div>
        </div>';
      $output .= '<div class="d-flex justify-content-around">';

      foreach($query->posts as $p) {
        $title = $p->post_title;
        $excerpt = $p->post_excerpt;
        $featured_image = get_the_post_thumbnail_url($p->ID, 'wide-thumb');
        $permalink = get_permalink($p->ID); 
        // $thumbnail_override = get_field('thumbnail_override', $p->ID);

        if(!empty($thumbnail_override)) {
          $image = $thumbnail_override;
        } else {
          $image = !empty($featured_image) ? $featured_image : "";
        }

        $output .= '
          <div class="card shadow rounded-0 col-12 col-sm-6 col-md-4">
            <img src="'.$image.'" class="card-img-top rounded-0" alt="'.$title.'">
            <div class="card-body">
              <h5 class="card-title">'.$title.'</h5>
              <p class="card-text">'.$excerpt.'</p>
              <a href="'.$permalink.'" class="btn btn-primary">Learn more</a>
            </div>
          </div>';
      }
      $output .= '</div></div></div>';
    }

    wp_reset_query();

    return $output;
  } else {
    return;
  }
}

$TLC_Shortcodes = new TLC_Shortcodes();
