<?php

namespace PostTypes;

trait TheLoop
{

    public $default_args = [];

    public $defaultTemplate;

    public function setDefaultTemplate($functionalTemplate){
        $this->defaultTemplate = $functionalTemplate;
    }

    public function loop_through_posts($optional_args = []){

        $query = new \WP_Query($this->returnFormattedArgs($optional_args));

        //Retrun a WP Loop for the given query which accepts a callback to be used on all the posts
        //TODO: This could be an issue with storing the query, but with page reloads it would update
        return function ($callback = null) use ($query) {
            while ($query->have_posts()){
                $query->the_post();
                call_user_func_array($callback, [get_the_ID()]);
            }
            //This function because wordpress can't handle it self
            wp_reset_postdata();
            return $query;
        };
    }

    public function loop_through_all($callback = null, $args = []){
        return call_user_func_array($this->loop_through_posts($args), [$callback]);
    }

    public function returnFormattedArgs($optional_args = []){
        $args = array_merge($this->default_args, $optional_args);
        $args['post_type'] = $this->slug;
        return $args;
    }

}