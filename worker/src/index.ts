import PostalMime from "postal-mime";

type Env = {
  LARAVEL_INBOUND_URL: string;
  INBOUND_EMAIL_TOKEN: string;
  FORWARD_TO_EMAIL?: string;
};

export default {
  async email(message: ForwardableEmailMessage, env: Env, ctx: ExecutionContext): Promise<void> {
    const raw = await new Response(message.raw).arrayBuffer();
    const parsed = await PostalMime.parse(raw);
    const form = new FormData();

    form.append("provider", "cloudflare-email-routing");
    form.append("message_id", message.headers.get("message-id") || "");
    form.append("from_email", message.from);
    form.append("to_email", message.to);
    form.append("subject", parsed.subject || message.headers.get("subject") || "");
    form.append("attachment_count", String(parsed.attachments.length));

    parsed.attachments.forEach((attachment, index) => {
      const blob = new Blob([attachment.content], {
        type: attachment.mimeType || "application/octet-stream",
      });

      form.append(
        `attachments[${index}]`,
        blob,
        attachment.filename || `attachment-${index + 1}`,
      );
    });

    const ingest = fetch(env.LARAVEL_INBOUND_URL, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${env.INBOUND_EMAIL_TOKEN}`,
      },
      body: form,
    }).then(async (response) => {
      if (!response.ok) {
        throw new Error(`Laravel inbound failed: ${response.status} ${await response.text()}`);
      }
    });

    ctx.waitUntil(ingest);

    if (env.FORWARD_TO_EMAIL) {
      await message.forward(env.FORWARD_TO_EMAIL);
      return;
    }

    await ingest;
  },
} satisfies ExportedHandler<Env>;
