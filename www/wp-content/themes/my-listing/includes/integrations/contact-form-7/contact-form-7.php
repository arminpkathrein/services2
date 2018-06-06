<?php

/*
 * Contact Form 7 Integration.
 */

class CASE27_Contact_Form_7_Integration {

	/*
	 * Constructor.
	 */
	public function __construct()
	{
		add_filter('wpcf7_form_hidden_fields', [$this, 'add_custom_hidden_fields']);
		add_filter('wpcf7_mail_components', [$this, 'add_mail_recipients'], 100, 3);
	}


	/*
     * Add custom hidden fields to the form html markup. This is needed for
     * listing contact forms, where we need the post id to make sure it's a 'job_listing',
     * and need to add a placeholder for the list of email recipients, which in each listing will
     * be replaced by unqiue email(s) for each different listing.
	 */
	public function add_custom_hidden_fields($fields)
	{
		$fields['_case27_recipients'] = '%case27_recipients%';
		$fields['_case27_post_id'] = get_the_ID();

		return $fields;
	}


	/*
     * For 'job_listing' contact forms, update the 'recipient' component with
     * the email(s) of the requested listing.
	 */
	public function add_mail_recipients($components, $form, $obj)
	{
		if (!isset($_POST['_case27_post_id']) || !$_POST['_case27_post_id'] || !isset($_POST['_case27_recipients']) || !$_POST['_case27_recipients']) {
			return $components;
		}

		$listing = get_posts([
			'post_type' => 'job_listing',
			'p' => absint((int) $_POST['_case27_post_id']),
			]);

		// Make sure it's a 'job_listing' post type.
		if (!$listing) {
			return $components;
		}

		$recipients_validated = [];

		// Validate each recipient email.
		foreach ((array) explode(',', $_POST['_case27_recipients']) as $recipient) {
			$recipient = trim($recipient);

			if (is_email($recipient)) {
				$recipients_validated[] = $recipient;
			}
		}

		// Update the recipient value.
		if ($recipients_validated) {
			$components['recipient'] = join(',', $recipients_validated);
		}

		return $components;
	}
}

new CASE27_Contact_Form_7_Integration;