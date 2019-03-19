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
  add_shortcode('featured-page', 'featured_page');
  add_shortcode('featured-category', 'featured_category');
  add_shortcode('featured-post-type', 'featured_post_type');
}

function featured_page($atts)
{
    $a = shortcode_atts([
        'page-id' => 0,
        'text-align' => 'left',
        'background-color' => '#ffffff',
    ], $atts);

    if ($a['page-id'] === 0) {
        return;
    }

    $content; $title;

    $query = new WP_Query([
        'p' => $a['page-id'],
        'post_type' => 'page',
    ]);

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            $title = $p->post_title;
            $content = apply_filters('the_content', $p->post_content);
            $featured_image = get_the_post_thumbnail_url($p->ID, 'large');
        }
    }

    wp_reset_query();

    if($featured_image) {
      if($a['text-align'] == "left"){
        $columns = '<div class="order-1 order-md-2 col-12 col-md-6 image-wrap">
                      <img class="img-fluid" src="'.$featured_image.'" alt="' . $title . '" />
                    </div>
                    <div class="order-2 order-md-1 col-12 col-md-6 content-wrap">
                      <h2 class="page-title">' . $title . '</h2>
                      ' . $content . '
                    </div>';
      } else {
        $columns = '<div class="order-2 order-md-2 col-12 col-md-6 content-wrap">
                      <h2 class="page-title">' . $title . '</h2>
                      ' . $content . '
                    </div>
                    <div class="order-1 order-md-1 col-12 col-md-6 image-wrap">
                      <img class="img-fluid" src="'.$featured_image.'" alt="' . $title . '" />
                    </div>';
      }
    } else {
      $columns = '<div class="col-12 col-md-8 offset-md-2">
                    <h2 class="page-title">' . $title . '</h2>
                    ' . $content . '
                  </div>';
    }

    $output = '
      <section class="featured-page" style="background-color: ' . $a['background_color'] . '">
        <div class="container">
          <div class="row align-items-center">
            '.$columns.'
          </div>
        </div>
      </section>';

    return $output;
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
    $query = new WP_Query([
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
      $output .= '
        <section class="featured-section" style="background-color: '.$a['background-color'].';">
          <div class="container">
            <h2 class="section-title text-center">'.$name.'</h2>
            <div class="section-description row">
              <div class="col-md-8 offset-md-2"><p class="text-center">'.$desc.'</p></div>
            </div>
            <div class="card-deck">';
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
                  <div class="card shadow rounded-0">
                    '.(!empty($image) ? '<img src="'.$image.'" class="card-img-top rounded-0" alt="'.$title.'">' : '' ).'
                    <div class="card-body">
                      <h5 class="card-title">'.$title.'</h5>
                      <p class="card-text">'.$excerpt.'</p>
                      <a href="'.$permalink.'" class="btn btn-primary btn-block">Learn more</a>
                    </div>
                  </div>';
              }
    $output .= '
            </div>
          </div>
        </section>';
    }

    wp_reset_query();

    return $output;
  } else {
    return;
  }
}

function featured_post_type($atts) {
  $a = shortcode_atts([
    'post-type' => 'post',
    'orderby' => 'menu_order',
    'order' => 'ASC',
    'show-archive-link' => false,
    'show-excerpt' => false,
    'card-deck' => false,
    'background-color' => '#ffffff',
  ], $atts);

  $pto = get_post_type_object($a['post-type']);

  $output;

  $query = new WP_Query([
    'post_type' => $a['post-type'],
    'orderby' => $a['orderby'],
    'order' => $a['order'],
  ]);

  if($query->have_posts()){
    $html = '<div class="row">';
    foreach($query->posts as $p) {
      $featured_image = get_the_post_thumbnail_url($p->ID, 'thumbnail');
      $title = $p->post_title;
      if(!empty($featured_image)){
        $html .= '<div class="col-12 col-sm-6 col-md-2 text-center">
                    <img class="img-fluid" src="'.$featured_image.'" alt="'.$title.'" />
                    <p>'.$title.'</p>
                  </div>';
      }
    }
    $html .= "</div>";
  }

  wp_reset_query();

  $output .= '
      <section class="featured-post-type" style="background-color: '.$a['background-color'].';">
        <div class="container">
          <h2 class="section-title text-center">'.$pto->label.'</h2>
          '.$html.'
        </div>
      </section>';

  return $output;
}

$TLC_Shortcodes = new TLC_Shortcodes();
