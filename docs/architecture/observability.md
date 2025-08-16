# Observability & Scaling Architecture

## Overview

Step 11 introduces a comprehensive observability and scaling infrastructure to the HotspotPortal application. This includes distributed tracing, metrics collection, structured logging, performance optimizations, and resilience features.

## Components

### 1. Observability Stack

#### Traces & Correlation
- **CorrelationIdMiddleware**: Adds X-Request-Id headers for request tracking
- **OpenTelemetry Configuration**: Basic instrumentation setup (TODO: full implementation)
- **TraceContextProcessor**: Injects trace context into logs

#### Metrics (Prometheus Compatible)
- **MetricsController**: Exposes `/internal/metrics` endpoint in Prometheus format
- **QueueLoadMonitor**: Monitors queue depth and processing rates
- **AdaptiveRateLimiter**: Tracks rate limiting metrics
- **SLA Metrics**: Leverages existing SlaRecorder for latency tracking

#### Structured Logging
- **StructuredLog Helper**: Standardized logging with context and sanitization
- **SlowQueryListener**: Logs database queries exceeding threshold
- **Structured Channel**: JSON-formatted logs for parsing

### 2. Performance & Database

#### Index Optimization
- **DbAuditIndexesCommand**: Analyzes database schema for missing indexes
- **Suggested Indexes**: Composite indexes for common query patterns
- **Migration Templates**: Ready-to-use index additions

#### Caching Strategy
- **CacheWarmCommand**: Pre-warms frequently accessed data
- **ApiCacheMiddleware**: ETag/Cache-Control for public endpoints
- **Multi-layer TTL**: Different cache durations by data type

#### Query Monitoring
- **SlowQueryListener**: Tracks queries > configurable threshold (default 120ms)
- **Query Metrics**: Integrated with metrics collection

### 3. Resilience & Scalability

#### Queue Management
- **Multi-queue Strategy**: critical, high, default, low priority queues
- **QueueLoadMonitor**: Real-time queue depth monitoring
- **MonitorQueuesJob**: Automated queue health checks and alerting

#### Health Checks
- **HealthController**: Liveness, readiness, and detailed health endpoints
- **Individual Checks**: Database, Redis, Queue, MikroTik, Payment latency
- **Health Standards**: Industry-standard health check format

#### Feature Flags
- **FeatureFlag Model**: Database-backed feature toggles
- **Feature Service**: Cached flag resolution with invalidation
- **CLI Management**: Commands for enable/disable/list operations

### 4. Rate Limiting & Security

#### Adaptive Rate Limiting
- **AdaptiveRateLimiter**: Dynamic limits based on system load
- **Load Factors**: Queue depth, CPU, memory considerations
- **Role-based Limits**: Different limits for admin/agent/user/guest

#### Security Headers
- **SecurityHeadersMiddleware**: CSP, frame options, content type protection
- **Bruteforce Protection**: TODO - rate limiting on auth endpoints
- **Secret Management**: TODO - rotation procedures

#### Chaos Engineering
- **ChaosMiddleware**: Fault injection for staging environments
- **Configurable Chaos**: Error rates, latency injection, timeouts
- **Safety Limits**: Only active in staging environment

## Configuration

### Environment Variables

```bash
# OpenTelemetry
OTEL_TRACES_ENABLED=false
OTEL_TRACES_SAMPLER_RATIO=0.2
OTEL_EXPORTER_OTLP_TRACES_ENDPOINT=http://localhost:4318/v1/traces

# Metrics
INTERNAL_METRICS_TOKEN=changeme
INTERNAL_METRICS_ALLOWED_IPS=

# Performance
DB_SLOW_MS=120
CACHE_WARM_ENABLED=true

# Queue Monitoring
QUEUE_CRITICAL_MAX=100
QUEUE_CRITICAL_AGE_MAX=60

# Rate Limiting
ADAPTIVE_RATE_BASE_USER=120
ADAPTIVE_RATE_BASE_ADMIN=600

# Chaos (Staging Only)
CHAOS_ENABLED=false
CHAOS_ERROR_RATE=0.05
```

### Cache Configuration

Different TTL values for various data types:
- User profiles: 5 minutes
- Feature flags: 1 minute
- Global metrics: 1 minute
- Registry reports: 15 minutes

## Endpoints

### Health Checks
- `GET /health/live` - Basic liveness probe
- `GET /health/ready` - Readiness check with dependencies
- `GET /health/summary` - Quick health overview

### Metrics
- `GET /internal/metrics` - Prometheus format metrics (protected)

### Feature Flags CLI
```bash
php artisan feature:enable feature_key
php artisan feature:disable feature_key
php artisan feature:list
```

### Maintenance Commands
```bash
php artisan cache:warm
php artisan db:audit-indexes
php artisan load:baseline
php artisan chaos:toggle enable  # staging only
```

## Monitoring Integration

### Prometheus Setup (TODO)
```yaml
# prometheus.yml
scrape_configs:
  - job_name: 'hotspot-portal'
    static_configs:
      - targets: ['app:8000']
    metrics_path: '/internal/metrics'
    bearer_token: 'your_metrics_token'
```

### Grafana Dashboards (TODO)
- Application metrics dashboard
- Queue monitoring dashboard
- Health status overview
- Error rate and latency tracking

## Load Testing

Basic k6 script provided in `scripts/load/basic.js`:

```bash
k6 run scripts/load/basic.js
```

Scenarios covered:
- User login flow
- Public API access
- Order creation
- Health endpoint checks

## Future Enhancements (TODO)

1. **Full OpenTelemetry Integration**
   - Automatic instrumentation
   - Distributed tracing
   - Custom spans and attributes

2. **Advanced Metrics**
   - Business metrics (revenue, user growth)
   - Custom histograms and counters
   - SLI/SLO tracking

3. **Enhanced Security**
   - Secret rotation automation
   - Advanced bruteforce protection
   - Security event logging

4. **Scalability Features**
   - Auto-scaling policies
   - Load balancer health checks
   - Circuit breakers

5. **Operational Tools**
   - Real-time dashboard
   - Alert routing
   - Incident management integration