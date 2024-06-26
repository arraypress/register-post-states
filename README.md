# WordPress Register Custom Post States Library

Effortlessly manage and display custom indicators next to your posts in WordPress. Define your own post states like "
Landing Page" or "Featured Article" with ease. This library streamlines content management, aiding quick post
identification and categorization.

## Features ##

* **Custom Post States:** Define your own indicators for posts.
* **Flexible Integration:** Seamlessly add this library to your WordPress project.
* **Callable Function:** Use a callable function to retrieve option values.
* **Quick Reference:** Instantly recognize the state of each post.

## Minimum Requirements ##

* **PHP:** 7.4
* **WordPress:** 6.5.4

## Installation

This library is a developer tool, not a WordPress plugin, so it needs to be included in your WordPress project or plugin.

You can install it using Composer:

```bash
composer require arraypress/register-custom-columns
```

## Basic Usage

```php
// Require the Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

use function ArrayPress\RegisterPostStates\register_post_states;
```

### Registering Post States

To utilize this functionality, you first define an associative array that maps option keys to labels for the post
states. You can optionally specify a callable function responsible for retrieving option values, such as WordPress'
built-in `get_option` function. Here's how you can set it up:

```php
// Configure post states with an associative array, mapping option keys to labels.
// Optionally, specify a callable function like `get_option` to retrieve option values.
// It's important to ensure `get_option` returns a valid WordPress post ID to match with the admin posts list.
register_post_states( [
    'landing_page'  => __( 'Landing Page', 'text-domain' ),
    'featured_post' => __( 'Featured Post', 'text-domain' )
] );
```

When the `RegisterPostStates` is initialized with the provided options, it hooks into
WordPress `'display_post_states'` filter. This integration allows it to append the custom state labels to the
appropriate posts in the admin list view, based on the configuration provided.

Ensure that this function returns a valid post ID to correctly associate custom state labels with the appropriate posts.

### Advanced Usage

For more advanced customization, you can set a specific callable function to fetch option values instead of the
default `get_option`. This is particularly useful if your WordPress setup utilizes a custom options management system,
such as `edd_get_option` from Easy Digital Downloads or any other custom-built mechanism.

Here's how to define a custom getter function for the `RegisterPostStates`:

```php
$options_map = [
    'landing_page'  => __( 'Landing Page', 'text-domain' ),
    'featured_post' => __( 'Featured Post', 'text-domain' ),
    // Additional custom states as necessary
];

// Define your custom getter function, such as 'edd_get_option' or another custom function.
$custom_option_getter = 'edd_get_option';

// Initialize the Post_States_Manager with your custom getter.
register_post_states( $options_map, $custom_option_getter );
```

When you provide your custom getter, `RegisterPostStates` will use this function for all option value retrievals. This
seamless integration allows for a consistent data handling process that aligns with your website's specific
configuration.

Remember to ensure that the custom getter function you provide meets the expected signature and functionality
as `get_option`, to prevent any incompatibilities or errors in the post states management.

### Error Handling

The `register_post_states` function also accepts a third parameter: an error callback function. This callback is invoked
if an exception occurs during the initialization of the `RegisterPostStates`. This allows for graceful handling of
initialization errors and ensures a smooth user experience.

```php
register_post_states( $options_map, 'get_option', function( $exception ) {
    edd_debug_log_exception( $exception );
});
```

## Contributions

Contributions to this library are highly appreciated. Raise issues on GitHub or submit pull requests for bug
fixes or new features. Share feedback and suggestions for improvements.

## License: GPLv2 or later

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.