<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Wordpress alternatives
 * update_post_meta() strip slashes and it's impossible to save json or "\'" in post meta
 * https://core.trac.wordpress.org/ticket/21767
 */

/**
 * Add metadata for the specified object.
 *
 * @uses $wpdb WordPress database object for queries.
 *
 * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
 * @param int $object_id ID of the object metadata is for
 * @param string $meta_key Metadata key
 * @param mixed $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool $unique Optional, default is false. Whether the specified metadata key should be
 *        unique for the object. If true, and the object already has a value for the specified
 *        metadata key, no change will be made
 *
 * @return int|bool The meta ID on success, false on failure.
 */
function fw_add_metadata( $meta_type, $object_id, $meta_key, $meta_value, $unique = false ) {

	/**
	 * @var WPDB $wpdb
	 */
	global $wpdb;

	if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) {
		return false;
	}

	$object_id = absint( $object_id );
	if ( ! $object_id ) {
		return false;
	}

	$table = _get_meta_table( $meta_type );
	if ( ! $table ) {
		return false;
	}

	$column = sanitize_key( $meta_type . '_id' );

	// expected_slashed ($meta_key)
	//$meta_key = wp_unslash($meta_key);
	//$meta_value = wp_unslash($meta_value);
	$meta_value = sanitize_meta( $meta_key, $meta_value, $meta_type );

	/**
	 * Filter whether to add metadata of a specific type.
	 *
	 * The dynamic portion of the hook, $meta_type, refers to the meta
	 * object type (comment, post, or user). Returning a non-null value
	 * will effectively short-circuit the function.
	 *
	 * @param null|bool $check Whether to allow adding metadata for the given type.
	 * @param int $object_id Object ID.
	 * @param string $meta_key Meta key.
	 * @param mixed $meta_value Meta value. Must be serializable if non-scalar.
	 * @param bool $unique Whether the specified meta key should be unique
	 *                              for the object. Optional. Default false.
	 */
	$check = apply_filters( "add_{$meta_type}_metadata", null, $object_id, $meta_key, $meta_value, $unique );
	if ( null !== $check ) {
		return $check;
	}

	if ( $unique && $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE meta_key = %s AND $column = %d LIMIT 1",
			$meta_key, $object_id ) )
	) {
		return false;
	}

	$_meta_value = $meta_value;
	$meta_value  = maybe_serialize( $meta_value );

	/**
	 * Fires immediately before meta of a specific type is added.
	 *
	 * The dynamic portion of the hook, $meta_type, refers to the meta
	 * object type (comment, post, or user).
	 *
	 * @param int $object_id Object ID.
	 * @param string $meta_key Meta key.
	 * @param mixed $meta_value Meta value.
	 */
	do_action( "add_{$meta_type}_meta", $object_id, $meta_key, $_meta_value );

	$result = $wpdb->insert( $table, array(
		$column      => $object_id,
		'meta_key'   => $meta_key,
		'meta_value' => $meta_value,
	) );
	if ( ! $result ) {
		return false;
	}

	$mid = (int) $wpdb->insert_id;

	wp_cache_delete( $object_id, $meta_type . '_meta' );

	/**
	 * Fires immediately after meta of a specific type is added.
	 *
	 * The dynamic portion of the hook, $meta_type, refers to the meta
	 * object type (comment, post, or user).
	 *
	 * @param int $mid The meta ID after successful update.
	 * @param int $object_id Object ID.
	 * @param string $meta_key Meta key.
	 * @param mixed $meta_value Meta value.
	 */
	do_action( "added_{$meta_type}_meta", $mid, $object_id, $meta_key, $_meta_value );

	return $mid;
}

/**
 * Update metadata for the specified object. If no value already exists for the specified object
 * ID and metadata key, the metadata will be added.
 *
 * @uses $wpdb WordPress database object for queries.
 *
 * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
 * @param int $object_id ID of the object metadata is for
 * @param string $meta_key Metadata key
 * @param mixed $meta_value Metadata value. Must be serializable if non-scalar.
 * @param mixed $prev_value Optional. If specified, only update existing metadata entries with
 *        the specified value. Otherwise, update all entries.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function fw_update_metadata( $meta_type, $object_id, $meta_key, $meta_value, $prev_value = '' ) {
	global $wpdb;

	if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) {
		return false;
	}

	$object_id = absint( $object_id );
	if ( ! $object_id ) {
		return false;
	}

	$table = _get_meta_table( $meta_type );
	if ( ! $table ) {
		return false;
	}

	$column    = sanitize_key( $meta_type . '_id' );
	$id_column = 'user' == $meta_type ? 'umeta_id' : 'meta_id';

	// expected_slashed ($meta_key)
	//$meta_key = wp_unslash($meta_key);
	$passed_value = $meta_value;
	//$meta_value = wp_unslash($meta_value);
	$meta_value = sanitize_meta( $meta_key, $meta_value, $meta_type );

	/**
	 * Filter whether to update metadata of a specific type.
	 *
	 * The dynamic portion of the hook, $meta_type, refers to the meta
	 * object type (comment, post, or user). Returning a non-null value
	 * will effectively short-circuit the function.
	 *
	 * @param null|bool $check Whether to allow updating metadata for the given type.
	 * @param int $object_id Object ID.
	 * @param string $meta_key Meta key.
	 * @param mixed $meta_value Meta value. Must be serializable if non-scalar.
	 * @param mixed $prev_value Optional. If specified, only update existing
	 *                              metadata entries with the specified value.
	 *                              Otherwise, update all entries.
	 */
	$check = apply_filters( "update_{$meta_type}_metadata", null, $object_id, $meta_key, $meta_value, $prev_value );
	if ( null !== $check ) {
		return (bool) $check;
	}

	// Compare existing value to new value if no prev value given and the key exists only once.
	if ( empty( $prev_value ) ) {
		$old_value = get_metadata( $meta_type, $object_id, $meta_key );
		if ( count( $old_value ) == 1 ) {
			if ( $old_value[0] === $meta_value ) {
				return false;
			}
		}
	}

	if ( ! $meta_id = $wpdb->get_var( $wpdb->prepare( "SELECT $id_column FROM $table WHERE meta_key = %s AND $column = %d LIMIT 1", $meta_key, $object_id ) ) ) {
		return fw_add_metadata( $meta_type, $object_id, $meta_key, $passed_value );
	}

	$_meta_value = $meta_value;
	$meta_value  = maybe_serialize( $meta_value );

	$data  = compact( 'meta_value' );
	$where = array( $column => $object_id, 'meta_key' => $meta_key );

	if ( ! empty( $prev_value ) ) {
		$prev_value          = maybe_serialize( $prev_value );
		$where['meta_value'] = $prev_value;
	}

	/**
	 * Fires immediately before updating metadata of a specific type.
	 *
	 * The dynamic portion of the hook, $meta_type, refers to the meta
	 * object type (comment, post, or user).
	 *
	 * @param int $meta_id ID of the metadata entry to update.
	 * @param int $object_id Object ID.
	 * @param string $meta_key Meta key.
	 * @param mixed $meta_value Meta value.
	 */
	do_action( "update_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );

	if ( 'post' == $meta_type ) {
		/**
		 * Fires immediately before updating a post's metadata.
		 *
		 * @param int $meta_id ID of metadata entry to update.
		 * @param int $object_id Object ID.
		 * @param string $meta_key Meta key.
		 * @param mixed $meta_value Meta value.
		 */
		do_action( 'update_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
	}

	$result = $wpdb->update( $table, $data, $where );
	if ( ! $result ) {
		return false;
	}

	wp_cache_delete( $object_id, $meta_type . '_meta' );

	/**
	 * Fires immediately after updating metadata of a specific type.
	 *
	 * The dynamic portion of the hook, $meta_type, refers to the meta
	 * object type (comment, post, or user).
	 *
	 * @param int $meta_id ID of updated metadata entry.
	 * @param int $object_id Object ID.
	 * @param string $meta_key Meta key.
	 * @param mixed $meta_value Meta value.
	 */
	do_action( "updated_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );

	if ( 'post' == $meta_type ) {
		/**
		 * Fires immediately after updating a post's metadata.
		 *
		 * @param int $meta_id ID of updated metadata entry.
		 * @param int $object_id Object ID.
		 * @param string $meta_key Meta key.
		 * @param mixed $meta_value Meta value.
		 */
		do_action( 'updated_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
	}

	return true;
}

;

/**
 * Delete metadata for the specified object.
 *
 * @uses $wpdb WordPress database object for queries.
 *
 * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
 * @param int $object_id ID of the object metadata is for
 * @param string $meta_key Metadata key
 * @param mixed $meta_value Optional. Metadata value. Must be serializable if non-scalar. If specified, only delete metadata entries
 *        with this value. Otherwise, delete all entries with the specified meta_key.
 * @param bool $delete_all Optional, default is false. If true, delete matching metadata entries
 *        for all objects, ignoring the specified object_id. Otherwise, only delete matching
 *        metadata entries for the specified object_id.
 *
 * @return bool True on successful delete, false on failure.
 */
function fw_delete_metadata( $meta_type, $object_id, $meta_key, $meta_value = '', $delete_all = false ) {
	/**
	 * @var WPDB $wpdb
	 */
	global $wpdb;

	if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) && ! $delete_all ) {
		return false;
	}

	$object_id = absint( $object_id );
	if ( ! $object_id && ! $delete_all ) {
		return false;
	}

	$table = _get_meta_table( $meta_type );
	if ( ! $table ) {
		return false;
	}

	$type_column = sanitize_key( $meta_type . '_id' );
	$id_column   = 'user' == $meta_type ? 'umeta_id' : 'meta_id';
	// expected_slashed ($meta_key)
	//$meta_key = wp_unslash($meta_key);
	//$meta_value = wp_unslash($meta_value);

	/**
	 * Filter whether to delete metadata of a specific type.
	 *
	 * The dynamic portion of the hook, $meta_type, refers to the meta
	 * object type (comment, post, or user). Returning a non-null value
	 * will effectively short-circuit the function.
	 *
	 * @param null|bool $delete Whether to allow metadata deletion of the given type.
	 * @param int $object_id Object ID.
	 * @param string $meta_key Meta key.
	 * @param mixed $meta_value Meta value. Must be serializable if non-scalar.
	 * @param bool $delete_all Whether to delete the matching metadata entries
	 *                              for all objects, ignoring the specified $object_id.
	 *                              Default false.
	 */
	$check = apply_filters( "delete_{$meta_type}_metadata", null, $object_id, $meta_key, $meta_value, $delete_all );
	if ( null !== $check ) {
		return (bool) $check;
	}

	$_meta_value = $meta_value;
	$meta_value  = maybe_serialize( $meta_value );

	$query = $wpdb->prepare( "SELECT $id_column FROM $table WHERE meta_key = %s", $meta_key );

	if ( ! $delete_all ) {
		$query .= $wpdb->prepare( " AND $type_column = %d", $object_id );
	}

	if ( $meta_value ) {
		$query .= $wpdb->prepare( " AND meta_value = %s", $meta_value );
	}

	$meta_ids = $wpdb->get_col( $query );
	if ( ! count( $meta_ids ) ) {
		return false;
	}

	if ( $delete_all ) {
		$object_ids = $wpdb->get_col( $wpdb->prepare( "SELECT $type_column FROM $table WHERE meta_key = %s", $meta_key ) );
	}

	/**
	 * Fires immediately before deleting metadata of a specific type.
	 *
	 * The dynamic portion of the hook, $meta_type, refers to the meta
	 * object type (comment, post, or user).
	 *
	 * @param array $meta_ids An array of metadata entry IDs to delete.
	 * @param int $object_id Object ID.
	 * @param string $meta_key Meta key.
	 * @param mixed $meta_value Meta value.
	 */
	do_action( "delete_{$meta_type}_meta", $meta_ids, $object_id, $meta_key, $_meta_value );

	// Old-style action.
	if ( 'post' == $meta_type ) {
		/**
		 * Fires immediately before deleting metadata for a post.
		 *
		 * @param array $meta_ids An array of post metadata entry IDs to delete.
		 */
		do_action( 'delete_postmeta', $meta_ids );
	}

	$query = "DELETE FROM $table WHERE $id_column IN( " . implode( ',', $meta_ids ) . " )";

	$count = $wpdb->query( $query );

	if ( ! $count ) {
		return false;
	}

	if ( $delete_all ) {
		foreach ( (array) $object_ids as $o_id ) {
			wp_cache_delete( $o_id, $meta_type . '_meta' );
		}
	} else {
		wp_cache_delete( $object_id, $meta_type . '_meta' );
	}

	/**
	 * Fires immediately after deleting metadata of a specific type.
	 *
	 * The dynamic portion of the hook name, $meta_type, refers to the meta
	 * object type (comment, post, or user).
	 *
	 * @param array $meta_ids An array of deleted metadata entry IDs.
	 * @param int $object_id Object ID.
	 * @param string $meta_key Meta key.
	 * @param mixed $meta_value Meta value.
	 */
	do_action( "deleted_{$meta_type}_meta", $meta_ids, $object_id, $meta_key, $_meta_value );

	// Old-style action.
	if ( 'post' == $meta_type ) {
		/**
		 * Fires immediately after deleting metadata for a post.
		 *
		 * @param array $meta_ids An array of deleted post metadata entry IDs.
		 */
		do_action( 'deleted_postmeta', $meta_ids );
	}

	return true;
}

/**
 * Add meta data field to a user.
 *
 * Post meta data is called "Custom Fields" on the Administration Screens.
 *
 * @param int $user_id User ID.
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Metadata value.
 * @param bool $unique Optional, default is false. Whether the same key should not be added.
 *
 * @return int|bool Meta ID on success, false on failure.
 */
function fw_add_user_meta( $user_id, $meta_key, $meta_value, $unique = false ) {
	return fw_add_metadata( 'user', $user_id, $meta_key, $meta_value, $unique );
}

/**
 * Update user meta field based on user ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and user ID.
 *
 * If the meta field for the user does not exist, it will be added.
 *
 * @param int $user_id User ID.
 * @param string $meta_key Metadata key.
 * @param mixed $meta_value Metadata value.
 * @param mixed $prev_value Optional. Previous value to check before removing.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function fw_update_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ) {
	return fw_update_metadata( 'user', $user_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Remove metadata matching criteria from a user.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @param int $user_id user ID
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Optional. Metadata value.
 *
 * @return bool True on success, false on failure.
 */
function fw_delete_user_meta( $user_id, $meta_key, $meta_value = '' ) {
	return fw_delete_metadata( 'user', $user_id, $meta_key, $meta_value );
}

/**
 * Add meta data field to a post.
 *
 * Post meta data is called "Custom Fields" on the Administration Screen.
 *
 * @param int $post_id Post ID.
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool $unique Optional. Whether the same key should not be added.
 *                           Default false.
 *
 * @return int|bool Meta ID on success, false on failure.
 */
function fw_add_post_meta( $post_id, $meta_key, $meta_value, $unique = false ) {
	// Make sure meta is added to the post, not a revision. // fixme: why this is needed?
	/*if ( $the_post = wp_is_post_revision( $post_id ) ) {
		$post_id = $the_post;
	}*/

	return fw_add_metadata( 'post', $post_id, $meta_key, $meta_value, $unique );
}

/**
 * Update post meta field based on post ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and post ID.
 *
 * If the meta field for the post does not exist, it will be added.
 *
 * @param int $post_id Post ID.
 * @param string $meta_key Metadata key.
 * @param mixed $meta_value Metadata value. Must be serializable if non-scalar.
 * @param mixed $prev_value Optional. Previous value to check before removing.
 *                           Default empty.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function fw_update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ) {
	// Make sure meta is added to the post, not a revision. fixme: why this is needed?
	/*if ( $the_post = wp_is_post_revision( $post_id ) ) {
		$post_id = $the_post;
	}*/

	return fw_update_metadata( 'post', $post_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Remove metadata matching criteria from a post.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @param int $post_id Post ID.
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Optional. Metadata value. Must be serializable if
 *                           non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function fw_delete_post_meta( $post_id, $meta_key, $meta_value = '' ) {
	// Make sure meta is added to the post, not a revision. // fixme: why this is needed?
	/*if ( $the_post = wp_is_post_revision( $post_id ) ) {
		$post_id = $the_post;
	}*/

	return delete_metadata( 'post', $post_id, $meta_key, $meta_value );
}

//
// Comment meta functions
//

/**
 * Add meta data field to a comment.
 *
 * @param int $comment_id Comment ID.
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Metadata value.
 * @param bool $unique Optional, default is false. Whether the same key should not be added.
 *
 * @return int|bool Meta ID on success, false on failure.
 */
function fw_add_comment_meta( $comment_id, $meta_key, $meta_value, $unique = false ) {
	return fw_add_metadata( 'comment', $comment_id, $meta_key, $meta_value, $unique );
}

/**
 * Update comment meta field based on comment ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and comment ID.
 *
 * If the meta field for the comment does not exist, it will be added.
 *
 * @param int $comment_id Comment ID.
 * @param string $meta_key Metadata key.
 * @param mixed $meta_value Metadata value.
 * @param mixed $prev_value Optional. Previous value to check before removing.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function fw_update_comment_meta( $comment_id, $meta_key, $meta_value, $prev_value = '' ) {
	return fw_update_metadata( 'comment', $comment_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Remove metadata matching criteria from a comment.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @param int $comment_id comment ID
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Optional. Metadata value.
 *
 * @return bool True on success, false on failure.
 */
function fw_delete_comment_meta( $comment_id, $meta_key, $meta_value = '' ) {
	return fw_delete_metadata( 'comment', $comment_id, $meta_key, $meta_value );
}

//
// Term meta functions
//http://core.trac.wordpress.org/ticket/10142
//
/**
 * Add meta data field to a term.
 *
 * @param int $term_id Post ID.
 * @param string $key Metadata name.
 * @param mixed $value Metadata value.
 * @param bool $unique Optional, default is false. Whether the same key should not be added.
 * @return bool False for failure. True for success.
 */
function fw_add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
	return fw_add_metadata( 'fw_term', $term_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove metadata matching criteria from a term.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @param int $term_id term ID
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Optional. Metadata value.
 *
 * @return bool False for failure. True for success.
 */
function fw_delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {
	return fw_delete_metadata( 'fw_term', $term_id, $meta_key, $meta_value );
}

/**
 * Retrieve term meta field for a term.
 *
 * @param int $term_id Term ID.
 * @param string $key The meta key to retrieve.
 * @param bool $single Whether to return a single value.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
 *  is true.
 */
function fw_get_term_meta( $term_id, $key, $single = false ) {
	return get_metadata( 'fw_term', $term_id, $key, $single );
}

/**
 * Update term meta field based on term ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and term ID.
 *
 * If the meta field for the term does not exist, it will be added.
 *
 * @param int $term_id Term ID.
 * @param string $key Metadata key.
 * @param mixed $value Metadata value.
 * @param mixed $prev_value Optional. Previous value to check before removing.
 *
 * @return bool False on failure, true if success.
 */
function fw_update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
	return fw_update_metadata( 'fw_term', $term_id, $meta_key, $meta_value, $prev_value );
}