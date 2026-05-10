<?php
/**
 * Shared item collection helpers for Telkari.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize an item collection via a dedicated item callback.
 *
 * This helper normalizes the final order after invalid items are removed so
 * stored collections remain compact and deterministic.
 *
 * @param array    $items             Raw item collection.
 * @param callable $sanitize_callback Callback used to sanitize each item.
 * @return array
 */
function telkari_sanitize_collection_items( $items, $sanitize_callback ) {
	if ( ! is_array( $items ) || ! is_callable( $sanitize_callback ) ) {
		return array();
	}

	$sanitized = array();

	foreach ( $items as $item ) {
		$clean_item = call_user_func( $sanitize_callback, $item );

		if ( is_array( $clean_item ) ) {
			$sanitized[] = $clean_item;
		}
	}

	return telkari_normalize_collection_item_order( $sanitized );
}

/**
 * Normalize collection order to a sequential zero-based list.
 *
 * @param array $items Collection items.
 * @return array
 */
function telkari_normalize_collection_item_order( $items ) {
	if ( ! is_array( $items ) ) {
		return array();
	}

	$normalized = array_values(
		array_filter(
			$items,
			function ( $item ) {
				return is_array( $item );
			}
		)
	);

	$normalized = telkari_sort_collection_items( $normalized );

	foreach ( $normalized as $index => $item ) {
		$normalized[ $index ]['order'] = $index;
	}

	return $normalized;
}

/**
 * Sort collection items by their order key.
 *
 * @param array $items Collection items.
 * @return array
 */
function telkari_sort_collection_items( $items ) {
	if ( ! is_array( $items ) ) {
		return array();
	}

	$sorted_items = array_values(
		array_filter(
			$items,
			function ( $item ) {
				return is_array( $item );
			}
		)
	);

	usort(
		$sorted_items,
		function ( $left_item, $right_item ) {
			$left_order  = isset( $left_item['order'] ) ? absint( $left_item['order'] ) : 0;
			$right_order = isset( $right_item['order'] ) ? absint( $right_item['order'] ) : 0;

			return $left_order <=> $right_order;
		}
	);

	return $sorted_items;
}

/**
 * Return enabled, sorted collection items.
 *
 * @param array $items         Collection items.
 * @param array $required_keys Required non-empty keys for each item.
 * @return array
 */
function telkari_get_enabled_sorted_collection_items( $items, $required_keys = array() ) {
	if ( ! is_array( $items ) ) {
		return array();
	}

	$enabled_items = array_filter(
		$items,
		function ( $item ) use ( $required_keys ) {
			if ( ! is_array( $item ) || empty( $item['enabled'] ) ) {
				return false;
			}

			foreach ( $required_keys as $required_key ) {
				if ( empty( $item[ $required_key ] ) ) {
					return false;
				}
			}

			return true;
		}
	);

	return telkari_sort_collection_items( $enabled_items );
}

/**
 * Return link target attributes for frontend links.
 *
 * @param string $link_target Configured link target.
 * @return array
 */
function telkari_get_link_target_attributes( $link_target ) {
	if ( '_blank' !== $link_target ) {
		return array();
	}

	return array(
		'target' => '_blank',
		'rel'    => 'noopener noreferrer',
	);
}
