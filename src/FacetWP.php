<?php

namespace PostTypes;

trait FacetWP
{

    /**
     * A temparary array for storing the submenu before its registered by wp
     * TODO: Find a better way to do this
     * @var array
     *
     */

    public function initFacetWP(){
        $this->facetwp = true;
        $this->default_args['facetwp'] = true;
        add_filter( 'facetwp_is_main_query', array($this, 'is_main_query'), 10 , 2);
    }

    public function is_main_query( $is_main_query, $query ) {
        if ( isset( $query->query_vars['facetwp'] ) ) {
            $is_main_query = (bool) $query->query_vars['facetwp'];
        }
        return $is_main_query;
    }

}