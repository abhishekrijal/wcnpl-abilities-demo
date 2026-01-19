# WCNPL Abilities Demo

A demo WordPress plugin showcasing the WordPress 6.9 Abilities API with a simple forms system.

## Description

This plugin demonstrates the new WordPress 6.9 Abilities API by creating a simple forms system with the following abilities:

- **Submit Form**: Submit a new form entry with name, email, and message
- **Get Submission Details**: Retrieve details of a specific form submission
- **Count Forms**: Get the total number of forms in the system
- **Count Submissions**: Get the total number of form submissions (optionally filtered by form ID)

## Requirements

- WordPress 6.9 or higher
- PHP 7.4 or higher

## Installation

1. Upload the `wcnpl-abilities-demo` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create the necessary database tables and a sample form

## Features

### Database Structure

The plugin creates two database tables:

- `wp_wcnpl_forms`: Stores form definitions
- `wp_wcnpl_form_submissions`: Stores form submissions

### Abilities API Implementation

The plugin registers a custom ability category (`wcnpl-forms`) and four abilities:

#### 1. Submit Form (`wcnpl-abilities-demo/submit-form`)

**Permission**: Public (anyone can submit)

**Input**:
```json
{
  "form_id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "message": "This is a test message"
}
```

**Output**:
```json
{
  "success": true,
  "submission_id": 1,
  "message": "Form submitted successfully."
}
```

#### 2. Get Submission Details (`wcnpl-abilities-demo/get-submission`)

**Permission**: Users with `manage_options` capability

**Input**:
```json
{
  "submission_id": 1
}
```

**Output**:
```json
{
  "success": true,
  "submission": {
    "id": 1,
    "form_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "message": "This is a test message",
    "submitted_at": "2026-01-11 12:00:00"
  },
  "message": "Submission retrieved successfully."
}
```

#### 3. Count Forms (`wcnpl-abilities-demo/count-forms`)

**Permission**: Users with `manage_options` capability

**Input**: None required

**Output**:
```json
{
  "count": 1
}
```

#### 4. Count Submissions (`wcnpl-abilities-demo/count-submissions`)

**Permission**: Users with `manage_options` capability

**Input** (optional):
```json
{
  "form_id": 1
}
```

**Output**:
```json
{
  "count": 5,
  "form_id": 1
}
```

## Usage with REST API

### List All Abilities

```bash
GET /wp-json/wp-abilities/v1/abilities
```

### Get Specific Ability

```bash
GET /wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/submit-form
```

### Execute an Ability

```bash
POST /wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/submit-form/run
Content-Type: application/json

{
  "form_id": 1,
  "name": "Jane Doe",
  "email": "jane@example.com",
  "message": "Hello from the Abilities API!"
}
```

### Count Submissions

```bash
POST /wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/count-submissions/run
Content-Type: application/json

{
  "form_id": 1
}
```

## Testing Examples

### Using cURL

#### Submit a Form
```bash
curl -X POST https://your-site.com/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/submit-form/run \
  -H "Content-Type: application/json" \
  -d '{
    "form_id": 1,
    "name": "Test User",
    "email": "test@example.com",
    "message": "This is a test submission"
  }'
```

#### Get Submission (requires authentication)
```bash
curl -X POST https://your-site.com/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/get-submission/run \
  -H "Content-Type: application/json" \
  -u admin:password \
  -d '{
    "submission_id": 1
  }'
```

#### Count Forms (requires authentication)
```bash
curl -X POST https://your-site.com/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/count-forms/run \
  -H "Content-Type: application/json" \
  -u admin:password \
  -d '{}'
```

## Code Standards

This plugin follows WordPress Coding Standards (WPCS) and includes:

- Proper sanitization and validation of input data
- Secure database queries using `$wpdb->prepare()`
- Internationalization support with text domain
- Proper capability checks for protected abilities
- JSON Schema validation for inputs and outputs
- Comprehensive inline documentation

## Development

### File Structure

```
wcnpl-abilities-demo/
├── wcnpl-abilities-demo.php (Main plugin file)
├── README.md
└── includes/
    ├── class-wcnpl-abilities-demo.php (Core plugin class)
    ├── class-wcnpl-abilities-demo-loader.php (Hooks loader)
    ├── class-wcnpl-abilities-demo-activator.php (Activation handler)
    ├── class-wcnpl-abilities-demo-deactivator.php (Deactivation handler)
    └── class-wcnpl-abilities-demo-abilities.php (Abilities registration)
```

## License

GPL v2 or later

## Author

Abhishek Rijal

## Changelog

### 1.0.0
- Initial release
- Implemented basic forms system
- Registered four abilities demonstrating the WP Abilities API
- Added complete REST API support
