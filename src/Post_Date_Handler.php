<?php
namespace PostTypes;


trait Post_Date_Handler {
    /**
     * An Array of date fields and corrisponding post_meta key
     * 
     * Available date fields
     * archive
     *
     * @var
     */
    private $dates;

    function init_dates($dates) {
        $this->dates = $dates;
        foreach ($this->dates as $date => $meta_key){
            if ($date === 'archive') {
                $this->init_archive();
            }
        }
    }

    /* Add the appropriate functions to the wp on save and schedule job */
    //TODO: Figure out why 'save_post_event' is not firing does it have something to do with the class?
    public function init_archive(){
        add_action('save_post', array($this, 'update_is_upcoming'));
        
        add_action('check_upcoming_is_post_upcoming', array($this, 'check_upcoming_is_post_upcoming'));
        if ( wp_next_scheduled( 'check_upcoming_is_post_upcoming' ) === false ) {
            wp_schedule_event( time(), 'minute', 'check_upcoming_is_post_upcoming' );
        }
    }

    /* The function which is used to update the post on save 
     * we have to have this wrapper because of save_post vs save_post_event see above
     */    
    public function update_is_upcoming($post_id){
        global $post;
        if ($post->post_type == $this->slug) {
            $this->is_post_upcoming($post_id);
        }
    }

    /* Loops through all the posts and calls is_post_upcoming on each post */
    function check_all_is_post_upcoming(){
        $this->loop_through_all(array($this, 'is_post_upcoming'));
    }

    /* creates a loop for all upcoming posts */
    public function loop_through_upcoming_posts($callback){
        return $this->loop_through_all($callback, array("meta_key" => "_event_upcoming_label", "meta_value" => "Upcoming"));
        
    }

    /* creates a loop for all past posts */
    public function loop_through_past_posts($callback){
        return call_user_func_array($this->loop_through_posts(array("posts_per_page"=> -1, "meta_key" => "_event_upcoming_label", "meta_value" => "Past")), [$callback]);
    }

    /* Passes the is_post_upcoming function to only the upcoming posts loop */
    function check_upcoming_is_post_upcoming(){
        $this->loop_through_upcoming_posts(array($this, 'is_post_upcoming'));
    }
    
    /* check an individual post if the archive date is today or earlier, 
     * if it is it sets the post_meta field _is_current to 0 
     *  it also sets the post meta field _event_upcoming_label to 'Upcoming' or 'Past'
     */
    public function is_post_upcoming($post_id = false) {
        $post_id = (!empty($post_id)) ? $post_id : get_the_ID();        
        //In order to do this check we need both a post, and an archive date field
        if (empty($post_id) || empty($this->dates['archive'])) {
            return;
        }
        
        //Get the archived date
        $archive_date = get_post_meta($post_id, $this->dates['archive']);

        //Get todays date to compare against
        $today = date('Ymd');
        
        //If we do not have an info in the archive_date post meta, stop
        if (empty($archive_date)) {
            return;
        }
        
        //Update the archive based on the inequalitys
        $post_meta_value = (intval($today) < intval($archive_date[0])) ? "Upcoming" : "Past";
        $oldMeta = get_post_meta($post_id, "_event_upcoming_label");
        update_post_meta($post_id, '_is_upcoming', intval($today) < intval($archive_date[0]));
        //This post meta field is specifically for facets.
        //TODO: Could just be the only one?
        $didUpdate = update_post_meta($post_id, '_event_upcoming_label', $post_meta_value);
        if ($didUpdate) {

            add_action('init', function() use($post_id)
            {
                \FWP()->indexer->index( $post_id );            
            });
        }
    }
}