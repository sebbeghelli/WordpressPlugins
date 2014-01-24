<?php

/*
Plugin Name: GizWeather
Description: A simple weather plugin for Gizmodo
Author: Sebastien Beghelli
Version: 1.0
*/

// ADDING THE CITY CUSTOM FIELD TO THE POST
function weathercustomfield($post_id){
	if($_GET['post_type'] != 'page'){
		add_post_meta($post_id, 'City', '', true);
	}
	return true;
}

add_action('wp_insert_post', 'weathercustomfield');

// SEARCH FILTER TO ADD THE CITY CUSTOM FIELD TO THE WEBSITE SEARCH RESULTS
function custom_search_query( $query ) {
	if ( !is_admin() && $query->is_search ) {
		$value = $query->query_vars['s'];
		$query->query_vars['s'] = "";
			$query->set('meta_query', array(
				array(
				'key' => 'City',
				'value' => $value,
				'compare' => 'LIKE'
				)
			));
		$query->set('properties', 'post');
	};
}

add_filter( 'pre_get_posts', 'custom_search_query');

// CREATING THE WEATHER SHORTCODE
function weatherfunc( $atts )  {

	$city = get_post_meta( get_the_ID(), 'City', true );
		
	$loc_array= Array($city);		
	// PRIVATE SEBASTIEN BEGHELLI KEY FOR GIZ TEST APPLIANCE - PLEASE DON'T USE FOR ANY OTHER PURPOSE
	$api_key="k4amjd2k9rmpfzu3jgz5se9d";

	$loc_safe=Array();
	foreach($loc_array as $loc){
		$loc_safe[]= urlencode($loc);
	}
	$loc_string=implode(",", $loc_safe);

	$basicurl=sprintf('http://api.worldweatheronline.com/free/v1/weather.ashx?key=%s&q=%s&num_of_days=%s', $api_key, $loc_string, intval($num_of_days));

	$xml_response = file_get_contents($basicurl);
	$xml = simplexml_load_string($xml_response);

	$finalweatherprint = "<fieldset>";
	$finalweatherprint .= "<strong>Current weather in ".$city."</strong><br/>";
	$finalweatherprint .= "<img style='float:left;margin:5px' src='".$xml->current_condition->weatherIconUrl."' />";
	$finalweatherprint .= "Temperature : ".$xml->current_condition->temp_C."°<br/>";
	$finalweatherprint .= $xml->current_condition->cloudcover."% cloud cover<br/>";
	$finalweatherprint .= "Wind speed : ".$xml->current_condition->windspeedKmph."km/h<br/>";
	$finalweatherprint .= "</fieldset>";

	return $finalweatherprint;
}

add_shortcode( 'showweather', 'weatherfunc');

?>