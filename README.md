# Payment Event Processor

Event-driven payment ingestion with a transactional outbox, Symfony Messenger (AMQP), and async handlers.

## Architecture

```
POST /api/payment-events
  → payment_events + outbox_events (same DB transaction)

outbox-worker: app:process-outbox --watch
  → FOR UPDATE SKIP LOCKED batch dispatch
  → RabbitMQ (async transport)

messenger-worker: messenger:consume async
  → PaymentEventHandler marks outbox processed
```

Stale recovery: if an event was dispatched but not processed within `OUTBOX_STALE_AFTER_SECONDS` (default 300s), the outbox poller clears `dispatched_at` so it can be dispatched again. The handler is idempotent on `processed`.

## Docker (development)

Start the stack including workers:

```bash
docker compose up -d --build
```

Services:

| Service | Role |
|---------|------|
| `php` | HTTP API (FrankenPHP) |
| `database` | PostgreSQL |
| `rabbitmq` | AMQP broker (management UI on port 15672) |
| `outbox-worker` | Polls and dispatches outbox events |
| `messenger-worker` | Consumes `async` queue |

## Manual commands

```bash
# One-shot outbox poll
docker compose exec php bin/console app:process-outbox

# Continuous poll (same as outbox-worker)
docker compose exec php bin/console app:process-outbox --watch

# Consume messages manually
docker compose exec php bin/console messenger:consume async -vv

# Inspect failed messages
docker compose exec php bin/console messenger:failed:show
```

## Configuration

| Variable | Default | Description |
|----------|---------|-------------|
| `OUTBOX_BATCH_SIZE` | `50` | Max events per poll |
| `OUTBOX_STALE_AFTER_SECONDS` | `300` | Re-dispatch threshold for stuck events |
| `OUTBOX_POLL_INTERVAL_SECONDS` | `5` | Sleep between polls in `--watch` mode |
| `MESSENGER_TRANSPORT_DSN` | AMQP URL | Async transport |

## Example ingest

```bash
curl -s -X POST http://localhost/api/payment-events \
  -H 'Content-Type: application/json' \
  -d '{
    "eventId": "550e8400-e29b-41d4-a716-446655440000",
    "type": "payment.completed",
    "paymentId": "pay_123",
    "payload": {"amount": 1000, "currency": "EUR"}
  }'
```

Duplicate `eventId` returns HTTP 409.
