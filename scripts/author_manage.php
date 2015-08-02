<?php
class Authors {
    public static function find_creator($creator) {
        global $coauthors_plus;

        $args = array(
            'meta_query' => array(
                'relation' => 'AND',
            ),
        );

        if (isset($creator['firstName'])) {
            $args['meta_query'][] = array(
                'key'     => 'first_name',
                'value'   => $creator['firstName'],
                'compare' => 'LIKE'
            );
        }

        $args['meta_query'][] = array(
            'key'     => 'last_name',
            'value'   => $creator['lastName'],
            'compare' => 'LIKE'
        );

        $authors = get_users( $args );

        if (!empty($authors)) {
            return $authors[0];
        } else {
            $display_name = $creator['firstName'] . ' ' . $creator['lastName'];
            $user_login = sanitize_title($display_name);
            $guest = $coauthors_plus->guest_authors->get_guest_author_by('user_login', $user_login);
            return $guest;
        }
    }

    public static function add_guest_author( $creator ) {
        global $coauthors_plus;

        $user_id = null;

        $display_name = $creator['firstName'] . ' ' . $creator['lastName'];
        $user_login = sanitize_title($display_name);
        $args = array(
            'display_name' => $display_name,
            'user_login' => $user_login,
            'first_name' => $creator['firstName'],
            'last_name' => $creator['lastName'],
        );
        
        $user_id = $coauthors_plus->guest_authors->create( $args );
        return $user_login;
    }

    public static function get_or_create_wp_author($creator) {
        $author_nicename = null;

        $author = static::find_creator($creator);
        if (!empty($author)) { // use existing author
            $author_nicename = $author->user_nicename;
        } else { // create a guest author
            $author_nicename = static::add_guest_author( $creator );
        }
        return $author_nicename;
    }

    public static function get_wp_authors_for($item) {
        $authors = array();
        foreach ($item['presenters'] as $creator) {
            $authors[] = static::get_or_create_wp_author($creator);
        }
        return $authors;
    }

    public static function do_add_coauthors($post_id, $post_obj) {
        global $coauthors_plus;

        $authors = static::get_wp_authors_for($post_obj);

        $coauthors_plus->add_coauthors($post_id, $authors);
    }

}