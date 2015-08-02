<?php
define('EVENT_TYPE_TAXONOMY', 'lecture-series');

function academic_year(DateTime $userDate) {
    $currentYear = $userDate->format('Y');
    $cutoff = new DateTime($userDate->format('Y') . '/08/31 23:59:59');
    if ($userDate < $cutoff) {
        return ($currentYear-1) . '-' . $currentYear;
    }
    return $currentYear . '-' . ($currentYear+1);
}


class Taxonomy {
    public static function get_year($event) {
        $dt = new DateTime();
        $dt->setTimestamp($event['datetime']);
        $year = academic_year($dt);
        return $year;
    }

    public static function get_or_create_terms($event) {
        $event_type_ids = array();
        $year = static::get_year($event);
        $event_type = $event['lecture-series'];

        // Get or add parent term
        $args = array(
            'name__like' => $event_type,
            'hide_empty' => false,
        );
        $terms = get_terms(EVENT_TYPE_TAXONOMY, $args);
        if (!empty($terms)) {
            $parent_ID = $terms[0]->ID;
        } else {
            $parent = wp_insert_term(
                $event_type,
                EVENT_TYPE_TAXONOMY
            );
            if (is_wp_error($parent)) {
                $msg = $parent->get_error_message();
                die(implode("\n", array('parent', $msg, $event_type, $year)) . "\n");
            }
            $parent_ID = $parent['term_id'];
        }

        // Get or add academic-year term
        $child_args = array(
            'parent' => $parent_ID,
            'name__like' => $year,
            'hide_empty' => false,
        );
        $child_terms = get_terms(EVENT_TYPE_TAXONOMY, $child_args);
        if(!empty($child_terms)) {
            $event_type_ids[] = $child_terms[0]->ID;
        } else {
            $term = wp_insert_term(
                $year,
                EVENT_TYPE_TAXONOMY,
                array(
                    'parent' => $parent_ID,
                )
            );
            if (is_wp_error($term)) {
                $msg = $term->get_error_message();
                die(implode("\n", array('child', $msg, $event_type, $year)) . "\n");
            }
            $event_type_ids[] = $term['term_id'];
        }

        return $event_type_ids;
    }

    public static function add_event_type($post_ID, $event) {
        $event_type_ids = static::get_or_create_terms($event);
        wp_set_object_terms($post_ID, $event_type_ids, EVENT_TYPE_TAXONOMY);
    }
}