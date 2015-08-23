<?php

require_once("author_manage.php");
require_once("custom_fields.php");

/**
 * Implements a command to add events.
 */
class Courses_Command extends WP_CLI_Command {

	/**
	 * Adds courses to the database.
	 *
	 * ## OPTIONS
	 *
	 * <json>
	 * : The name of the JSON file to import.
	 *
	 * ## EXAMPLES
	 *
	 *     wp courses add courses.json
	 *
	 * @synopsis <json>
	 */
	function add( $args, $assoc_args ) {
		list( $filename ) = $args;
		$js_str = file_get_contents($filename);
		$json_a = json_decode($js_str, true);

		$subfields = get_checkbox_possible_values('subfields');
		$programs = get_checkbox_possible_values('program');

		foreach ($json_a as $course) {
			$args = array(
				'name' => $course['slug'],
				'post_type' => 'course',
			);

			$course_numbers = implode(' / ', $course['courseNumber']);

			$existed = false;
			$posts = get_posts($args);
			if (!empty($posts)) {
				$post_id = $posts[0]->ID;
				$existed = true;
			} else {
				$post_id = wp_insert_post(array(
					'post_type' => 'course',
					'post_name' => $course['slug'],
					'post_title' => $course['Course Title'],
					'post_content' => $course['Course Description'],
					'post_status' => 'publish',
					'comment_status' => 'closed',
					'ping_status' => 'closed',
				));
			}

			if ($post_id) {
				update_post_meta($post_id, 'wpcf-course-number', $course_numbers);
				if ($course['Dist Area']) {
					update_post_meta($post_id, 'wpcf-distribution-area', $course['Dist Area']);
				}
				if ($course['Course Status']) {
					update_post_meta($post_id, 'wpcf-course-status', $course['Course Status']);
				}
				if ($course['Program']) {
					$program = $programs[$course['Program']];
					if ($program) {
						update_post_meta($post_id, 'wpcf-program', $program);
					}
				}
				if ($course['Subfield']) {
					$subfield = $subfields[$course['Subfield']];
					if ($subfield) {
						update_post_meta($post_id, 'wpcf-subfields', $subfield);
					}
				}
				if ($existed) {
					$updating = array(
						'ID' => $post_id,
						'post_title' => $course['Course Title'],
					);
					wp_update_post($updating);
				}
				Authors::do_add_coauthors($post_id, $course);
			}
		}

		WP_CLI::success( "$filename was successfully imported." );
	}
}

WP_CLI::add_command( 'courses', 'Courses_Command' );
