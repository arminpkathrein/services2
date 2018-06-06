<?php

class CASE27_Integrations_Review {

	public function __construct()
	{
        add_action( 'pre_comment_on_post', [ $this, 'action_pre_comment_on_post' ] );
        add_action( 'comment_post', [ $this, 'action_comment_post' ] );
        add_action( "admin_post_update_review", [$this, 'update_review'] );
        add_action( "admin_post_nopriv_update_review", [$this, 'update_review'] );
        add_action( "trash_comment", [$this, 'update_listing_rating_on_comment_delete'] );
        add_action( "delete_comment", [$this, 'update_listing_rating_on_comment_delete'] );
        add_action( "transition_comment_status", [$this, 'update_listing_rating_on_comment_transition'], 10, 3 );
	}


    public function update_listing_rating_on_comment_delete($commentID)
    {
        $comment = get_comment($commentID);

        delete_comment_meta($commentID, '_case27_post_rating');
        update_post_meta($comment->comment_post_ID, '_case27_average_rating', self::get_listing_rating($comment->comment_post_ID));
    }

    public function update_listing_rating_on_comment_transition($new_status, $old_status, $comment)
    {
        if (get_post_type($comment->comment_post_ID) != 'job_listing') return;

        update_post_meta($comment->comment_post_ID, '_case27_average_rating', self::get_listing_rating($comment->comment_post_ID));
    }

    public static function get_rating($commentID)
    {
        $rating = absint( get_comment_meta($commentID, '_case27_post_rating', true) );

        if ($rating && $rating >= 1 && $rating <= 10) {
            return $rating;
        }

        return false;
    }


    public static function get_listing_rating_optimized($listingID)
    {
        // Save average rating as listing meta each time comments are added/removed,
        // as a way to optimize the amount of time it takes to retrieve the average rating.
        $meta_rating = get_post_meta($listingID, '_case27_average_rating', true);

        if ($meta_rating && $meta_rating >= 1 && $meta_rating <= 10) {
            return round($meta_rating, 1);
        }

        return self::get_listing_rating($listingID);
    }


    public static function get_listing_rating($listingID)
    {
        global $wpdb;

        $rating = (float) $wpdb->get_var( $wpdb->prepare("
            SELECT AVG(meta_value) AS avg_rating
            FROM $wpdb->commentmeta
            WHERE meta_key = '_case27_post_rating'
            AND comment_id IN (
                SELECT comment_id
                FROM $wpdb->comments
                WHERE comment_post_ID = %s
                AND comment_approved = 1
            )", $listingID) );

        if ($rating && $rating >= 1 && $rating <= 10) {
            return round($rating, 1);
        }

        return false;
    }


    public function update_review()
    {
        if (!is_user_logged_in() || !isset($_POST['comment']) || !$_POST['comment'] || !isset($_POST['listing_id']) || !$_POST['listing_id']) {
            return wp_die( '<p>' . __( 'Invalid request.', 'my-listing') . '</p>', __( 'Comment Submission Failure.', 'my-listing' ), array( 'back_link' => true ) );
        }

        $listingID = absint((int) $_POST['listing_id']);
        $comment_content = trim( $_POST['comment'] );
        $rating = isset($_POST['star_rating']) && $_POST['star_rating'] ? absint((int) $_POST['star_rating']) : false;
        $user_review = CASE27_Integrations_Review::has_user_reviewed(get_current_user_id(), $listingID);

        if (!$user_review) {
            return wp_die( '<p>' . __( 'Invalid request.', 'my-listing') . '</p>', __( 'Comment Submission Failure.', 'my-listing' ), array( 'back_link' => true ) );
        }

        wp_update_comment([
            'comment_ID' => $user_review->comment_ID,
            'comment_content' => $comment_content,
        ]);

        if ($rating && $rating >= 1 && $rating <= 10) {
            update_comment_meta($user_review->comment_ID, '_case27_post_rating', $rating);
            update_post_meta($listingID, '_case27_average_rating', self::get_listing_rating($listingID));
        }

        if ( wp_get_referer() ) {
            return wp_safe_redirect( wp_get_referer() );
        }

        return wp_die( '<p>' . __( 'Your review has been updated.', 'my-listing') . '</p>', __( 'Success', 'my-listing' ), array( 'back_link' => true ) );
    }


	public static function has_user_reviewed($userID, $listingID)
	{
		if (!$listingID || !$userID) return false;

		$review = get_comments([
			'user_id' => $userID,
			'post_id' => $listingID,
    		'parent' => 0,
			]);

		return $review ? $review[0] : false;
	}


    public function action_pre_comment_on_post($postID)
    {
    	$post = get_post($postID);

    	if (!is_user_logged_in() || !$post || get_post_type($post) !== 'job_listing') {
    		return;
    	}

    	if ( isset( $_POST['comment_parent'] ) ) {
    		$comment_parent = absint( $_POST['comment_parent'] );

    		if ($comment_parent) {
    			return;
    		}
    	}

    	// See if user has already reviewed this listing.
    	$user_comments = get_comments([
    		'user_id' => get_current_user_id(),
    		'post_id' => $post->ID,
    		'parent' => 0,
    		]);

    	// If so, don't proceed with the comment submission.
    	if ($user_comments) {
    		wp_die( '<p>' . __( 'You\'ve already sumbitted a review on this listing.', 'my-listing') . '</p>', __( 'Comment Submission Failure', 'my-listing' ), array( 'back_link' => true ) );
    	}
    }


    public function action_comment_post($comment_id)
    {
    	$is_reply = isset($_POST['comment_parent']) && $_POST['comment_parent'] !== '0';
    	$rating = isset($_POST['star_rating']) && $_POST['star_rating'] ? absint((int) $_POST['star_rating']) : false;

    	// dump($comment_id, $_POST, $is_reply, $rating);

    	if ($is_reply || get_post_type($_POST['comment_post_ID']) !== 'job_listing') {
    		return;
    	}

    	// At this point, we know the review is unique, is not a comment reply, and has been submitted on a listing post type.
    	// Proceed to save rating it it's present and valid.
    	if ($rating && $rating >= 1 && $rating <= 10) {
    		update_comment_meta($comment_id, '_case27_post_rating', $rating);
            update_post_meta(absint($_POST['comment_post_ID']), '_case27_average_rating', self::get_listing_rating(absint($_POST['comment_post_ID'])));
    	}
    }
}

new CASE27_Integrations_Review;
