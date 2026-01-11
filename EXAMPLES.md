# Examples - Extending the WCNPL Abilities Demo Plugin

This file contains examples of how to extend the plugin with additional abilities and features.

## Example 1: Adding a New Ability to List All Forms

```php
/**
 * Register ability to list all forms.
 */
private function register_list_forms_ability() {
    if ( ! function_exists( 'wp_register_ability' ) ) {
        return;
    }

    wp_register_ability(
        array(
            'name'                => 'wcnpl-abilities-demo/list-forms',
            'label'               => __( 'List Forms', 'wcnpl-abilities-demo' ),
            'description'         => __( 'Get a list of all available forms.', 'wcnpl-abilities-demo' ),
            'category'            => 'wcnpl-forms',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(),
            ),
            'output_schema'       => array(
                'type'  => 'array',
                'items' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'id'          => array( 'type' => 'integer' ),
                        'title'       => array( 'type' => 'string' ),
                        'description' => array( 'type' => 'string' ),
                        'created_at'  => array( 'type' => 'string' ),
                    ),
                ),
            ),
            'permission_callback' => function () {
                return true; // Public
            },
            'execute_callback'    => array( $this, 'execute_list_forms' ),
        )
    );
}

/**
 * Execute the list forms ability.
 *
 * @param array $input The input data.
 * @return array The list of forms.
 */
public function execute_list_forms( $input ) {
    global $wpdb;

    $forms_table = $wpdb->prefix . 'wcnpl_forms';
    $forms       = $wpdb->get_results( "SELECT * FROM $forms_table ORDER BY created_at DESC", ARRAY_A );

    return array_map(
        function( $form ) {
            return array(
                'id'          => intval( $form['id'] ),
                'title'       => $form['title'],
                'description' => $form['description'],
                'created_at'  => $form['created_at'],
            );
        },
        $forms
    );
}
```

## Example 2: Adding Pagination to List Submissions

```php
/**
 * Register ability to list submissions with pagination.
 */
private function register_list_submissions_ability() {
    if ( ! function_exists( 'wp_register_ability' ) ) {
        return;
    }

    wp_register_ability(
        array(
            'name'                => 'wcnpl-abilities-demo/list-submissions',
            'label'               => __( 'List Submissions', 'wcnpl-abilities-demo' ),
            'description'         => __( 'Get a paginated list of form submissions.', 'wcnpl-abilities-demo' ),
            'category'            => 'wcnpl-forms',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'form_id' => array(
                        'type'        => 'integer',
                        'description' => __( 'Optional form ID to filter submissions.', 'wcnpl-abilities-demo' ),
                        'minimum'     => 1,
                    ),
                    'page'    => array(
                        'type'        => 'integer',
                        'description' => __( 'Page number for pagination.', 'wcnpl-abilities-demo' ),
                        'minimum'     => 1,
                        'default'     => 1,
                    ),
                    'per_page' => array(
                        'type'        => 'integer',
                        'description' => __( 'Number of items per page.', 'wcnpl-abilities-demo' ),
                        'minimum'     => 1,
                        'maximum'     => 100,
                        'default'     => 10,
                    ),
                ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'submissions' => array(
                        'type'  => 'array',
                        'items' => array(
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
                    ),
                    'total'       => array( 'type' => 'integer' ),
                    'page'        => array( 'type' => 'integer' ),
                    'per_page'    => array( 'type' => 'integer' ),
                    'total_pages' => array( 'type' => 'integer' ),
                ),
            ),
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'execute_callback'    => array( $this, 'execute_list_submissions' ),
        )
    );
}

/**
 * Execute the list submissions ability.
 *
 * @param array $input The input data.
 * @return array The paginated list of submissions.
 */
public function execute_list_submissions( $input ) {
    global $wpdb;

    $form_id           = isset( $input['form_id'] ) ? intval( $input['form_id'] ) : 0;
    $page              = isset( $input['page'] ) ? max( 1, intval( $input['page'] ) ) : 1;
    $per_page          = isset( $input['per_page'] ) ? min( 100, max( 1, intval( $input['per_page'] ) ) ) : 10;
    $offset            = ( $page - 1 ) * $per_page;
    $submissions_table = $wpdb->prefix . 'wcnpl_form_submissions';

    // Build query
    $where = '';
    $count_where = '';
    
    if ( $form_id > 0 ) {
        $where = $wpdb->prepare( " WHERE form_id = %d", $form_id );
        $count_where = $where;
    }

    // Get total count
    $total = $wpdb->get_var( "SELECT COUNT(*) FROM $submissions_table $count_where" );

    // Get submissions
    $submissions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $submissions_table $where ORDER BY submitted_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ),
        ARRAY_A
    );

    $formatted_submissions = array_map(
        function( $submission ) {
            return array(
                'id'           => intval( $submission['id'] ),
                'form_id'      => intval( $submission['form_id'] ),
                'name'         => $submission['name'],
                'email'        => $submission['email'],
                'message'      => $submission['message'],
                'submitted_at' => $submission['submitted_at'],
            );
        },
        $submissions
    );

    return array(
        'submissions' => $formatted_submissions,
        'total'       => intval( $total ),
        'page'        => $page,
        'per_page'    => $per_page,
        'total_pages' => ceil( $total / $per_page ),
    );
}
```

## Example 3: Adding Search Functionality

```php
/**
 * Register ability to search submissions.
 */
private function register_search_submissions_ability() {
    if ( ! function_exists( 'wp_register_ability' ) ) {
        return;
    }

    wp_register_ability(
        array(
            'name'                => 'wcnpl-abilities-demo/search-submissions',
            'label'               => __( 'Search Submissions', 'wcnpl-abilities-demo' ),
            'description'         => __( 'Search form submissions by name, email, or message.', 'wcnpl-abilities-demo' ),
            'category'            => 'wcnpl-forms',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'search_term' => array(
                        'type'        => 'string',
                        'description' => __( 'Search term to find in name, email, or message.', 'wcnpl-abilities-demo' ),
                        'minLength'   => 1,
                    ),
                ),
                'required'   => array( 'search_term' ),
            ),
            'output_schema'       => array(
                'type'  => 'array',
                'items' => array(
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
            ),
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'execute_callback'    => array( $this, 'execute_search_submissions' ),
        )
    );
}

/**
 * Execute the search submissions ability.
 *
 * @param array $input The input data.
 * @return array The search results.
 */
public function execute_search_submissions( $input ) {
    global $wpdb;

    $search_term       = isset( $input['search_term'] ) ? sanitize_text_field( $input['search_term'] ) : '';
    $submissions_table = $wpdb->prefix . 'wcnpl_form_submissions';

    $search_like = '%' . $wpdb->esc_like( $search_term ) . '%';

    $submissions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $submissions_table 
            WHERE name LIKE %s OR email LIKE %s OR message LIKE %s 
            ORDER BY submitted_at DESC",
            $search_like,
            $search_like,
            $search_like
        ),
        ARRAY_A
    );

    return array_map(
        function( $submission ) {
            return array(
                'id'           => intval( $submission['id'] ),
                'form_id'      => intval( $submission['form_id'] ),
                'name'         => $submission['name'],
                'email'        => $submission['email'],
                'message'      => $submission['message'],
                'submitted_at' => $submission['submitted_at'],
            );
        },
        $submissions
    );
}
```

## Example 4: Integrating with JavaScript

```javascript
// Example: Submit a form from the frontend
async function submitForm(formId, name, email, message) {
    try {
        const response = await fetch('/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/submit-form/run', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                form_id: formId,
                name: name,
                email: email,
                message: message
            })
        });

        const result = await response.json();
        
        if (result.success) {
            console.log('Form submitted successfully!', result.submission_id);
        } else {
            console.error('Form submission failed:', result.message);
        }
        
        return result;
    } catch (error) {
        console.error('Error submitting form:', error);
        throw error;
    }
}

// Example: Get submission count for admin dashboard
async function getSubmissionCount(formId = null) {
    try {
        const body = formId ? { form_id: formId } : {};
        
        const response = await fetch('/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/count-submissions/run', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpApiSettings.nonce // WordPress nonce
            },
            credentials: 'same-origin',
            body: JSON.stringify(body)
        });

        const result = await response.json();
        return result.count;
    } catch (error) {
        console.error('Error getting submission count:', error);
        throw error;
    }
}
```

## Example 5: Adding Custom Validation

```php
/**
 * Add custom validation to form submission.
 */
public function execute_submit_form( $input ) {
    global $wpdb;

    // Basic sanitization
    $form_id = isset( $input['form_id'] ) ? intval( $input['form_id'] ) : 0;
    $name    = isset( $input['name'] ) ? sanitize_text_field( $input['name'] ) : '';
    $email   = isset( $input['email'] ) ? sanitize_email( $input['email'] ) : '';
    $message = isset( $input['message'] ) ? sanitize_textarea_field( $input['message'] ) : '';

    // Custom validation: Check for spam keywords
    $spam_keywords = array( 'viagra', 'casino', 'lottery' );
    foreach ( $spam_keywords as $keyword ) {
        if ( stripos( $message, $keyword ) !== false ) {
            return array(
                'success' => false,
                'message' => __( 'Spam content detected.', 'wcnpl-abilities-demo' ),
            );
        }
    }

    // Custom validation: Check message length
    if ( strlen( $message ) < 10 ) {
        return array(
            'success' => false,
            'message' => __( 'Message must be at least 10 characters long.', 'wcnpl-abilities-demo' ),
        );
    }

    // Custom validation: Rate limiting (one submission per email per hour)
    $submissions_table = $wpdb->prefix . 'wcnpl_form_submissions';
    $recent_submission = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $submissions_table 
            WHERE email = %s AND submitted_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            $email
        )
    );

    if ( $recent_submission > 0 ) {
        return array(
            'success' => false,
            'message' => __( 'Please wait before submitting another form.', 'wcnpl-abilities-demo' ),
        );
    }

    // Continue with normal submission...
    // (Rest of the original code)
}
```

## Example 6: Adding Hooks for Extensibility

```php
/**
 * Execute the submit form ability with hooks.
 */
public function execute_submit_form( $input ) {
    global $wpdb;

    // Allow modification of input before processing
    $input = apply_filters( 'wcnpl_abilities_demo_before_submit', $input );

    // ... existing validation code ...

    // Insert submission
    $submissions_table = $wpdb->prefix . 'wcnpl_form_submissions';
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
        $submission_id = $wpdb->insert_id;

        // Fire action after successful submission
        do_action( 'wcnpl_abilities_demo_after_submit', $submission_id, $input );

        return array(
            'success'       => true,
            'submission_id' => $submission_id,
            'message'       => __( 'Form submitted successfully.', 'wcnpl-abilities-demo' ),
        );
    }

    return array(
        'success' => false,
        'message' => __( 'Failed to submit form.', 'wcnpl-abilities-demo' ),
    );
}
```

### Using the Hooks in Other Plugins

```php
// Send email notification after form submission
add_action( 'wcnpl_abilities_demo_after_submit', function( $submission_id, $input ) {
    $admin_email = get_option( 'admin_email' );
    $subject     = 'New Form Submission';
    $message     = sprintf(
        "New submission from %s (%s):\n\n%s",
        $input['name'],
        $input['email'],
        $input['message']
    );

    wp_mail( $admin_email, $subject, $message );
}, 10, 2 );

// Modify input before submission (e.g., add IP address tracking)
add_filter( 'wcnpl_abilities_demo_before_submit', function( $input ) {
    $input['ip_address'] = $_SERVER['REMOTE_ADDR'];
    return $input;
} );
```

## Summary

These examples demonstrate:
- Adding new abilities with proper schemas
- Implementing pagination and search
- Frontend integration with JavaScript
- Custom validation logic
- Extensibility through WordPress hooks
- Security best practices

All extensions should follow WPCS and maintain the same code quality as the base plugin.
