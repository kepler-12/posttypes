<?php
namespace PostTypes;

trait PostDate
{
    /**
     * An Array of date fields and corrisponding post_meta key
     *
     * Available date fields
     * archive
     *
     * @var
     */
    private $dates;

    function initDates($dates)
    {
        $this->dates = $dates;
        foreach ($this->dates as $date => $meta_key) {
            if ($date === 'archive') {
                $this->init_archive();
            }
        }
    }

    /* Add the appropriate functions to the wp on save and schedule job */
    public function initDateArchive()
    {
        add_action('save_post', array($this, 'update_is_upcoming'));
        add_action('checkIfUpcoming', array($this, 'checkIfUpcoming'));

        if (wp_next_scheduled('checkIfUpcoming') === false) {
            wp_schedule_event(time(), 'daily', 'checkIfUpcoming');
        }
    }

    /* The function which is used to update the post on save
     * we have to have this wrapper because of save_post vs save_post_event see above
     */
    public function updateIsUpcoming($post_id)
    {
        global $post;
        if ($post->post_type == $this->slug) {
            $this->is_post_upcoming($post_id);
        }
    }

    /* Loops through all the posts and calls is_post_upcoming on each post */
    public function checkAllISPostUpcoming()
    {
        $this->loop_through_all(array($this, 'is_post_upcoming'));
    }

    /* creates a loop for all upcoming posts */
    public function loopThroughUpcomingPosts($callback)
    {
        return call_user_func_array($this->loop(
            array("meta_key" => "_event_upcoming_label", "meta_value" => "Upcoming")
        ), [$callback]);
    }

    /* creates a loop for all past posts */
    public function loopThroughPastPosts($callback)
    {
        return call_user_func_array($this->loop(
            array("meta_key" => "_event_upcoming_label", "meta_value" => "Past")
        ), [$callback]);
    }

    /* Passes the is_post_upcoming function to only the upcoming posts loop */
    public function checkIfUpcoming()
    {
        $this->loopThroughUpcomingPosts(array($this, 'is_post_upcoming'));
    }

    /* check an individual post if the archive date is today or earlier,
     * if it is it sets the post_meta field _is_current to 0
     *  it also sets the post meta field _event_upcoming_label to 'Upcoming' or 'Past'
     */
    public function isPostUpcoming($post_id = false)
    {
        $post_id = (!empty($post_id)) ? $post_id : get_the_ID();

        // In order to do this check we need both a post, and an archive date field
        if (empty($post_id) || empty($this->dates['archive'])) {
            return;
        }

        // Get the archived date
        $archiveDate = get_post_meta($post_id, $this->dates['archive']);

        // Get today's date to compare against
        $today = date('Ymd');

        // If we do not have an info in the archiveDate post meta, stop
        if (empty($archiveDate)) {
            return;
        }

        // Update the archive based on the inequalities
        $post_meta_value = (intval($today) < intval($archiveDate[0])) ? "Upcoming" : "Past";

        update_post_meta($post_id, '_is_upcoming', intval($today) < intval($archiveDate[0]));
        update_post_meta($post_id, '_event_upcoming_label', $post_meta_value);
    }
}
