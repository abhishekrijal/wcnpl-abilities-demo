# Using this plugin from Cursor via MCP

This plugin registers WordPress **Abilities** and exposes them over WordPress REST:

- List abilities: `GET /wp-json/wp-abilities/v1/abilities`
- Get ability details: `GET /wp-json/wp-abilities/v1/abilities/{name}`
- Run an ability: `POST /wp-json/wp-abilities/v1/abilities/{name}/run`

Cursor does **not** talk to WordPress plugins directly. Cursor talks to an **MCP server process** (a local Node/Python program) which then calls your WordPress REST endpoints.

This guide sets up a small Node.js MCP “bridge” server that turns your abilities into Cursor tools.

## Prereqs

- WordPress site running this plugin
- For admin-only abilities, create a WP **Application Password** for an admin user:
  - WP Admin → Users → Profile → Application Passwords

## 1) Create the MCP bridge (Node)

In this plugin folder, create a new directory `mcp/` with these files:

- `mcp/package.json`
- `mcp/server.js`

See the code in `mcp/server.js` and `mcp/package.json` (added in this repo).

Then install dependencies:

```bash
cd /path/to/wp-content/plugins/wcnpl-abilities-demo/mcp
npm install
```

## 2) Configure Cursor to run the MCP server

Create (or edit) your Cursor MCP config and add a server entry that runs the bridge.
Depending on your setup, Cursor will look for either a global config or a per-project config.

Use this shape:

```json
{
  "mcpServers": {
    "wcnpl-wp-abilities": {
      "command": "node",
      "args": ["/ABS/PATH/wp-content/plugins/wcnpl-abilities-demo/mcp/server.js"],
      "env": {
        "WP_BASE_URL": "https://your-site.test",
        "WP_USERNAME": "admin",
        "WP_APP_PASSWORD": "xxxx xxxx xxxx xxxx xxxx xxxx"
      }
    }
  }
}
```

Notes:
- `WP_BASE_URL` should be your site origin (no trailing slash), e.g. `https://example.test`
- For **public** abilities (like submit-form), auth isn’t required, but it’s fine to provide it anyway.
- For protected abilities, you must provide an admin user + app password.

Restart Cursor after editing MCP config.

## 3) What tools you get in Cursor

The bridge exposes these MCP tools (mapping 1:1 to the plugin’s abilities):

- `wcnpl_submit_form` → runs `wcnpl-abilities-demo/submit-form`
- `wcnpl_get_submission` → runs `wcnpl-abilities-demo/get-submission` (admin)
- `wcnpl_count_forms` → runs `wcnpl-abilities-demo/count-forms` (admin)
- `wcnpl_count_submissions` → runs `wcnpl-abilities-demo/count-submissions` (admin)

## 4) Troubleshooting

- **401/403 on admin abilities**: verify Application Password, username, and that the user has `manage_options`.
- **REST route not found**: confirm WordPress is 6.9+, and `GET /wp-json/wp-abilities/v1/abilities` works in a browser/curl.
- **TLS / self-signed cert issues** (local dev): use a trusted local domain/cert (e.g. Valet, mkcert) rather than disabling verification.

