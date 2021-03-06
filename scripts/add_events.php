<?php

require_once("author_manage.php");
require_once("taxonomy_manage.php");

/**
 * Implements a command to add events.
 */
class Events_Command extends WP_CLI_Command {

	/**
	 * Adds events to the database.
	 *
	 * ## OPTIONS
	 *
	 * <json>
	 * : The name of the JSON file to import.
	 *
	 * ## EXAMPLES
	 *
	 *     wp events add events.json
	 *
	 * @synopsis <json>
	 */
	function add( $args, $assoc_args ) {
		list( $filename ) = $args;
		$js_str = file_get_contents($filename);
		$json_a = json_decode($js_str, true);

		foreach ($json_a as $event) {
			$args = array( 'meta_key' => 'old-event-id',
						   'meta_value' => $event['id'],
						   'post_type' => 'event',
			);

			$existed = false;
			$should_delete = $event['action'] == 'delete';
			$posts = get_posts($args);
			if (!empty($posts)) {
				$post_id = $posts[0]->ID;
				$existed = true;
			} else if (!$should_delete) {
				$post_id = wp_insert_post(array(
					'post_type' => 'event',
					'post_title' => $event['title'],
					'post_excerpt' => $event['dek'],
					'post_content' => $event['description'],
					'post_status' => 'publish',
					'comment_status' => 'closed',
					'ping_status' => 'closed',
				));
			}

			if ($post_id) {
				if ($existed && $should_delete) {
					wp_delete_post($post_id);
				} else {
					update_post_meta($post_id, 'wpcf-event-date-time', $event['datetime']);
					update_post_meta($post_id, 'wpcf-location', $event['location']);
					update_post_meta($post_id, 'old-event-id', $event['id']);
					if ($existed) {
						$updating = array(
							'ID' => $post_id,
								'post_title' => $event['title'],
								'post_excerpt' => $event['dek'],
						);
						wp_update_post($updating);
					}
					Authors::do_add_coauthors($post_id, $event);
					Taxonomy::add_event_type($post_id, $event);
				}
			}
		}

		WP_CLI::success( "$filename was successfully imported." );
	}
}

WP_CLI::add_command( 'events', 'Events_Command' );
