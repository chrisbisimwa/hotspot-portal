import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend, Counter } from 'k6/metrics';

// Custom metrics
const loginSuccessRate = new Rate('login_success_rate');
const apiResponseTime = new Trend('api_response_time');
const errorCount = new Counter('errors');

// Test configuration
export const options = {
  stages: [
    { duration: '30s', target: 10 }, // Ramp up
    { duration: '1m', target: 20 },  // Stay at 20 users
    { duration: '30s', target: 0 },  // Ramp down
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% of requests under 500ms
    login_success_rate: ['rate>0.95'], // 95% login success rate
    errors: ['count<10'], // Less than 10 errors total
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';

// Test data
const testUsers = [
  { email: 'admin@demo.test', password: 'password' },
  // Add more test users as needed
];

export function setup() {
  console.log(`Starting load test against ${BASE_URL}`);
  
  // Test basic connectivity
  const response = http.get(`${BASE_URL}/health/live`);
  check(response, {
    'health endpoint is up': (r) => r.status === 200,
  });
  
  return { baseUrl: BASE_URL };
}

export default function (data) {
  const user = testUsers[Math.floor(Math.random() * testUsers.length)];
  
  // Test scenarios with different weights
  const scenario = Math.random();
  
  if (scenario < 0.3) {
    // 30% - User login and dashboard access
    testUserLoginFlow(data.baseUrl, user);
  } else if (scenario < 0.6) {
    // 30% - API user profiles access (public)
    testPublicApiAccess(data.baseUrl);
  } else if (scenario < 0.8) {
    // 20% - Create order flow (requires login)
    testCreateOrderFlow(data.baseUrl, user);
  } else {
    // 20% - Health check and metrics access
    testMonitoringEndpoints(data.baseUrl);
  }
  
  sleep(1); // Wait between iterations
}

function testUserLoginFlow(baseUrl, user) {
  const loginResponse = http.post(`${baseUrl}/api/v1/auth/login`, {
    email: user.email,
    password: user.password,
  }, {
    headers: { 'Content-Type': 'application/json' },
  });
  
  const loginSuccess = check(loginResponse, {
    'login status is 200': (r) => r.status === 200,
    'login returns token': (r) => r.json('data.token') !== undefined,
  });
  
  loginSuccessRate.add(loginSuccess);
  
  if (loginSuccess) {
    const token = loginResponse.json('data.token');
    
    // Access user dashboard data
    const meResponse = http.get(`${baseUrl}/api/v1/me`, {
      headers: { 'Authorization': `Bearer ${token}` },
    });
    
    check(meResponse, {
      'me endpoint returns user data': (r) => r.status === 200,
    });
    
    // Access user orders
    const ordersResponse = http.get(`${baseUrl}/api/v1/orders`, {
      headers: { 'Authorization': `Bearer ${token}` },
    });
    
    check(ordersResponse, {
      'orders endpoint accessible': (r) => r.status === 200,
    });
    
    apiResponseTime.add(meResponse.timings.duration);
  } else {
    errorCount.add(1);
  }
}

function testPublicApiAccess(baseUrl) {
  const profilesResponse = http.get(`${baseUrl}/api/v1/user-profiles`);
  
  const success = check(profilesResponse, {
    'public API status is 200': (r) => r.status === 200,
    'public API returns data structure': (r) => {
      const json = r.json();
      return json.success !== undefined && json.data !== undefined;
    },
    'response time under 300ms': (r) => r.timings.duration < 300,
  });
  
  if (!success) {
    errorCount.add(1);
  }
  
  apiResponseTime.add(profilesResponse.timings.duration);
}

function testCreateOrderFlow(baseUrl, user) {
  // Login first
  const loginResponse = http.post(`${baseUrl}/api/v1/auth/login`, {
    email: user.email,
    password: user.password,
  }, {
    headers: { 'Content-Type': 'application/json' },
  });
  
  if (loginResponse.status !== 200) {
    errorCount.add(1);
    return;
  }
  
  const token = loginResponse.json('data.token');
  
  // Create order (placeholder - would need actual user profile IDs)
  const createOrderResponse = http.post(`${baseUrl}/api/v1/orders`, {
    user_profile_id: 1, // This would need to be dynamic in real test
    quantity: 1,
  }, {
    headers: { 
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
  });
  
  const orderSuccess = check(createOrderResponse, {
    'order creation works': (r) => r.status === 200 || r.status === 201,
  });
  
  if (!orderSuccess) {
    errorCount.add(1);
  }
}

function testMonitoringEndpoints(baseUrl) {
  // Test health endpoints
  const liveResponse = http.get(`${baseUrl}/health/live`);
  const readyResponse = http.get(`${baseUrl}/health/ready`);
  
  check(liveResponse, {
    'health live endpoint works': (r) => r.status === 200,
  });
  
  check(readyResponse, {
    'health ready endpoint works': (r) => r.status === 200,
  });
  
  // Test metrics endpoint (would need valid token in real scenario)
  const metricsResponse = http.get(`${baseUrl}/internal/metrics`, {
    headers: { 'Authorization': 'Bearer test_token' },
  });
  
  // We expect 401 without valid token, which is correct
  check(metricsResponse, {
    'metrics endpoint is protected': (r) => r.status === 401,
  });
}

export function teardown(data) {
  console.log('Load test completed');
  
  // You could add cleanup logic here if needed
  // For example, deleting test data that was created
}

export function handleSummary(data) {
  return {
    'stdout': textSummary(data, { indent: ' ', enableColors: true }),
    'load-test-results.json': JSON.stringify(data),
  };
}

// Simple text summary (k6 built-in function would be used in real k6)
function textSummary(data, options = {}) {
  const indent = options.indent || '';
  const enableColors = options.enableColors || false;
  
  return `
${indent}============= LOAD TEST SUMMARY =============
${indent}Total Requests: ${data.metrics.http_reqs?.values?.count || 0}
${indent}Failed Requests: ${data.metrics.http_req_failed?.values?.passes || 0}
${indent}Average Response Time: ${(data.metrics.http_req_duration?.values?.avg || 0).toFixed(2)}ms
${indent}95th Percentile: ${(data.metrics.http_req_duration?.values?.['p(95)'] || 0).toFixed(2)}ms
${indent}Login Success Rate: ${((data.metrics.login_success_rate?.values?.rate || 0) * 100).toFixed(1)}%
${indent}Error Count: ${data.metrics.errors?.values?.count || 0}
${indent}==========================================
  `;
}