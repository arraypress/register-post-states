<?php
/**
 * Initializes and registers the Post_States_Manager to manage and display custom post states within the WordPress
 * admin panel.
 *
 * This utility function sets up a Post_States_Manager with a given mapping of option keys to their labels for
 * displaying custom post states, such as "Landing Page" or "Featured Article," next to post titles in the admin posts
 * list. These states provide administrators with at-a-glance insights into specific attributes of the posts directly
 * from the list view.
 *
 * The function requires an associative array mapping option keys to labels and an optional callable responsible for
 * retrieving option values. It gracefully handles errors during initialization, optionally using a provided error
 * callback function.
 *
 * Example usage:
 * $options_map = [
 *     'landing_page' => __('Landing Page', 'text-domain'),
 *     'featured_post' => __('Featured Post', 'text-domain'),
 *     // ... other states
 * ];
 * register_post_states( $options_map, 'get_option', function( $exception ) {
 *     // Error handling logic here
 * });
 *
 * By invoking this function, the Post_States_Manager is hooked into the 'display_post_states' WordPress filter and
 * will
 * append the appropriate labels to the posts in the list based on their states.
 *
 * @package         arraypress/register-post-states
 * @copyright       Copyright (c) 2024, ArrayPress Limited
 * @license         GPL2+
 * @version         0.1.0
 * @author          David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostStates;

use function is_callable;
use function call_user_func;
use Exception;

if ( ! function_exists( 'register_post_states' ) ) {
	/**
	 * Initializes the Post_States_Manager with given options and handles exceptions.
	 *
	 * @param array         $options_map    Associative array mapping option keys to labels.
	 * @param string|null   $option_getter  Function to retrieve the option values, defaults to 'get_option'.
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return RegisterPostStates|null The initialized Post_States_Manager or null on failure.
	 */
	function register_post_states(
		array $options_map,
		string $option_getter = null,
		?callable $error_callback = null
	): ?RegisterPostStates {
		try {
			return new RegisterPostStates( $options_map, $option_getter );
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			return null; // Return null on failure
		}
	}
}