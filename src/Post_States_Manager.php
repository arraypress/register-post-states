<?php
/**
 * The Post_States_Manager class manages the display of custom post states within the WordPress admin area.
 *
 * This class allows for the addition of custom labels next to post titles in the posts list, indicating special states
 * based on dynamic options such as "Landing Page", "Featured Article", etc. It requires an associative array that maps
 * option keys to their respective labels and a callable that retrieves option values. Post states enhance the CMS
 * interface by quickly informing administrators of specific properties of the posts without needing to open them.
 *
 * Example usage:
 * $options_map = [
 *     'landing_page' => __('Landing Page', 'text-domain'),
 *     'featured_post' => __('Featured Post', 'text-domain'),
 *     // ... other states
 * ];
 * $post_states_manager = new Post_States_Manager( $options_map );
 *
 * // This will hook into the 'display_post_states' filter and modify the post states as needed.
 *
 * Note: The class includes a check to prevent redefinition if it's already been defined in the namespace.
 *
 * @package       arraypress/register-post-states
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL2+
 * @since         1.0.0
 * @author        David Sherlock
 */

namespace ArrayPress\Utils\WP;

use InvalidArgumentException;
use WP_Post;
use function add_filter;
use function is_wp_error;

/**
 * A WordPress library for defining and managing custom post states within the admin area, facilitating a more
 * informative and intuitive post management experience.
 *
 * If the class already exists in the namespace, it won't be redefined.
 */
if ( ! class_exists( __NAMESPACE__ . '\\Post_States_Manager' ) ) :

	/**
	 * Class Post_States_Manager
	 *
	 * Manages the addition of custom post states in the WordPress admin area
	 * for specific pages based on dynamic options.
	 */
	class Post_States_Manager {

		/**
		 * An associative array mapping option keys to labels for the post states.
		 *
		 * @var array
		 */
		private array $options_map;

		/**
		 * The callable function used to retrieve option values.
		 *
		 * @var callable
		 */
		private $option_getter;

		/**
		 * Post_States_Manager constructor.
		 *
		 * @param array       $options_map   Associative array mapping option keys to labels.
		 * @param string|null $option_getter Function to retrieve the option values, defaults to 'get_option'.
		 */
		public function __construct( array $options_map, string $option_getter = null ) {
			$this->options_map = array_filter( $options_map, function ( $label, $option_key ) {
				return $this->validate_options_map( $label, $option_key );
			}, ARRAY_FILTER_USE_BOTH );

			// If no callable is provided, use the WordPress 'get_option' function by default
			$this->option_getter = is_callable( $option_getter ) ? $option_getter : 'get_option';

			// If the default 'get_option' is not callable (which would be unusual), throw an exception
			if ( ! is_callable( $this->option_getter ) ) {
				throw new InvalidArgumentException( 'The option getter is not a callable function.' );
			}

			if ( empty( $this->options_map ) ) {
				throw new InvalidArgumentException( 'The options map cannot be empty and must contain valid keys and labels.' );
			}

			// Add the filter hook
			add_filter( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );
		}


		/**
		 * Validates each entry in the options map to ensure keys and labels are not empty.
		 *
		 * @param string $label      The label for the post state.
		 * @param string $option_key The key for the option.
		 *
		 * @return bool Returns true if both key and label are valid, false otherwise.
		 */
		private function validate_options_map( string $label, string $option_key ): bool {
			return ! empty( $option_key ) && ! empty( $label );
		}

		/**
		 * Adds custom page state displays to the WordPress Pages list.
		 *
		 * @param array   $post_states Existing post states.
		 * @param WP_Post $post        The current post object.
		 *
		 * @return array The modified post states.
		 */
		public function display_post_states( array $post_states, WP_Post $post ): array {
			if ( is_wp_error( $this->options_map ) ) {
				return $post_states; // Early return if the options map is invalid.
			}

			foreach ( $this->options_map as $option_key => $label ) {
				$option_value = call_user_func( $this->option_getter, $option_key );

				if ( intval( $option_value ) === $post->ID ) {
					$post_states[ $option_key ] = $label;
				}
			}

			return $post_states;
		}
	}

endif;