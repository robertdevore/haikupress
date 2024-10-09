<?php

/**
 * Plugin Name: HaikuPress
 * Plugin URI:  https://github.com/robertdevore/haikupress/
 * Description: Enforces a haiku format on post content, allowing only a 5-7-5 syllable structure.
 * Version:     1.0.0
 * Author:      Robert DeVore
 * Author URI:  https://robertdevore.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: haikupress
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    wp_die();
}

// Define the plugin version.
define( 'HAIKUPRESS_VERSION', '1.0.0' );

/**
 * Validate content before publishing via the REST API (Block Editor).
 *
 * @param WP_Post         $prepared_post An object representing a single post prepared
 *                                        for inserting or updating the database.
 * @param WP_REST_Request $request       The request object.
 * @return WP_Post|WP_Error The prepared post or a WP_Error object on failure.
 */
function haikupress_rest_pre_insert_post( $prepared_post, $request ) {
    if ( isset( $prepared_post->post_type ) && 'post' !== $prepared_post->post_type ) {
        return $prepared_post;
    }

    $content = isset( $prepared_post->post_content ) ? $prepared_post->post_content : '';

    // Extract plain text from blocks.
    $plain_text = haikupress_get_plain_text( $content );

    // Validate the haiku format using plain text.
    $result = haikupress_validate_haiku( $plain_text );

    if ( is_wp_error( $result ) ) {
        return new WP_Error(
            'rest_cannot_publish',
            $result->get_error_message(),
            array( 'status' => 403 )
        );
    }

    return $prepared_post;
}
add_filter( 'rest_pre_insert_post', 'haikupress_rest_pre_insert_post', 10, 2 );

/**
 * Validate content before publishing via Classic Editor.
 *
 * @param array $data    An array of slashed, sanitized, and processed post data.
 * @param array $postarr An array of sanitized (and slashed) but otherwise unmodified post data.
 * @return array Modified post data.
 */
function haikupress_validate_on_save( $data, $postarr ) {
    if ( 'post' !== $data['post_type'] ) {
        return $data;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $data;
    }

    if ( wp_is_post_revision( $postarr['ID'] ) ) {
        return $data;
    }

    $content = $data['post_content'];

    // Extract plain text from content.
    $plain_text = haikupress_get_plain_text( $content );

    // Validate the haiku format using plain text.
    $result  = haikupress_validate_haiku( $plain_text );

    if ( is_wp_error( $result ) ) {
        // Set transient to display admin notice.
        set_transient( 'haikupress_admin_notice', $result->get_error_message(), 60 );

        // Revert status to draft.
        $data['post_status'] = 'draft';
    }

    return $data;
}
add_filter( 'wp_insert_post_data', 'haikupress_validate_on_save', 10, 2 );

/**
 * Display an admin notice if haiku validation failed.
 */
function haikupress_admin_notice() {
    if ( $notice = get_transient( 'haikupress_admin_notice' ) ) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $notice ) . '</p></div>';
        delete_transient( 'haikupress_admin_notice' );
    }
}
add_action( 'admin_notices', 'haikupress_admin_notice' );

/**
 * Remove the "Post Draft Updated" notice when draft is due to haiku validation failure.
 *
 * @param string $location The redirect URL.
 * @param int    $post_id  The post ID.
 * @return string Modified redirect URL.
 */
function haikupress_remove_draft_updated_notice( $location, $post_id ) {
    if ( get_transient( 'haikupress_admin_notice' ) ) {
        // Remove the 'message' parameter from the URL.
        $location = remove_query_arg( 'message', $location );
    }
    return $location;
}
add_filter( 'redirect_post_location', 'haikupress_remove_draft_updated_notice', 10, 2 );

/**
 * Extract plain text from Gutenberg blocks.
 *
 * @param string $content The post content with block markup.
 * @return string The extracted plain text content.
 */
function haikupress_get_plain_text( $content ) {
    $blocks = parse_blocks( $content );
    $plain_text = '';

    foreach ( $blocks as $block ) {
        $plain_text .= haikupress_extract_text_from_block( $block );
    }

    // Trim any extra whitespace.
    $plain_text = trim( $plain_text );

    return $plain_text;
}

/**
 * Recursively extract text from a block and its inner blocks.
 *
 * @param array $block The block array.
 * @return string The extracted text.
 */
function haikupress_extract_text_from_block( $block ) {
    $text = '';

    if ( isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) && ! empty( $block['innerBlocks'] ) ) {
        foreach ( $block['innerBlocks'] as $inner_block ) {
            $text .= haikupress_extract_text_from_block( $inner_block );
        }
    } elseif ( isset( $block['innerHTML'] ) ) {
        $text .= wp_strip_all_tags( $block['innerHTML'] ) . "\n";
    } elseif ( isset( $block['innerContent'] ) && is_array( $block['innerContent'] ) ) {
        foreach ( $block['innerContent'] as $content ) {
            $text .= wp_strip_all_tags( $content ) . "\n";
        }
    }

    return $text;
}

/**
 * Validate haiku structure (5-7-5 syllables) and return an error if invalid.
 *
 * @param string $content The plain text content to validate.
 * @return true|WP_Error True if valid, WP_Error if invalid.
 */
function haikupress_validate_haiku( $content ) {
    // Split content into lines.
    $lines = preg_split( '/\r\n|\r|\n/', trim( $content ) );

    // Remove empty lines.
    $lines = array_filter( $lines, 'trim' );

    // Re-index array.
    $lines = array_values( $lines );

    // Check for exactly three lines.
    if ( count( $lines ) !== 3 ) {
        return new WP_Error(
            'haikupress_line_count',
            __( 'Content must contain exactly three lines to be a haiku.', 'haikupress' )
        );
    }

    // Define the required syllable pattern for a haiku (5-7-5).
    $required_syllables = [5, 7, 5];

    // Validate each line's syllable count.
    foreach ( $lines as $index => $line ) {
        $line           = trim( $line );
        $syllable_count = haikupress_count_syllables( $line );

        if ( $syllable_count !== $required_syllables[ $index ] ) {
            return new WP_Error(
                'haikupress_syllable_count',
                sprintf(
                    __( 'Line %1$d must contain %2$d syllables: "%3$s".', 'haikupress' ),
                    $index + 1,
                    $required_syllables[ $index ],
                    esc_html( $line )
                )
            );
        }
    }

    return true;
}

/**
 * Estimate the syllable count of a line.
 *
 * @param string $line The line to count syllables in.
 * @return int Estimated syllable count.
 */
function haikupress_count_syllables( $line ) {
    // Convert line to lowercase for consistent pattern matching.
    $line = strtolower( $line );

    // Remove non-alphabetic characters.
    $line = preg_replace( '/[^a-z]/', '', $line );

    // Handle common diphthongs to avoid overcounting.
    $line = str_replace(
        [
            'aa', 'ae', 'ai', 'ao', 'au',
            'ea', 'ee', 'ei', 'eo', 'eu',
            'ia', 'ie', 'ii', 'io', 'iu',
            'oa', 'oe', 'oi', 'oo', 'ou',
            'ua', 'ue', 'ui', 'uo', 'uu',
        ],
        'a',
        $line
    );

    // Basic estimation based on vowel groups.
    $syllables = preg_match_all( '/[aeiouy]+/', $line, $matches );

    // Subtract one syllable for each silent 'e' at the end of a word.
    $syllables -= preg_match_all( '/[aeiouy]+e\b/', $line );

    // Ensure syllable count is at least 1.
    return max( 1, $syllables );
}
