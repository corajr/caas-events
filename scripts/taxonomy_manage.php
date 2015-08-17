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

function die_from($msg, WP_Error $error, $rest = array()) {
    $err = $error->get_error_message();
    $rest_txt = print_r($rest, true);
    die(implode("\n", array($msg, $err, $rest_txt)) . "\n");
}

class Taxonomy {
    public static function get_year($event) {
        $dt = new DateTime();
        $dt->setTimestamp($event['datetime']);
        $year = academic_year($dt);
        return $year;
    }

    public static function find_term($name, $parent = 0) {
        $term = term_exists($name, EVENT_TYPE_TAXONOMY, $parent);
        return $term ? $term['term_id'] : false;
    }

    public static function get_or_create_terms($event) {
        $event_type_ids = array();
        $year = static::get_year($event);
        $event_type = $event['lecture-series'];

        if (empty($event_type)) {
            return array();
        }

        // Get or add parent term
        $parent_ID = static::find_term($event_type);

        if ( ! $parent_ID ) {
            $parent = wp_insert_term(
                $event_type,
                EVENT_TYPE_TAXONOMY
            );
            if (is_wp_error($parent)) {
                die_from(
                    "parent couldn't insert", 
                    $parent, 
                    array($event_type, $year)
                ); 
            }
            $parent_ID = $parent['term_id'];
        }

        $parent_ID = intval($parent_ID);

        // Get or add academic-year term
        // $child_ID = static::find_term($year, $parent_ID);

        /* if( ! $child_ID) {
            $term = wp_insert_term(
                $year,
                EVENT_TYPE_TAXONOMY,
                array(
                    'parent' => $parent_ID,
                )
            );
            if (is_wp_error($term)) {
                die_from(
                    "child couldn't insert",
                    $term,
                    array($event_type, $year)
                );
            }
            $child_ID = $term['term_id'];
        }
        $child_ID = intval($child_ID);
    */
        $event_type_ids[] = $parent_ID;

        return $event_type_ids;
    }

    public static function add_event_type($post_ID, $event) {
        $event_type_ids = static::get_or_create_terms($event);
        wp_set_object_terms($post_ID, $event_type_ids, EVENT_TYPE_TAXONOMY);
    }
}
