<?php
/*
  Plugin Name: Google Review Slider
  Plugin URI: localmap.co
  Description: Integrate reviews from your Google My Business page via short code
  Version: 1.2
  Author: Local Map Co.
  Author URI: localmap.co
  License: GPL2
*/

function review_slider_enqueue_scripts(){

  global $post;
  if( !is_a( $post, 'WP_Post' ) || !has_shortcode( $post->post_content, 'review_slider') )
    return;

  wp_enqueue_style('slick-style', plugin_dir_url( __FILE__ ) . 'slick/slick.css');
  wp_enqueue_style('slick-theme-style', plugin_dir_url( __FILE__ ) . 'slick/slick-theme.css', array('slick-style'));
  wp_enqueue_style('review-slider-theme', plugin_dir_url( __FILE__ ) . 'css/google-review-slider.css');

  wp_enqueue_script('slick', plugin_dir_url( __FILE__ ) . 'slick/slick.min.js', array('jquery'), true);
  wp_enqueue_script('review-slider', plugin_dir_url( __FILE__ ) . 'js/google-review-slider.js', array('slick'), "0.0.1", true);

}
add_action( 'wp_enqueue_scripts', 'review_slider_enqueue_scripts');


function review_slider_get_reviews(){
  $APIkey = 'AIzaSyAMjdI44Yzrmgm3fq7xxy9HgPN0qMD34kk';

  $options = get_option( 'review_slider_settings' );
  $placeID = $options['review_slider_google_place_ID'];

  if(!$placeID)
    return;

  $querystring = 'https://maps.googleapis.com/maps/api/place/details/json?placeid=' . $placeID . '&key=' . $APIkey;

  $results_json = file_get_contents($querystring);
  $raw_results = json_decode($results_json);

  if(!$raw_results || !$raw_results->result || !$raw_results->result->reviews)
    return;

  $raw_reviews = $raw_results->result->reviews;
  $results = array();
  for($i = 0; $i < sizeof($raw_reviews); $i++){
    $obj = new stdClass();
    $obj->author_name = $raw_reviews[$i]->author_name;
    $obj->text = $raw_reviews[$i]->text;
    $obj->rating = $raw_reviews[$i]->rating;
    array_push($results, $obj);
  }

  update_option( 'review_slider_reviews', $results );
  update_option( 'review_slider_business_url', $raw_results->result->url);
  update_option( 'review_slider_city', $raw_results->result->address_components[2]->short_name);
}
add_action('review_slider_daily_hook', 'review_slider_get_reviews');


function review_slider_on_activation(){
  wp_schedule_event(time(), 'daily', 'review_slider_daily_hook');

  $defaults = array();
  $defaults['review_slider_primary_text_color_field'] = '#ffffff';
  $defaults['review_slider_secondary_text_color_field'] = '#93c900';
  $defaults['review_slider_header_text'] = 'Client Testimonials';
  $defaults['review_slider_image_url'] = plugin_dir_url( __FILE__ ) . 'city_example_half-min.jpg';

  add_option('review_slider_settings', $defaults);
}


function review_slider_on_deactivation(){
  $timestamp = wp_next_scheduled( 'review_slider_daily_hook' );
  wp_unschedule_event($timestamp, 'review_slider_daily_hook');
}


function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=google-review-slider">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );


function review_slider_admin_enqueue_scripts( $hook ) {

  if( is_admin() ) {

    if(function_exists( 'wp_enqueue_media' )){
      wp_enqueue_media();
    }else{
      wp_enqueue_style('thickbox');
      wp_enqueue_script('media-upload');
      wp_enqueue_script('thickbox');
    }

    // Add the color picker css file
    wp_enqueue_style( 'wp-color-picker' );

    // Include our custom jQuery file with WordPress Color Picker dependency
    wp_enqueue_script( 'review-slider-admin', plugin_dir_url( __FILE__ ) . 'js/google-review-slider-admin.js', array( 'wp-color-picker' , 'thickbox', 'media-upload'), false, true );
  }
}
add_action( 'admin_enqueue_scripts', 'review_slider_admin_enqueue_scripts' );


function review_slider_generate_menu(){

  ?>
    <div class="wrap">
      <h1> Google Review Slider </h1>
      <form method="post" action="options.php">
        <?php settings_fields( 'reviewSliderGroup' ); ?>
        <?php do_settings_sections( 'reviewSliderGroup' ); ?>
        <?php submit_button(); ?>
      </form>
      <p> Shortcode is [review_slider] </p>
      <p> To get the Google Place ID, <a target="_blank" href="https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder">visit this link.</a></p>
      <p> Reviews are refreshed every 24 hours or whenever this page is visited.</p>
    </div>
  <?php

  review_slider_get_reviews();
}


function review_slider_settings_init() {

  register_setting( 'reviewSliderGroup', 'review_slider_settings' );

	add_settings_section(
		'review_slider_reviewSliderGroup_section',
		__( 'All Settings', 'wordpress' ),
		'',
		'reviewSliderGroup'
	);

	add_settings_field(
		'review_slider_google_place_ID',
		__( 'Google Place ID', 'wordpress' ),
		'review_slider_google_place_ID_render',
		'reviewSliderGroup',
		'review_slider_reviewSliderGroup_section'
	);

  add_settings_field(
    'review_slider_header_text',
    __( 'Header Text', 'wordpress' ),
    'review_slider_header_text_render',
    'reviewSliderGroup',
    'review_slider_reviewSliderGroup_section'
  );

	add_settings_field(
		'review_slider_checkbox_five_only',
		__( 'Only Show 5 Star Reviews', 'wordpress' ),
		'review_slider_checkbox_five_only_render',
		'reviewSliderGroup',
		'review_slider_reviewSliderGroup_section'
	);

  add_settings_field(
    'review_slider_primary_text_color_field',
    __( 'Primary Text Color', 'wordpress' ),
    'review_slider_primary_text_color_field_render',
    'reviewSliderGroup',
    'review_slider_reviewSliderGroup_section'
  );

  add_settings_field(
    'review_slider_secondary_text_color_field',
    __( 'Secondary Text Color', 'wordpress' ),
    'review_slider_secondary_text_color_field_render',
    'reviewSliderGroup',
    'review_slider_reviewSliderGroup_section'
  );

  add_settings_field(
    'review_slider_image_url',
    __( 'Background Image URL', 'wordpress' ),
    'review_slider_image_url_render',
    'reviewSliderGroup',
    'review_slider_reviewSliderGroup_section'
  );

}
add_action( 'admin_init', 'review_slider_settings_init' );


function review_slider_google_place_ID_render(  ) {

	$options = get_option( 'review_slider_settings' );
	?>
	<input type='text' name='review_slider_settings[review_slider_google_place_ID]' value='<?php echo $options['review_slider_google_place_ID']; ?>'>
	<?php
}

function review_slider_header_text_render(  ) {

	$options = get_option( 'review_slider_settings' );
	?>
	<input type='text' name='review_slider_settings[review_slider_header_text]' value='<?php echo $options['review_slider_header_text']; ?>'>
	<?php
}

function review_slider_checkbox_five_only_render(  ) {

	$options = get_option( 'review_slider_settings' );

  if( ! isset($options['review_slider_checkbox_five_only']) )
    $options['review_slider_checkbox_five_only'] = false;

	?>
	<input type='checkbox' name='review_slider_settings[review_slider_checkbox_five_only]' <?php checked( $options['review_slider_checkbox_five_only'], 1 ); ?> value='1'>
	<?php
}


function review_slider_primary_text_color_field_render(  ) {

	$options = get_option( 'review_slider_settings' );
	?>
	<input type='text' class="color-field" name='review_slider_settings[review_slider_primary_text_color_field]' value='<?php echo $options['review_slider_primary_text_color_field']; ?>'>
	<?php
}


function review_slider_secondary_text_color_field_render(  ) {

	$options = get_option( 'review_slider_settings' );
	?>
	<input type='text' class="color-field" name='review_slider_settings[review_slider_secondary_text_color_field]' value='<?php echo $options['review_slider_secondary_text_color_field']; ?>'>
	<?php
}

function review_slider_image_url_render(  ) {

	$options = get_option( 'review_slider_settings' );
	?>
	<input type='text' class="header_logo_url" name='review_slider_settings[review_slider_image_url]' value='<?php echo $options['review_slider_image_url']; ?>'>
  <a href="#" class="header_logo_upload">Upload</a>
	<?php
}

function review_slider_add_menu(){
  add_options_page('Google Review Slider', 'Google Review Slider', 'manage_options', 'google-review-slider', 'review_slider_generate_menu');
}
add_action('admin_menu', 'review_slider_add_menu');


function review_slider_shortcode(){

    $reviews = get_option( 'review_slider_reviews' );
    $business_url = get_option( 'review_slider_business_url' );
    $city = get_option( 'review_slider_city' );
    $settings = get_option( 'review_slider_settings' );

    if( ! isset($settings['review_slider_checkbox_five_only']) )
      $settings['review_slider_checkbox_five_only'] = false;

    $header_text = $settings['review_slider_header_text'];
    $only_five = $settings['review_slider_checkbox_five_only'];
    $primary_color = $settings['review_slider_primary_text_color_field'];
    $secondary_color = $settings['review_slider_secondary_text_color_field'];
    $image_url = $settings['review_slider_image_url'];

    $slider_html = '<div class="review-slider-container" style="background-image: url(\'' . $image_url . '\');">' .
      '<h2 id="review-slider-header" style="color:' . $primary_color . ';"><strong>' . $header_text . '<strong></h2>' .
      '<div class="review-slider-slider">';

    for($i = 0; $i < sizeof($reviews); $i++){

      if($only_five && $reviews[$i]->rating != 5)
        continue;

      $slider_html .=
        '<div class="review-slider-review-container">' .
          '<div class="review-slider-star-container">';

      for($j = 0; $j < $reviews[$i]->rating; $j++){
        $slider_html .= '<span style="color:' . $secondary_color . ';">&#9733;</span>';
      }

      $review_text = $reviews[$i]->text;
      if (strlen($review_text) > 15)
        $review_text = substr($review_text, 0, 150) . '...';

      $author_text = $reviews[$i]->author_name . ', ' . $city;

      $slider_html .=
          '</div>' .
          '<p class="review-slider-review-text" style="color:' . $primary_color . ';">' . $review_text . '</p>' .
          '<p class="review-slider-author-text" style="color:' . $secondary_color . ';">' . $author_text . '</p>' .
        '</div>';
    }

    $slider_html .=
      '</div>' .
      '<a target="_blank" style="color:' . $secondary_color . ';" class="review-slider-link" href="' . $business_url . '">More Reviews</a>' .
    '</div>';

  return $slider_html;
}
add_shortcode( 'review_slider', 'review_slider_shortcode' );


register_activation_hook( __FILE__, 'review_slider_on_activation' );
register_deactivation_hook(__FILE__, 'review_slider_on_deactivation' );
?>
