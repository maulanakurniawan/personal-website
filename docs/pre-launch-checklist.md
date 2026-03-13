# Pre-Launch Checklist

## Product readiness
- [ ] Confirm core user flows work end-to-end (signup, webhook creation, delivery, logs).
- [ ] Verify webhook delivery retries and backoff behavior.
- [ ] Validate webhook signature verification guidance is documented.
- [ ] Ensure alerting works for failed deliveries.
- [ ] Confirm data retention policy and deletion workflows.

## Security & compliance
- [ ] Verify HTTPS everywhere and HSTS enabled.
- [ ] Audit environment variables and secrets handling.
- [ ] Run dependency vulnerability scan and address critical issues.
- [ ] Ensure least-privilege access for service accounts.
- [ ] Verify privacy policy and terms are published.

## Infrastructure & reliability
- [ ] Confirm database backups and restore procedures.
- [ ] Validate uptime monitoring and incident alerting.
- [ ] Load test webhook delivery throughput and queue behavior.
- [ ] Ensure logging and tracing are sufficient for debugging.
- [ ] Review rate limits and abuse protections.

## Analytics & tracking
- [ ] Confirm analytics events for signup, activation, and retention.
- [ ] Verify conversion funnels and dashboards.
- [ ] Set up error monitoring (frontend + backend).

## Support & operations
- [ ] Prepare support inbox and response templates.
- [ ] Document common troubleshooting steps.
- [ ] Create internal runbook for outages.
