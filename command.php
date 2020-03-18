<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

use WP_CLI\Utils;

/**
 * A wrapper for 'media import' command to download and import photos from Pexels into Media Library.
 */
class Pexels_Import_Command extends WP_CLI_Command {
	
	/**
	 * Pexels API base URL.
	 */
	const PEXELS_API_URL = 'https://api.pexels.com/v1/';
	
	/**
	 * Pexels API key
	 */
	const PEXELS_API_KEY = '563492ad6f9170000100000147b95f140fe441b858072ac5940c9ba0';
	
	/**
	 * Image sizes supported by Pexels API.
	 */
	const	PEXELS_IMAGE_SIZES = array( 'original', 'large2x', 'large', 'medium', 'small', 'portrait', 'landscape', 'tiny' );

	/**
	 * Download and import photos from Pexels into Media Library.
	 *
	 * ## OPTIONS
	 *
	 * <id|page_url|random>...
	 * : One or more IDs or page URLs of the Pexels photo to import. Or pick 'random' from the Curated photos.
	 *
	 * [--size=<name>]
	 * : Name of the predefined image size. Defaults to 'original'.
	 * ---
	 * default: original
	 * options:
	 *   - original    # (full size)
	 *   - large2x     # (1880x1300)
	 *   - large       # (940x650)
	 *   - medium      # (?x350)
	 *   - small       # (?x130)
	 *   - portrait    # (800x1200 cropped)
	 *   - landscape   # (1200x627 cropped)
	 *   - tiny        # (280x200 cropped)
	 * ---
	 *
	 * [--custom_size=<1920x1280>]
	 * : Custom image size specified as maximum width and height.
	 *
	 * [--crop]
	 * : Crop the image to the specified custom size.
	 *
	 * [--[no-]credit]
	 * : Credit a photographer by inserting links to Pexels website in the Description field. Enabled by default if no description is provided. Single photo only.
	 *
	 * [--title=<title>]
	 * : Attachment title (post title field). Single photo only.
	 *
	 * [--caption=<caption>]
	 * : Caption for attachent (post excerpt field). Single photo only.
	 *
	 * [--alt=<alt_text>]
	 * : Alt text for image (saved as post meta).
	 *
	 * [--desc=<description>]
	 * : "Description" field (post content) of attachment post.
	 *
	 * [--post_id=<post_id>]
	 * : ID of the post to attach the imported file to.
	 *
	 * [--featured_image]
	 * : If set, set the imported image as the Featured Image of the post its attached to. Single photo only.
	 *
	 * [--porcelain]
	 * : Output just the new attachment ID.
	 *
	 * ## EXAMPLES
	 *
	 *     # Import single photo by ID in 'large' size.
	 *     $ wp media pexels photo 3604268 --size=large
	 *
	 *     # Import single photo in custom size without link to Pexels in the description and set it as Featured image for the post ID 1.
	 *     $ wp media pexels photo 3604268 --custom_size=1920x1280 --no-credit --featured_image --post_id=1
	 *
	 *     # Import single photo by page URL in 'original' size with custom title and caption.
	 *     $ wp media pexels photo https://www.pexels.com/photo/snow-covered-pine-trees-3604268/ --title="Snow trees" --caption="Winter is coming"
	 *
	 *     # Import 2 random (Curated) photos cropped to custom size.
	 *     $ wp media pexels photo random random --custom_size=960x960 --crop
	 *
	 *     # Import 3 photos in 'medium' size with the Alt text and attach them to the post ID 1.
	 *     $ wp media pexels photo 3143922 2703181 3534924 --size=medium --alt="City" --post_id=1
	 *
	 * @alias image
	 */
	public function photo( $args, $assoc_args = array() ) {
		$assoc_args = wp_parse_args(
			$assoc_args,
			array(
				'size'    => 'original',
			)
		);
		
		$size = $assoc_args['size'];
		if ( ! in_array( $size, self::PEXELS_IMAGE_SIZES ) ) {
			WP_CLI::error( sprintf( 'Unknown image size "%s".', $size ) );
		}
		$custom_size = array();
		if ( ! empty( $assoc_args['custom_size'] ) && ! preg_match( '/(?<width>\d+)x(?<height>\d+)/', $assoc_args['custom_size'], $custom_size ) ) {
			WP_CLI::error( sprintf( 'Invalid custom image size "%s".', $assoc_args['custom_size'] ) );
		}
		$crop = Utils\get_flag_value( $assoc_args, 'crop' );
		$credit = Utils\get_flag_value( $assoc_args, 'credit', true );
		
		// URLs for 'media import' command
		$urls = array();
		
		foreach ( $args as $id ) {
			if ( ! preg_match( '/^\d{3,}$|-(\d{3,})\/$|^random$/', $id, $id_match ) ) {
				WP_CLI::error( "Invalid photo ID '$id'." );
			}
			// ID from page URL
			if ( isset( $id_match[1] ) ) {
				$id = $id_match[1];
			}
			
			// Make request to the Pexels API
			$api_url = self::PEXELS_API_URL . ( $id == 'random' ? 'curated?per_page=1&page=' . rand(1, 1000) : "photos/$id" );
			$response = Utils\http_request( 'GET', $api_url, null, array( 'Authorization' => self::PEXELS_API_KEY ), array( 'timeout' => 30 ) );
			if ( 20 !== (int) substr( $response->status_code, 0, 2 ) ) {
				WP_CLI::error( "Couldn't fetch response from Pexels API '{$api_url}' (HTTP code {$response->status_code})." );
			}
			// Parse photo data
			$data = json_decode( $response->body, true );
			//WP_CLI::debug( 'Photo JSON data: ' . print_r( $data, true ) );
			if ( isset( $data['photos'] ) ) {
				list($photo) = $data['photos'];
			} elseif ( isset( $data['src'] ) ) {
				$photo = $data;
			} else {
				WP_CLI::error( "Failed to parse photo data at '{$api_url}'." );
			}
			
			// Get photo URL of predefined size
			if ( array_key_exists( $size, $photo['src'] ) && empty( $custom_size ) ) {
				$photo_url = $photo['src'][$size];
			// or custom size (but downsize only)
			} elseif ( ! empty( $custom_size ) && $custom_size['width'] < $photo['width'] && $custom_size['height'] < $photo['height'] ) {
				// use 'large' image size URL and replace the width and height query params
				$photo_url = preg_replace(
					array( '/w=\d+/', '/h=\d+/' ),
					array( "w={$custom_size['width']}", "h={$custom_size['height']}" ),
					$photo['src']['large']
				);
				if ( $crop ) {
					$photo_url .= '&fit=crop';
				}
			// or fallback to the original size
			} else {
				$photo_url = $photo['src']['original'];
			}
			$urls[] = $photo_url;
		}
		
		// Metadata for single photo import
		if ( count( $urls ) == 1 ) {
			// Use photo's page URL to reconstruct the default title
			if ( empty( $assoc_args['title'] ) ) {
				$assoc_args['title'] = ucwords( str_replace( '-', ' ', preg_replace( '@^.+/photo/([^/]+)-\d{3,}/$@', '$1', $photo['url'] ) ) );
			}
			// Credit a photographer in the description by default
			if ( $credit && empty( $assoc_args['desc'] ) ) {
				$assoc_args['desc'] = sprintf( 'Photo by <a href="%s" target="_blank" rel="noopener">%s</a> on <a href="%s" target="_blank" rel="noopener">Pexels</a>.', $photo['photographer_url'], $photo['photographer'], $photo['url'] );
			}
		// Metadata for multi photo import
		} else {
			// Remove optional args not valuable when importing multiple photos
			unset( $assoc_args['title'], $assoc_args['caption'], $assoc_args['featured_image'] );
		}
		
		// Remove optional args not supported by 'media import' command
		unset( $assoc_args['size'], $assoc_args['custom_size'], $assoc_args['crop'], $assoc_args['credit'] );

		WP_CLI::debug( 'Photo URLs to import: ' . print_r( $urls, true ) );
		WP_CLI::debug( 'Photo import params: ' . print_r( $assoc_args, true ) );
		
		// Run the 'media import' command with Pexels photo URL(s) and all optional import parameters
		WP_CLI::run_command( array_merge( array( 'media', 'import' ), $urls ), $assoc_args );
	}	
	
}

WP_CLI::add_command( 'media pexels', 'Pexels_Import_Command' );
