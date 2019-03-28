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
  add_shortcode('featured-events', 'featured_events');
  add_shortcode('featured-more-information', 'featured_more_information');
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
            <div class="row cards-wrap">';
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
                  <div class="col-12 col-md-4">
                    <div class="card shadow rounded-0">
                      '.(!empty($image) ? '<img src="'.$image.'" class="card-img-top rounded-0" alt="'.$title.'">' : '' ).'
                      <div class="card-body">
                        <h5 class="card-title">'.$title.'</h5>
                        <p class="card-text">'.$excerpt.'</p>
                        <a href="'.$permalink.'" class="btn btn-primary btn-block">Learn more</a>
                      </div>
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
    'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
    'order' => 'ASC',
    'show-archive-link' => false,
    'title' => '',
    'card-deck' => false,
    'taxonomy' => '',
    'taxonomy-term' => '',
    'background-color' => '#ffffff',
  ], $atts);

  $pto = get_post_type_object($a['post-type']);

  $output; $body_html; $header_html; $tax_query = [];

  $title = (!empty($a['title']) ? $a['title'] : $pto->label);

  if($a['show-archive-link']) {
    $pto_archive = get_post_type_archive_link($a['post-type']);
    $header_html = '<div class="d-flex mb-4 align-items-center archive-link">
                      <div class="p-0"><h2>'.$title.'</h2></div>
                      <div class="ml-auto p-0">
                        <a class="btn btn-pink-light btn-block pl-4 pr-4" href="'.$pto_archive.'">Learn more</a>
                      </div>
                    </div>';
  } else {
    $header_html = '<h2 class="section-title text-center">'.$title.'</h2>';
  }

  if(!empty($a['taxonomy']) && !empty($a['taxonomy-term'])) {
    $tax_query = [
      [
        'taxonomy' => $a['taxonomy'],
        'field'    => 'slug',
        'terms'    => $a['taxonomy-term'],
      ],
    ];
  }

  $query = new WP_Query([
    'post_type' => $a['post-type'],
    'orderby' => $a['orderby'],
    'order' => $a['order'],
    'tax_query' => $tax_query
  ]);

  if($query->have_posts()){
    if($a['card-deck']) {
      $body_html = '<div class="card-deck card-deck-slider">';
      foreach($query->posts as $p) {
        $title = $p->post_title;
        $excerpt = substr($p->post_excerpt, 0, 155);
        $permalink = get_permalink($p->ID);
        $featured_image;

        // Determine what image to use
        if(get_the_post_thumbnail_url($p->ID)) {
          $featured_image = get_the_post_thumbnail_url($p->ID, 'wide-thumb');
        } elseif (wp_get_attachment_image_url(206)) {
          $featured_image = wp_get_attachment_image_url(206, 'wide-thumb');
        }

        $body_html .= '<div class="slide text-center">
                        <div class="card">
                          <a href="'.$permalink.'">
                            '.(!empty($featured_image) ? '<img class="card-img-top" src="'.$featured_image.'" alt="'.$title.'" />' : "").'
                            <div class="card-body">
                              <h5 class="card-title">'.$title.'</h5>
                              '.(!empty($excerpt) ? '<p class="card-text">'.$excerpt.'</p>' : '').'
                            </div>
                          </a>
                        </div>
                      </div>';
      }
      $body_html .= "</div>";
    } else {
      $body_html = '<div class="simple-slider">';
      foreach($query->posts as $p) {
        $featured_image = get_the_post_thumbnail_url($p->ID, 'affiliate-thumb');
        $title = $p->post_title;
        if(!empty($featured_image)){
          $body_html .= '<div class="slide text-center">
                      <img class="img-fluid" src="'.$featured_image.'" alt="'.$title.'" />
                      <p>'.$title.'</p>
                    </div>';
        }
      }
      $body_html .= "</div>";
    }
  }

  wp_reset_query();

  $output .= '
      <section class="featured-post-type" style="background-color: '.$a['background-color'].';">
        <div class="container">
          '.$header_html.'
          '.$body_html.'
        </div>
      </section>';

  return $output;
}

function featured_events($atts) {
  $a = shortcode_atts([
    'show-archive-link' => false,
    'title' => '',
    'background-color' => '#ffffff',
  ], $atts);

  $pto = get_post_type_object($a['post-type']);

  $output; $body_html; $header_html; $tax_query = [];

  $title = (!empty($a['title']) ? $a['title'] : $pto->label);

  if($a['show-archive-link']) {
    $pto_archive = get_post_type_archive_link($a['post-type']);
    $header_html = '<div class="d-flex mb-4 align-items-center archive-link">
                      <div class="p-0"><h2>'.$title.'</h2></div>
                      <div class="ml-auto p-0">
                        <a class="btn btn-pink-light btn-block pl-4 pr-4" href="'.$pto_archive.'">Learn more</a>
                      </div>
                    </div>';
  } else {
    $header_html = '<h2 class="section-title text-center">'.$title.'</h2>';
  }

  $query = new WP_Query([
    'post_type' => 'events'
  ]);

  if($query->have_posts()){
    $body_html = '<div class="card-deck card-deck-slider">';
    foreach($query->posts as $p) {
      $title = $p->post_title;
      $excerpt = substr($p->post_excerpt, 0, 155);
      $permalink = get_permalink($p->ID);

      $event_type = get_field('event_type', $p->ID);
      $dates = get_field('dates', $p->ID);
      $registration_cost = get_field('registration_cost', $p->ID);
      $services_provided = get_field('services_provided', $p->ID);

      // Determine what image to show
      if(!empty(get_the_post_thumbnail_url($p->ID))) {
        // If the event has a featured image, use that
        $featured_image = get_the_post_thumbnail_url($p->ID, 'wide-thumb');
      } elseif(get_the_post_thumbnail_url($event_type[0]->ID)) {
        // else, if the Event Type has a featured image, use that
        $featured_image = get_the_post_thumbnail_url($event_type[0]->ID, 'wide-thumb');
      } else {
        // Otherwise, use the Lotus Flower (media id 206)
        // I know! It's a hard coded id! I get it!
        $featured_image = wp_get_attachment_image_url(206, 'wide-thumb');
      }

      $body_html .= '<div class="slide text-center">
                      <div class="card">
                        <a href="'.$permalink.'">
                        '.(!empty($featured_image) 
                          ? '<img class="card-img-top" src="'.$featured_image.'" alt="'.$title.'" />' 
                          : "" ).'
                          <div class="card-body">
                            <h4 class="card-title">'.$title.'</h4>
                            '.(!empty($dates) ? '<p class="card-subtitle mb-2">'.$dates.'</p>' : '').'
                            '.(!empty($registration_cost) ? '<p class="h2">$'.$registration_cost.'</p>' : '').'
                            <p>Register Now &gt;</p>
                          </div>
                        </a>
                      </div>
                    </div>';
    }
    $body_html .= "</div>";
  }

  wp_reset_query();

  $output .= '
      <section class="featured-events" style="background-color: '.$a['background-color'].';">
        <div class="container">
          '.$header_html.'
          '.$body_html.'
        </div>
      </section>';

  return $output;
}

function featured_more_information($atts) {
  $a = shortcode_atts([
    'title' => '',
    'email' => '',
    'phone' => '',
    'facebook' => '',
    'youtube' => '',
    'background-color' => '#ffffff',
  ], $atts);

  $output; $body_html;

  $output .= '
      <section class="featured-more-information" style="background-color: '.$a['background-color'].';">
        <div class="container">
          <div class="row align-items-center text-center">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0"><h2 class="title">'.$a['title'].'</h2></div>
            <div class="col-12 col-lg-3 mb-2 mb-lg-0">
              <i class="fas fa-envelope"></i> <a class="link" href="mailto:'.$a['email'].'">'.$a['email'].'</a>
            </div>
            <div class="col-12 col-lg-3 mb-4 mb-lg-0">
              <i class="fas fa-phone"></i> <a class="link" href="tel:'.$a['phone'].'">'.$a['phone'].'</a>
            </div>
            <div class="col-12 col-lg-2 mb-lg-0">
              <a class="social-link" href="'.$a['facebook'].'">
                <span class="fa-stack fa-1x">
                  <i class="fas fa-circle fa-stack-2x"></i>
                  <i class="fab fa-facebook-f fa-stack-1x fa-inverse"></i>
                </span>
              </a>
              <a class="social-link" href="'.$a['youtube'].'">
                <span class="fa-stack fa-1x">
                  <i class="fas fa-circle fa-stack-2x"></i>
                  <i class="fab fa-youtube fa-stack-1x fa-inverse"></i>
                </span>
              </a>
            </div>
          </div>
        </div>
      </section>';

  return $output;
}

$TLC_Shortcodes = new TLC_Shortcodes();
