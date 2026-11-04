<?php
/**
 * api/health.php
 *
 * Dedicated JSON health check endpoint for automated monitoring probes
 * (Azure App Service Health Check, Kubernetes, UptimeRobot, Pingdom).
 *
 * Always returns application/json with appropriate HTTP status codes:
 * - 200 OK: All systems operational
 * - 503 Service Unavailable: Core dependencies degraded or unreachable
 */

$_GET['format'] = 'json';
require_once __DIR__ . '/../health.php';

