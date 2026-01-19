# Quick Reference Guide

## Installation

1. Upload to `/wp-content/plugins/wcnpl-abilities-demo/`
2. Activate in WordPress admin
3. Sample form created automatically

## Abilities Overview

| Ability Name | Permission | Purpose |
|-------------|-----------|---------|
| `wcnpl-abilities-demo/submit-form` | Public | Submit a form entry |
| `wcnpl-abilities-demo/get-submission` | Admin | Get submission details |
| `wcnpl-abilities-demo/count-forms` | Admin | Count total forms |
| `wcnpl-abilities-demo/count-submissions` | Admin | Count submissions |

## Quick Test Commands

### List All Abilities
```bash
curl http://localhost/wp-json/wp-abilities/v1/abilities
```

### Submit a Form
```bash
curl -X POST http://localhost/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/submit-form/run \
  -H "Content-Type: application/json" \
  -d '{"form_id":1,"name":"Test","email":"test@example.com","message":"Hello"}'
```

### Count Forms (requires auth)
```bash
curl -X POST http://localhost/wp-json/wp-abilities/v1/abilities/wcnpl-abilities-demo/count-forms/run \
  -H "Content-Type: application/json" \
  -u admin:password \
  -d '{}'
```

## File Structure

```
wcnpl-abilities-demo/
├── wcnpl-abilities-demo.php           # Main plugin file
├── includes/
│   ├── class-wcnpl-abilities-demo.php              # Core class
│   ├── class-wcnpl-abilities-demo-loader.php       # Hook manager
│   ├── class-wcnpl-abilities-demo-activator.php    # Activation
│   ├── class-wcnpl-abilities-demo-deactivator.php  # Deactivation
│   └── class-wcnpl-abilities-demo-abilities.php    # Abilities
├── README.md                          # Overview
├── README-PLUGIN.md                   # Full documentation
├── TESTING.md                         # Testing guide
├── EXAMPLES.md                        # Extension examples
└── PROJECT-SUMMARY.md                 # Project summary
```

## Database Tables

- `wp_wcnpl_forms` - Form definitions
- `wp_wcnpl_form_submissions` - Form submissions

## Key Functions

### Register Category
```php
wp_register_ability_category(
    'wcnpl-forms',
    [
        'label' => 'Forms',
        'description' => 'Form abilities',
    ]
);
```

### Register Ability
```php
wp_register_ability(
    'plugin/ability-name',
    [
        'label' => 'Ability Label',
        'description' => 'What it does',
        'category' => 'category-slug',
        'input_schema' => [...],
        'output_schema' => [...],
        'permission_callback' => function() { return true; },
        'execute_callback' => [$this, 'method_name'],
    ]
);
```

## Documentation Files

- **README.md** - Project overview
- **README-PLUGIN.md** - Complete plugin documentation
- **TESTING.md** - How to test the plugin
- **EXAMPLES.md** - How to extend the plugin
- **PROJECT-SUMMARY.md** - Project summary for presentation
- **QUICK-REFERENCE.md** - This file

## REST API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/wp-abilities/v1/abilities` | GET | List all abilities |
| `/wp-abilities/v1/abilities/{name}` | GET | Get ability details |
| `/wp-abilities/v1/abilities/{name}/run` | POST | Execute ability |

## Common Use Cases

1. **Public Form Submission** - Use submit-form ability
2. **Admin Dashboard** - Use count abilities
3. **Form Analytics** - Use count-submissions with form_id
4. **Submission Review** - Use get-submission ability

## Security Notes

- ✅ All inputs sanitized
- ✅ All queries use prepared statements
- ✅ Permission callbacks on protected abilities
- ✅ Email validation before storage
- ✅ Form existence checks

## Support

For issues or questions:
- Review the documentation files
- Check TESTING.md for common issues
- See EXAMPLES.md for extension patterns

## Version

Current version: 1.0.0

## License

GPL v2 or later
