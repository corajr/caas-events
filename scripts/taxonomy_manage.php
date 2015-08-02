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

    public static function search_name_then_slug($name, $args = array()) {
        $name_args = array(
            'name__like' => $name,
            'get' => 'all',
        );

        $args1 = array_merge($args, $name_args);
        $terms = get_terms(EVENT_TYPE_TAXONOMY, $args1);
        if (is_wp_error($terms)) {
            die_from('search failed', $terms, array($event_type));
        } else if (!empty($terms)) {
            return $terms[0]->term_id;
        } else {
            $slug = array(
                'slug' => sanitize_title($name),
                'get' => 'all',
            );

            $args2 = array_merge($args, $slug);
            $terms = get_terms(EVENT_TYPE_TAXONOMY, $args2);

            if (is_wp_error($terms)) {
                die_from('search failed', $terms, array($event_type));
            } else if (!empty($terms)) {
                return $terms[0]->term_id;
            } else {
                return false;
            }
        }
    }

    public static function get_or_create_terms($event) {
        $event_type_ids = array();
        $year = static::get_year($event);
        $event_type = $event['lecture-series'];

        // Get or add parent term
        $parent_ID = static::search_name_then_slug($event_type);

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
        $child_args = array(
            'parent' => $parent_ID,
        );

        $child_ID = static::search_name_then_slug($year, $child_args);

        if( ! $child_ID) {
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

        $event_type_ids[] = $child_ID;

        return $event_type_ids;
    }

    public static function add_event_type($post_ID, $event) {
        $event_type_ids = static::get_or_create_terms($event);
        wp_set_object_terms($post_ID, $event_type_ids, EVENT_TYPE_TAXONOMY);
    }
}