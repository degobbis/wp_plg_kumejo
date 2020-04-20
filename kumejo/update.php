<?php

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'plugins_api', 'kumejo_plugin_info', 20, 3 );
/*
 * $res empty at this step
 * $action 'plugin_information'
 * $args stdClass Object
 */
function kumejo_plugin_info( $res, $action, $args ) {
	// do nothing if this is not about getting plugin information
	if ( 'plugin_information' !== $action ) {
		return false;
	}

	// do nothing if it is not our plugin
	if ( 'kumejo' !== $args->slug ) {
		return false;
	}

	// trying to get from cache first
	if ( false == $remote = get_transient( 'kumejo_update' ) ) {

		// info.json is the file with the actual plugin information on your server
		$remote = wp_remote_get( 'https://raw.githubusercontent.com/degobbis/wp_plg_kumejo/master/update.json', array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {
			set_transient( 'kumejo_update', $remote, 60 ); // 43200 = 12 hours cache
		}

	}

	if ( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {

		$remote = json_decode( $remote['body'] );
		$res    = new stdClass();

		$res->name           = $remote->name;
		$res->slug           = 'kumejo';
		$res->version        = $remote->version;
		$res->tested         = $remote->tested;
		$res->requires       = $remote->requires;
		$res->author         = '<a href="' . $remote->author_url . '">' . $remote->author . '</a>';
		$res->author_profile = $remote->author_url;
		$res->download_link  = $remote->download_url;
		$res->trunk          = $remote->download_url;
		$res->requires_php   = $remote->requires_php;
		$res->last_updated   = $remote->last_updated;
		$res->sections       = array(
			'description' => $remote->sections->description,
			'installation' => $remote->sections->installation,
			'changelog' => $remote->sections->changelog,
			// you can add your custom sections (tabs) here
		);

		// in case you want the screenshots tab, use the following HTML format for its content:
		// <ol><li><a href="IMG_URL" target="_blank"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
		if ( ! empty( $remote->sections->screenshots ) ) {
			$res->sections['screenshots'] = $remote->sections->screenshots;
		}

		$res->banners = array(
			'low'  => $remote->banners->low,
			'high' => $remote->banners->high,
		);

		return $res;

	}

	return false;

}

add_filter( 'site_transient_update_plugins', 'kumejo_push_update', 10, 2 );

function kumejo_push_update( $transient, $test ) {
	if ( empty( $transient->checked ) ) {
		return $transient;
	}

	if ( ! empty( $transient->no_update['kumejo/kumejo.php'] ) ) {
		return $transient;
	}

	// trying to get from cache first, to disable cache comment 10,20,21,22,24
	if ( false == $remote = get_transient( 'kumejo_update' ) ) {

		// info.json is the file with the actual plugin information on your server
		$remote = wp_remote_get( 'https://raw.githubusercontent.com/degobbis/wp_plg_kumejo/master/update.json', array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {
			set_transient( 'kumejo_update', $remote, 60 ); // 43200 = 12 hours cache
		}

	}

	if ( $remote ) {

		$remote = json_decode( $remote['body'] );

		$pluginVersion = $transient->checked['kumejo/kumejo.php'];
		// your installed plugin version should be on the line below! You can obtain it dynamically of course
		if ( $remote && version_compare( $pluginVersion, $remote->version, '<' ) && version_compare( $remote->requires, get_bloginfo( 'version' ), '<' ) ) {
			$res                                 = new stdClass;
			$res->slug                           = 'kumejo';
			$res->plugin                         = 'kumejo/kumejo.php'; // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
			$res->new_version                    = $remote->version;
			$res->tested                         = $remote->tested;
			$res->package                        = $remote->download_url;
			$transient->response[ $res->plugin ] = $res;
			//$transient->checked[$res->plugin] = $remote->version;
		}

	}

	return $transient;
}

add_action( 'upgrader_process_complete', 'kumejo_after_update', 10, 2 );

function kumejo_after_update( $upgrader_object, $options ) {
	if ( $options['action'] == 'update' && $options['type'] === 'plugin' ) {
		// just clean the cache when new plugin version is installed
		delete_transient( 'kumejo_update' );
	}
}