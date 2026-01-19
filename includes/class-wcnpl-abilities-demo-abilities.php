<?php
/**
 * The abilities registration class.
 *
 * @package    WCNPL_Abilities_Demo
 * @subpackage WCNPL_Abilities_Demo/includes
 */

/**
 * The abilities registration class.
 *
 * Defines all abilities for the forms plugin using the WP Abilities API.
 */
class WCNPL_Abilities_Demo_Abilities {

	/**
	 * Register fallback REST routes (does not require the Abilities API).
	 *
	 * This exists so the demo can still be used on WP installs where the
	 * Abilities API REST endpoints aren't available.
	 */
	public function register_rest_routes() {
		if ( ! function_exists( 'register_rest_route' ) ) {
			return;
		}

		register_rest_route(
			'wcnpl-abilities-demo/v1',
			'/submit-form',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_submit_form' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'wcnpl-abilities-demo/v1',
			'/get-submission',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_get_submission' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		register_rest_route(
			'wcnpl-abilities-demo/v1',
			'/count-forms',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_count_forms' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		register_rest_route(
			'wcnpl-abilities-demo/v1',
			'/count-submissions',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_count_submissions' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Register ability categories.
	 */
	public function register_categories() {
		$this->register_forms_category();
	}

	/**
	 * Register all abilities.
	 */
	public function register_abilities() {
		// Register individual abilities.
		$this->register_submit_form_ability();
		$this->register_get_submission_ability();
		$this->register_count_forms_ability();
		$this->register_count_submissions_ability();
	}

	/**
	 * Register the forms ability category.
	 */
	private function register_forms_category() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			'wcnpl-forms',
			array(
				'label'       => __( 'Forms', 'wcnpl-abilities-demo' ),
				'description' => __( 'Abilities related to form submissions and management.', 'wcnpl-abilities-demo' ),
			)
		);
	}

	/**
	 * Register ability to submit a form.
	 */
	private function register_submit_form_ability() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wcnpl-abilities-demo/submit-form',
			array(
				'label'               => __( 'Submit Form', 'wcnpl-abilities-demo' ),
				'description'         => __( 'Submit a new form entry with name, email, and message.', 'wcnpl-abilities-demo' ),
				'category'            => 'wcnpl-forms',
				'meta'                => array(
					'show_in_rest' => true,
				),
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'form_id' => array(
							'type'        => 'integer',
							'description' => __( 'The ID of the form to submit to.', 'wcnpl-abilities-demo' ),
							'minimum'     => 1,
						),
						'name'    => array(
							'type'        => 'string',
							'description' => __( 'Name of the person submitting the form.', 'wcnpl-abilities-demo' ),
							'minLength'   => 1,
							'maxLength'   => 255,
						),
						'email'   => array(
							'type'        => 'string',
							'description' => __( 'Email address of the person submitting the form.', 'wcnpl-abilities-demo' ),
							'format'      => 'email',
							'maxLength'   => 255,
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'The message content.', 'wcnpl-abilities-demo' ),
							'minLength'   => 1,
						),
					),
					'required'   => array( 'form_id', 'name', 'email', 'message' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'       => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the submission was successful.', 'wcnpl-abilities-demo' ),
						),
						'submission_id' => array(
							'type'        => 'integer',
							'description' => __( 'The ID of the created submission.', 'wcnpl-abilities-demo' ),
						),
						'message'       => array(
							'type'        => 'string',
							'description' => __( 'A message describing the result.', 'wcnpl-abilities-demo' ),
						),
					),
				),
				'permission_callback' => function () {
					// Allow anyone to submit forms (public forms).
					return true;
				},
				'execute_callback'    => array( $this, 'execute_submit_form' ),
			)
		);
	}

	/**
	 * Register ability to get submission details.
	 */
	private function register_get_submission_ability() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wcnpl-abilities-demo/get-submission',
			array(
				'label'               => __( 'Get Submission Details', 'wcnpl-abilities-demo' ),
				'description'         => __( 'Retrieve details of a specific form submission.', 'wcnpl-abilities-demo' ),
				'category'            => 'wcnpl-forms',
				'meta'                => array(
					'show_in_rest' => true,
				),
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'submission_id' => array(
							'type'        => 'integer',
							'description' => __( 'The ID of the submission to retrieve.', 'wcnpl-abilities-demo' ),
							'minimum'     => 1,
						),
					),
					'required'   => array( 'submission_id' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'    => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the request was successful.', 'wcnpl-abilities-demo' ),
						),
						'submission' => array(
							'type'       => 'object',
							'properties' => array(
								'id'           => array( 'type' => 'integer' ),
								'form_id'      => array( 'type' => 'integer' ),
								'name'         => array( 'type' => 'string' ),
								'email'        => array( 'type' => 'string' ),
								'message'      => array( 'type' => 'string' ),
								'submitted_at' => array( 'type' => 'string' ),
							),
						),
						'message'    => array(
							'type'        => 'string',
							'description' => __( 'A message describing the result.', 'wcnpl-abilities-demo' ),
						),
					),
				),
				'permission_callback' => function () {
					// Only logged in users with manage_options capability can view submissions.
					return current_user_can( 'manage_options' );
				},
				'execute_callback'    => array( $this, 'execute_get_submission' ),
			)
		);
	}

	/**
	 * Register ability to count forms.
	 */
	private function register_count_forms_ability() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wcnpl-abilities-demo/count-forms',
			array(
				'label'               => __( 'Count Forms', 'wcnpl-abilities-demo' ),
				'description'         => __( 'Get the total number of forms in the system, along with a list of forms.', 'wcnpl-abilities-demo' ),
				'category'            => 'wcnpl-forms',
				'meta'                => array(
					'show_in_rest' => true,
				),
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'count' => array(
							'type'        => 'integer',
							'description' => __( 'Total number of forms.', 'wcnpl-abilities-demo' ),
						),
						'forms' => array(
							'type'        => 'array',
							'description' => __( 'List of forms in the system.', 'wcnpl-abilities-demo' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id'          => array( 'type' => 'integer' ),
									'title'       => array( 'type' => 'string' ),
									'description' => array( 'type' => 'string' ),
									'created_at'  => array( 'type' => 'string' ),
									'updated_at'  => array( 'type' => 'string' ),
								),
							),
						),
					),
				),
				'permission_callback' => function () {
					// Only logged in users with manage_options capability can count forms.
					return current_user_can( 'manage_options' );
				},
				'execute_callback'    => array( $this, 'execute_count_forms' ),
			)
		);
	}

	/**
	 * Register ability to count submissions.
	 */
	private function register_count_submissions_ability() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wcnpl-abilities-demo/count-submissions',
			array(
				'label'               => __( 'Count Submissions', 'wcnpl-abilities-demo' ),
				'description'         => __( 'Get the total number of form submissions, optionally filtered by form ID.', 'wcnpl-abilities-demo' ),
				'category'            => 'wcnpl-forms',
				'meta'                => array(
					'show_in_rest' => true,
				),
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'form_id' => array(
							'type'        => 'integer',
							'description' => __( 'Optional form ID to filter submissions.', 'wcnpl-abilities-demo' ),
							'minimum'     => 1,
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'count'   => array(
							'type'        => 'integer',
							'description' => __( 'Total number of submissions.', 'wcnpl-abilities-demo' ),
						),
						'form_id' => array(
							'type'        => 'integer',
							'description' => __( 'The form ID used for filtering (if provided).', 'wcnpl-abilities-demo' ),
						),
					),
				),
				'permission_callback' => function () {
					// Only logged in users with manage_options capability can count submissions.
					return current_user_can( 'manage_options' );
				},
				'execute_callback'    => array( $this, 'execute_count_submissions' ),
			)
		);
	}

	/**
	 * Execute the submit form ability.
	 *
	 * @param array $input The input data.
	 * @return array The result of the submission.
	 */
	public function execute_submit_form( $input ) {
		global $wpdb;

		$form_id = isset( $input['form_id'] ) ? intval( $input['form_id'] ) : 0;
		$name    = isset( $input['name'] ) ? sanitize_text_field( $input['name'] ) : '';
		$email   = isset( $input['email'] ) ? sanitize_email( $input['email'] ) : '';
		$message = isset( $input['message'] ) ? sanitize_textarea_field( $input['message'] ) : '';

		// Validate email.
		if ( ! is_email( $email ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid email address.', 'wcnpl-abilities-demo' ),
			);
		}

		// Check if form exists.
		$forms_table = $wpdb->prefix . 'wcnpl_forms';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `$forms_table` WHERE id = %d", $form_id ) );

		if ( ! $form_exists ) {
			return array(
				'success' => false,
				'message' => __( 'Form not found.', 'wcnpl-abilities-demo' ),
			);
		}

		// Insert submission.
		$submissions_table = $wpdb->prefix . 'wcnpl_form_submissions';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$inserted          = $wpdb->insert(
			$submissions_table,
			array(
				'form_id' => $form_id,
				'name'    => $name,
				'email'   => $email,
				'message' => $message,
			),
			array( '%d', '%s', '%s', '%s' )
		);

		if ( $inserted ) {
			return array(
				'success'       => true,
				'submission_id' => $wpdb->insert_id,
				'message'       => __( 'Form submitted successfully.', 'wcnpl-abilities-demo' ),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'Failed to submit form.', 'wcnpl-abilities-demo' ),
		);
	}

	/**
	 * Execute the get submission ability.
	 *
	 * @param array $input The input data.
	 * @return array The submission details.
	 */
	public function execute_get_submission( $input ) {
		global $wpdb;

		$submission_id     = isset( $input['submission_id'] ) ? intval( $input['submission_id'] ) : 0;
		$submissions_table = $wpdb->prefix . 'wcnpl_form_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `$submissions_table` WHERE id = %d",
				$submission_id
			),
			ARRAY_A
		);

		if ( ! $submission ) {
			return array(
				'success' => false,
				'message' => __( 'Submission not found.', 'wcnpl-abilities-demo' ),
			);
		}

		return array(
			'success'    => true,
			'submission' => array(
				'id'           => intval( $submission['id'] ),
				'form_id'      => intval( $submission['form_id'] ),
				'name'         => $submission['name'],
				'email'        => $submission['email'],
				'message'      => $submission['message'],
				'submitted_at' => $submission['submitted_at'],
			),
			'message'    => __( 'Submission retrieved successfully.', 'wcnpl-abilities-demo' ),
		);
	}

	/**
	 * Execute the count forms ability.
	 *
	 * @param array $input The input data.
	 * @return array The count of forms.
	 */
	public function execute_count_forms( $input ) {
		global $wpdb;

		$forms_table = $wpdb->prefix . 'wcnpl_forms';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$count       = $wpdb->get_var( "SELECT COUNT(*) FROM `$forms_table`" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows        = $wpdb->get_results( "SELECT id, title, description, created_at, updated_at FROM `$forms_table` ORDER BY id ASC", ARRAY_A );

		$forms = array();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$forms[] = array(
					'id'          => intval( $row['id'] ),
					'title'       => (string) $row['title'],
					'description' => (string) $row['description'],
					'created_at'  => (string) $row['created_at'],
					'updated_at'  => (string) $row['updated_at'],
				);
			}
		}

		return array(
			'count' => intval( $count ),
			'forms' => $forms,
		);
	}

	/**
	 * REST callback: count/list forms (fallback route).
	 *
	 * @param WP_REST_Request $request The REST request.
	 * @return WP_REST_Response|array
	 */
	public function rest_count_forms( $request ) {
		$result = $this->execute_count_forms( array() );
		return rest_ensure_response( $result );
	}

	/**
	 * REST callback: submit form (fallback route).
	 *
	 * @param WP_REST_Request $request The REST request.
	 * @return WP_REST_Response|array
	 */
	public function rest_submit_form( $request ) {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = array();
		}
		$result = $this->execute_submit_form( $params );
		return rest_ensure_response( $result );
	}

	/**
	 * REST callback: get submission (fallback route).
	 *
	 * @param WP_REST_Request $request The REST request.
	 * @return WP_REST_Response|array
	 */
	public function rest_get_submission( $request ) {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = array();
		}
		$result = $this->execute_get_submission( $params );
		return rest_ensure_response( $result );
	}

	/**
	 * REST callback: count submissions (fallback route).
	 *
	 * @param WP_REST_Request $request The REST request.
	 * @return WP_REST_Response|array
	 */
	public function rest_count_submissions( $request ) {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = array();
		}
		$result = $this->execute_count_submissions( $params );
		return rest_ensure_response( $result );
	}

	/**
	 * Execute the count submissions ability.
	 *
	 * @param array $input The input data.
	 * @return array The count of submissions.
	 */
	public function execute_count_submissions( $input ) {
		global $wpdb;

		$form_id           = isset( $input['form_id'] ) ? intval( $input['form_id'] ) : 0;
		$submissions_table = $wpdb->prefix . 'wcnpl_form_submissions';

		if ( $form_id > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$wpdb->prefix}wcnpl_form_submissions` WHERE form_id = %d",
					$form_id
				)
			);

			return array(
				'count'   => intval( $count ),
				'form_id' => $form_id,
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `$submissions_table`" );

		return array(
			'count' => intval( $count ),
		);
	}
}
