<?php

function get_checkbox_possible_values( $meta_id ) {
	$fields = wpcf_admin_fields_get_fields();
	$field = $fields[$meta_id];
	$result = array();
	foreach ($field['data']['options'] as $k=>$v) {
		$title = $v['title'];
		$result[$title] = array($k => $title);
	}
	return $result;
}

if (!debug_backtrace()) {
	$values = get_checkbox_possible_values( 'subfields' );
	var_dump($values);
}
