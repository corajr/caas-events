<?php
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
            $post_id = wp_insert_post(array(
                'post_type' => 'event',
                'post_title' => $event['title'],
                'post_content' => $event['content'],
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ));
            if ($post_id) {
                add_post_meta($post_id, 'wpcf-event-date-time', array($event['datetime']));
                add_post_meta($post_id, 'wpcf-location', array($event['location']));
            }
        }
    
        WP_CLI::success( "$filename was successfully imported." );
    }
}

WP_CLI::add_command( 'events', 'Events_Command' );