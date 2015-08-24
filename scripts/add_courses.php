<?php

require_once("author_manage.php");
require_once("custom_fields.php");

/**
 * Implements a command to add events.
 */
class Courses_Command extends WP_CLI_Command {

	private $checkboxes = array();

	private function update_field($post_id, $course, $row_name, $field_name) {
		if ($course[$row_name]) {
			update_post_meta($post_id, 'wpcf-' . $field_name, $course[$row_name]);
		}
	}

	private function update_checkbox($post_id, $course, $row_name, $field_name) {
		if ($course[$row_name]) {
			$box = $this->checkboxes[$field_name][$course[$row_name]];
			if ($box) {
				update_post_meta($post_id, 'wpcf-' . $field_name, $box);
			}
		}
	}

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

		$this->checkboxes['subfields'] = get_checkbox_possible_values('subfields');
		$this->checkboxes['program'] = get_checkbox_possible_values('program');
		$this->checkboxes['semester'] = get_checkbox_possible_values('semester');

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
				$this->update_field($post_id, $course, 'Dist Area', 'distribution-area');
				$this->update_field($post_id, $course, 'Course Status', 'course-status');
				$this->update_field($post_id, $course, 'Lecture', 'lecture-time');
				$this->update_field($post_id, $course, 'Precept', 'precept-time');
				$this->update_field($post_id, $course, 'Year', 'academic-year');
				$this->update_checkbox($post_id, $course, 'Semester', 'semester');
				$this->update_checkbox($post_id, $course, 'Program', 'program');
				$this->update_checkbox($post_id, $course, 'Subfield', 'subfields');

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
