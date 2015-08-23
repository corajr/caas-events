<?php

$args = array(
);

$users = get_users($args);
$out = array();

foreach ($users as $user) {
	$creator = array(
		'firstName' => $user->first_name,
		'lastName' => $user->last_name,
	);
	$out[$creator['lastName']] = $creator;
}

echo json_encode($out);
