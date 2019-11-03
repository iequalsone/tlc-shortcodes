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
	add_shortcode('featured-post-type', 'featured_post_type');
	add_shortcode('featured-section', 'featured_section');
	add_shortcode('featured-content', 'featured_content');
	add_shortcode('featured-events', 'featured_events');
	add_shortcode('featured-more-information', 'featured_more_information');
	add_shortcode('single-banner', 'get_single_banner');
	add_shortcode('home-banner', 'get_home_banner');
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

	$output = ''; $body_html = ''; $header_html = ''; $tax_query = [];

	$title = (!empty($a['title']) ? $a['title'] : $pto->label);

	if($a['show-archive-link']) {
		$pto_archive = get_post_type_archive_link($a['post-type']);
		$header_html = '<div class="d-flex mb-4 align-items-center archive-link">
                      <div class="p-0"><h2>'.$title.'</h2></div>
                      <div class="ml-auto p-0">
                        <a class="btn btn-pink-light btn-block pl-4 pr-4 shadow-sm" href="'.$pto_archive.'">Learn more</a>
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
				$featured_image = '';

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
				$affiliate_link = get_field('affiliate_link', $p->ID);
				if(!empty($featured_image)){
					if(!empty($affiliate_link)){
						$body_html .= '	<div class="slide text-center">
															<a href="'.$affiliate_link.'" target="_blank">
																<img class="img-fluid" src="'.$featured_image.'" alt="'.$title.'" />
																<p>'.$title.'</p>
															</a>
														</div>';
					}else{
						$body_html .= '	<div class="slide text-center">
															<img class="img-fluid" src="'.$featured_image.'" alt="'.$title.'" />
															<p>'.$title.'</p>
														</div>';
					}
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

function featured_section($atts) {
	$a = shortcode_atts([
		'title' => '',
		'display-type' => '',
		'excerpt' => '',
		'services' => ''
	], $atts);

	$services = explode(",", $a['services']);

	$output; $body_html; $header_html;

	if($a['display-type'] === 'scrolling') {
		$header_html = '<div class="d-flex mb-4 align-items-center archive-link">
                      <div class="p-0"><h2>'.$a['title'].'</h2></div>
                    </div>';
	} else {
		$header_html = '<h2 class="section-title text-center">'.$a['title'].'</h2>';

		if(!empty($a['excerpt'])){
			$header_html .= '<div class="section-description row">
                      <div class="col-md-8 offset-md-2"><p class="text-center">'.$a['excerpt'].'</p></div>
                    </div>';
		}
	}

	$query = new WP_Query([
		'post_type' => 'services',
		'post__in' => $services,
		'orderby' => 'post__in',
	]);

	if($query->have_posts()){
		$body_html = '<div class="card-deck card-deck-slider '.$a['display-type'].'">';
		foreach($query->posts as $p) {
			$title = $p->post_title;
			$excerpt = substr($p->post_excerpt, 0, 155);
			$permalink = get_permalink($p->ID);
			$thumbnail_override = get_field('thumbnail_override', $p->ID);
			$featured_image;

			// Determine what image to use
			if(!empty($thumbnail_override)) {
				$featured_image = wp_get_attachment_image_url($thumbnail_override, 'wide-thumb');
			} elseif (get_the_post_thumbnail_url($p->ID)) {
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
	}

	wp_reset_query();

	$output .= '
      <section class="featured-section '.$a['display-type'].'">
        <div class="container">
          '.$header_html.'
          '.$body_html.'
        </div>
      </section>';

	return $output;
}

function featured_content($atts)
{
	$a = shortcode_atts([
		'title' => '',
		'image' => '',
		'content' => '',
		'alignment' => ''
	], $atts);

	$title = $a['title'];
	$content = apply_filters('the_content', $a['content']);
	$featured_image = wp_get_attachment_image_url($a['image'], 'large');

	if($featured_image) {
		if($a['alignment'] == "left"){
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
      <section class="featured-content">
        <div class="container">
          <div class="row align-items-center">
            '.$columns.'
          </div>
        </div>
      </section>';

	return $output;
}

function featured_events($atts) {
	$a = shortcode_atts([
		'title' => '',
		'events' => '',
	], $atts);

	$events = explode(",", $a['events']);

	$output; $body_html; $header_html;

	$title = $a['title'];

	$header_html = '<div class="d-flex mb-4 align-items-center archive-link">
                      <div class="p-0"><h2>'.$title.'</h2></div>
                    </div>';

	$query = new WP_Query([
		'post_type' => 'events',
		'post__in' => $events,
		'orderby' => 'post__in',
	]);

	if($query->have_posts()){
		$body_html = '<div class="card-deck card-deck-slider">';
		foreach($query->posts as $p) {
			$title = $p->post_title;
			$excerpt = substr($p->post_excerpt, 0, 155);
			$permalink = get_permalink($p->ID);

			$event_type = get_field('event_type', $p->ID);
			$dates = get_field('dates', $p->ID);
			$registration_cost = str_replace("$", "", get_field('registration_cost', $p->ID));

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

			$body_html .= ' <div class="slide text-center">
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
              <a target="_blank" class="social-link" href="'.$a['facebook'].'">
                <span class="fa-stack fa-1x">
                  <i class="fas fa-circle fa-stack-2x"></i>
                  <i class="fab fa-facebook-f fa-stack-1x fa-inverse"></i>
                </span>
              </a>
              <a target="_blank" class="social-link" href="'.$a['youtube'].'">
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

function get_single_banner($atts) {
	$obj = get_queried_object();
	$output = ""; $title = ""; $featured_image = "";

	global $post;
	$p = $post;

	if(is_single() || is_page()) {
		if (is_post_type_archive('wisdom-wednesday')) {
			$title = 'Wisdom Wednesday Archive';
		} elseif (is_singular('wisdom-wednesday')) {
			$title = "Wisdom Wednesday: " . get_the_title();
		} else {
			$title = get_the_title();
		}

		$event_type = get_field('event_type', $p->ID);

		// Determine what image to use
		if(get_the_post_thumbnail_url($p->ID)) {
			$featured_image = get_the_post_thumbnail_url($p->ID, 'wide-banner');
		} elseif(get_the_post_thumbnail_url($event_type[0]->ID)) {
			// else, if is Event and the Event Type has a featured image, use that
			$featured_image = get_the_post_thumbnail_url($event_type[0]->ID, 'wide-banner');
		} elseif (wp_get_attachment_image_url(206)) {
			$featured_image = wp_get_attachment_image_url(260, 'wide-banner');
		}

	} else {
		$title = $obj->name;
		$featured_image = wp_get_attachment_image_url(260, 'wide-banner');
	}

	$img_html = (!empty($featured_image) ? "<img class='img-fluid banner-image' src='$featured_image' />" : "" );

	if($featured_image){
		$output .= "<div class='single-banner'>
                  $img_html
                  <div class='banner-text'>
                  	<div class='container'>
											<div class='row align-items-center'>
												<div class='col-12'>
													<div class='description'>
														<h2>$title</h2>
													</div>
												</div>
											</div>
										</div>
                  </div>
                </div>";
	}

	return $output;
}

function get_home_banner($atts) {
	$output = "";

	$query = new \WP_Query([
		'post_type' => 'home-banner',
		'orderby' => 'menu_order',
		'order' => 'ASC',
		'posts_per_page' => -1
	]);

	if($query->have_posts()) {
		$output .= "<div class='home-banner-section'>";
		foreach($query->posts as $p) {
			$title = $p->post_title;
			$desc = get_field('description', $p->ID);
			$link = get_field('link', $p->ID);
			$featured_image = get_the_post_thumbnail_url($p->ID, 'wide-banner');

			$link = (!empty($link) ? "<a class='btn btn-primary btn-block link mt-xs-2 mt-sm-2' href='".$link['url']."' target='".$link['target']."' title='".$link['title']."'>Learn More</a>" : "");
			$img_html = (!empty($featured_image) ? "<img class='img-fluid banner-image' src='$featured_image' />" : "" );

			$output .= "<div class='slide'>
                    $img_html
                    <div class='banner-text'>
                    	<div class='container'>
												<div class='row align-items-center'>
													<div class='col-12 col-md-9'>
														<div class='description'>
															<h2>$title</h2>
															<p>$desc</p>
														</div>
													</div>
													<div class='col-12 col-md-3'>$link</div>
												</div>
											</div>
                    </div>
                  </div>";
		}
		$output .= "</div>";
	}

	wp_reset_query();

	return $output;
}

$TLC_Shortcodes = new TLC_Shortcodes();
