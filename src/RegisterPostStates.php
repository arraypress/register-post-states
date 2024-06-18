<?php
/**
 * The RegisterPostStates class manages the display of custom post states within the WordPress admin area.
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
 * $RegisterPostStates = new RegisterPostStates( $options_map );
 *
 * // This will hook into the 'display_post_states' filter and modify the post states as needed.
 *
 * Note: The class includes a check to prevent redefinition if it's already been defined in the namespace.
 *
 * @package         arraypress/register-post-states
 * @copyright       Copyright (c) 2024, ArrayPress Limited
 * @license         GPL2+
 * @version         0.1.0
 * @author          David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostStates;

use InvalidArgumentException;
use WP_Post;
use function add_filter;
use function array_filter;
use function call_user_func;
use function class_exists;
use function intval;
use function is_callable;
use function is_wp_error;

/**
 * Check if the class `RegisterPostStates` is defined, and if not, define it.
 */
if ( ! class_exists( 'RegisterPostStates' ) ):

	/**
	 * Class RegisterPostStates
	 *
	 * Manages the addition of custom post states in the WordPress admin area
	 * for specific pages based on dynamic options.
	 */
	class RegisterPostStates {

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
		 * RegisterPostStates constructor.
		 *
		 * @param array       $options_map   Associative array mapping option keys to labels.
		 * @param string|null $option_getter Function to retrieve the option values, defaults to 'get_option'.
		 */
		public function __construct( array $options_map, string $option_getter = null ) {
			$this->set_options_map( $options_map );
			$this->set_option_getter( $option_getter ?? 'get_option' );
			$this->setup_hooks();
		}

		/**
		 * Sets the options map.
		 *
		 * @param array $options_map Associative array mapping option keys to labels.
		 */
		public function set_options_map( array $options_map ): void {
			$this->options_map = array_filter( $options_map, function ( $label, $option_key ) {
				return $this->validate_options_map( $label, $option_key );
			}, ARRAY_FILTER_USE_BOTH );

			if ( empty( $this->options_map ) ) {
				throw new InvalidArgumentException( 'The options map cannot be empty and must contain valid keys and labels.' );
			}
		}

		/**
		 * Sets the option getter.
		 *
		 * @param string $option_getter The function to retrieve the option values.
		 */
		public function set_option_getter( string $option_getter ): void {
			if ( ! is_callable( $option_getter ) ) {
				throw new InvalidArgumentException( 'The option getter is not a callable function.' );
			}
			$this->option_getter = $option_getter;
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
		 * Sets up the necessary hooks for displaying post states.
		 *
		 * Hooks into the 'display_post_states' filter to modify the post states based on the provided options map.
		 *
		 * @since 1.0.0
		 */
		public function setup_hooks() {
			add_filter( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );
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