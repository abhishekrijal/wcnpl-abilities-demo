import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";

function requiredEnv(name) {
  const v = process.env[name];
  if (!v) throw new Error(`Missing required env var: ${name}`);
  return v;
}

function wpAuthHeader() {
  const user = process.env.WP_USERNAME;
  const pass = process.env.WP_APP_PASSWORD;
  if (!user || !pass) return {};
  const token = Buffer.from(`${user}:${pass}`, "utf8").toString("base64");
  return { Authorization: `Basic ${token}` };
}

async function wpAbilityRun(baseUrl, abilityName, input) {
  const url = `${baseUrl}/wp-json/wp-abilities/v1/abilities/${encodeURIComponent(
    abilityName
  )}/run`;
  const res = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      ...wpAuthHeader(),
    },
    body: JSON.stringify(input ?? {}),
  });

  const text = await res.text();
  let json;
  try {
    json = text ? JSON.parse(text) : null;
  } catch {
    json = { raw: text };
  }

  if (!res.ok) {
    const msg =
      (json && (json.message || json.code)) ||
      `HTTP ${res.status} ${res.statusText}`;
    throw new Error(`Ability run failed: ${abilityName}: ${msg}`);
  }

  return json;
}

async function wpFallbackRun(baseUrl, toolName, input) {
  const routes = {
    wcnpl_submit_form: "/wp-json/wcnpl-abilities-demo/v1/submit-form",
    wcnpl_get_submission: "/wp-json/wcnpl-abilities-demo/v1/get-submission",
    wcnpl_count_forms: "/wp-json/wcnpl-abilities-demo/v1/count-forms",
    wcnpl_count_submissions: "/wp-json/wcnpl-abilities-demo/v1/count-submissions",
  };

  const path = routes[toolName];
  if (!path) throw new Error(`No fallback route for tool: ${toolName}`);

  const url = `${baseUrl}${path}`;
  const res = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      ...wpAuthHeader(),
    },
    body: JSON.stringify(input ?? {}),
  });

  const text = await res.text();
  let json;
  try {
    json = text ? JSON.parse(text) : null;
  } catch {
    json = { raw: text };
  }

  if (!res.ok) {
    const msg =
      (json && (json.message || json.code)) ||
      `HTTP ${res.status} ${res.statusText}`;
    throw new Error(`Fallback run failed: ${toolName}: ${msg}`);
  }

  return json;
}

const WP_BASE_URL = requiredEnv("WP_BASE_URL").replace(/\/+$/, "");

// Keep this intentionally small and explicit. You can add more tools later.
const TOOLS = [
  {
    name: "wcnpl_submit_form",
    description:
      "Run WordPress ability wcnpl-abilities-demo/submit-form (public): submit a form entry.",
    ability: "wcnpl-abilities-demo/submit-form",
    inputSchema: {
      type: "object",
      properties: {
        form_id: { type: "integer", minimum: 1 },
        name: { type: "string", minLength: 1 },
        email: { type: "string", minLength: 1 },
        message: { type: "string", minLength: 1 },
      },
      required: ["form_id", "name", "email", "message"],
      additionalProperties: false,
    },
  },
  {
    name: "wcnpl_get_submission",
    description:
      "Run WordPress ability wcnpl-abilities-demo/get-submission (admin): fetch a submission by ID.",
    ability: "wcnpl-abilities-demo/get-submission",
    inputSchema: {
      type: "object",
      properties: { submission_id: { type: "integer", minimum: 1 } },
      required: ["submission_id"],
      additionalProperties: false,
    },
  },
  {
    name: "wcnpl_count_forms",
    description:
      "Run WordPress ability wcnpl-abilities-demo/count-forms (admin): count forms.",
    ability: "wcnpl-abilities-demo/count-forms",
    inputSchema: { type: "object", properties: {}, additionalProperties: false },
  },
  {
    name: "wcnpl_count_submissions",
    description:
      "Run WordPress ability wcnpl-abilities-demo/count-submissions (admin): count submissions (optionally by form_id).",
    ability: "wcnpl-abilities-demo/count-submissions",
    inputSchema: {
      type: "object",
      properties: { form_id: { type: "integer", minimum: 1 } },
      additionalProperties: false,
    },
  },
];

const server = new Server(
  { name: "wcnpl-wp-abilities", version: "1.0.0" },
  { capabilities: { tools: {} } }
);

server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: TOOLS.map((t) => ({
      name: t.name,
      description: t.description,
      inputSchema: t.inputSchema,
    })),
  };
});

server.setRequestHandler(CallToolRequestSchema, async (req) => {
  const tool = TOOLS.find((t) => t.name === req.params.name);
  if (!tool) throw new Error(`Unknown tool: ${req.params.name}`);

  let result;
  try {
    result = await wpAbilityRun(
      WP_BASE_URL,
      tool.ability,
      req.params.arguments ?? {}
    );
  } catch (err) {
    const msg = String(err && err.message ? err.message : err);
    // If the Abilities API REST route isn't available, fall back to the plugin's own REST routes.
    if (
      msg.includes("No route was found") ||
      msg.includes("rest_no_route") ||
      msg.includes("/wp-abilities/")
    ) {
      result = await wpFallbackRun(WP_BASE_URL, tool.name, req.params.arguments ?? {});
    } else {
      throw err;
    }
  }

  return {
    content: [
      {
        type: "text",
        text: JSON.stringify(result, null, 2),
      },
    ],
  };
});

const transport = new StdioServerTransport();
await server.connect(transport);

