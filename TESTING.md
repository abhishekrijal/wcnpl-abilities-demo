# Testing the WCNPL Abilities Demo Plugin

This guide provides step-by-step instructions for testing the plugin's abilities.

## Prerequisites

1. WordPress 6.9+ installation
2. Plugin activated and database tables created
3. REST API accessible

## Testing Steps

### 1. List All Abilities

Get all registered abilities in the system:

```bash
curl -X GET http://your-wordpress-site.com/wp-json/wp-abilities/v1/abilities
```

You should see all registered abilities including those from this plugin.

### 2. Get Specific Ability Details

Get details about the submit form ability:

```bash
curl -X GET http://your-wordpress-site.com/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/submit-form
```

This returns the schema, permissions, and other metadata.

### 3. Submit a Form (Public - No Authentication Required)

Submit a new form entry:

```bash
curl -X POST http://your-wordpress-site.com/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/submit-form/run \
  -H "Content-Type: application/json" \
  -d '{
    "form_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "message": "This is a test submission from the Abilities API!"
  }'
```

Expected response:
```json
{
  "success": true,
  "submission_id": 1,
  "message": "Form submitted successfully."
}
```

### 4. Test Input Validation

Try submitting with invalid email:

```bash
curl -X POST http://your-wordpress-site.com/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/submit-form/run \
  -H "Content-Type: application/json" \
  -d '{
    "form_id": 1,
    "name": "Test User",
    "email": "invalid-email",
    "message": "Testing validation"
  }'
```

Expected response:
```json
{
  "success": false,
  "message": "Invalid email address."
}
```

### 5. Count Forms (Requires Authentication)

Count total forms in the system:

```bash
curl -X POST http://your-wordpress-site.com/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/count-forms/run \
  -H "Content-Type: application/json" \
  -u admin:your-password \
  -d '{}'
```

Expected response:
```json
{
  "count": 1
}
```

### 6. Count Submissions (Requires Authentication)

Count all submissions:

```bash
curl -X POST http://your-wordpress-site.com/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/count-submissions/run \
  -H "Content-Type: application/json" \
  -u admin:your-password \
  -d '{}'
```

Count submissions for a specific form:

```bash
curl -X POST http://your-wordpress-site.com/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/count-submissions/run \
  -H "Content-Type: application/json" \
  -u admin:your-password \
  -d '{
    "form_id": 1
  }'
```

Expected response:
```json
{
  "count": 3,
  "form_id": 1
}
```

### 7. Get Submission Details (Requires Authentication)

Retrieve a specific submission:

```bash
curl -X POST http://your-wordpress-site.com/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/get-submission/run \
  -H "Content-Type: application/json" \
  -u admin:your-password \
  -d '{
    "submission_id": 1
  }'
```

Expected response:
```json
{
  "success": true,
  "submission": {
    "id": 1,
    "form_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "message": "This is a test submission from the Abilities API!",
    "submitted_at": "2026-01-11 12:00:00"
  },
  "message": "Submission retrieved successfully."
}
```

### 8. Test Permission Restrictions

Try to access protected abilities without authentication:

```bash
curl -X POST http://your-wordpress-site.com/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/get-submission/run \
  -H "Content-Type: application/json" \
  -d '{
    "submission_id": 1
  }'
```

Should receive a permission denied error.

## Testing with WordPress REST API Browser

You can also test using the WordPress REST API browser plugin or Postman:

1. Install "WP REST API Controller" plugin
2. Navigate to Tools → REST API
3. Browse to `/wp-abilities/v1/abilities`
4. Test each endpoint with the appropriate data

## Verifying Database Tables

Check if tables were created correctly:

```sql
-- In phpMyAdmin or MySQL client
SHOW TABLES LIKE '%wcnpl%';

-- Should show:
-- wp_wcnpl_forms
-- wp_wcnpl_form_submissions

-- View sample form
SELECT * FROM wp_wcnpl_forms;

-- View submissions
SELECT * FROM wp_wcnpl_form_submissions;
```

## Common Issues

### Issue: Abilities not appearing

**Solution**: Make sure WordPress 6.9+ is installed and the plugin is activated.

### Issue: Permission denied errors

**Solution**: For protected abilities, ensure you're authenticated with an admin user who has `manage_options` capability.

### Issue: Database tables not created

**Solution**: Deactivate and reactivate the plugin to trigger the activation hook.

### Issue: REST API not accessible

**Solution**: Check permalink settings and ensure pretty permalinks are enabled.

## Success Criteria

✅ All abilities appear in `/wp-abilities/v1/abilities` endpoint  
✅ Public abilities (submit-form) work without authentication  
✅ Protected abilities require proper authentication  
✅ Input validation works correctly  
✅ Database tables are created with sample data  
✅ All CRUD operations function as expected  

## Next Steps

After successful testing:
1. Explore extending with additional abilities
2. Add custom validation rules
3. Integrate with frontend forms
4. Build AI/automation tools using these abilities
