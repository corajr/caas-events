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
            $posts = get_posts($args);
            if (!empty($posts)) {
                $post_id = $posts[0]->ID;
            } else {
                $post_id = wp_insert_post(array(
                    'post_type' => 'event',
                    'post_title' => $event['title'],
                    'post_content' => $event['description'],
                    'post_status' => 'publish',
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                ));                
            }

            if ($post_id) {
                update_post_meta($post_id, 'wpcf-event-date-time', $event['datetime']);
                update_post_meta($post_id, 'wpcf-location', $event['location']);
                update_post_meta($post_id, 'old-event-id', $event['id']);
                Authors::do_add_coauthors($post_id, $event);
                Taxonomy::add_event_type($post_id, $event);
            }
        }
    
        WP_CLI::success( "$filename was successfully imported." );
    }
}

WP_CLI::add_command( 'events', 'Events_Command' );
