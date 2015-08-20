<?php

/**
 * Implements a command to add events.
 */
class Bios_Command extends WP_CLI_Command {

    /**
     * Change bios to custom field.
     * 
     * ## OPTIONS
     * 
     * None.
     * 
     * ## EXAMPLES
     * 
     *     wp bios convert
     *
     * @synopsis 
     */
    function convert( $args, $assoc_args ) {
        
    
        WP_CLI::success( "$filename was successfully imported." );
    }
}

WP_CLI::add_command( 'bios', 'Events_Command' );
