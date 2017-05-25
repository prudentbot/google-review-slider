<?php


$reviews = get_option( 'review_slider_reviews' );
$settings = get_option( 'review_slider_settings' );
$business_url = get_option( 'review_slider_business_url' );

if( ! isset($settings['review_slider_checkbox_five_only']) )
  $settings['review_slider_checkbox_five_only'] = false;

$only_five = $settings['review_slider_checkbox_five_only'];
$primary_color = $settings['review_slider_primary_text_color_field'];
$secondary_color = $settings['review_slider_secondary_text_color_field'];

$slider_html =
'<div class="review-slider-container">' .
  '<h2 id="review-slider-header" style="color:' . $primary_color . ';"><strong>Client Testimonials<strong></h2>' .
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

  $slider_html .=
      '</div>' .
      '<p class="review-slider-review-text" style="color:' . $primary_color . ';">' . $review_text . '</p>' .
      '<p class="review-slider-author-text" style="color:' . $secondary_color . ';">' . $reviews[$i]->author_name . '</p>' .
    '</div>';
}

$slider_html .=
  '</div>' .
  '<a class="review-slider-link" target="_blank" style="color:' . $secondary_color . ';" href="' . $business_url . '">More Reviews</a>' .
'</div>';

echo $slider_html;

?>
