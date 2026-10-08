// Observed WebSocket transport, not an event fake. Node >=22, local stack only.
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';

const container = 'lala-corporate-acceptance-app-1';
for (const [name, expected] of Object.entries({
  APP_ENV: 'staging', DB_DATABASE: 'lala_corporate_acceptance_test',
  MAIL_HOST: 'mailpit', OTP_DEMO_MODE: 'true',
})) {
  assert.equal(execFileSync('docker', ['exec', container, 'printenv', name], { encoding: 'utf8' }).trim(), expected,
    `Refusing non-isolated acceptance: ${name}`);
}
const api = 'http://127.0.0.1:8095/api';
let checks = 0;
function passed(label) { checks++; process.stdout.write(`PASS ${label}\n`); }
async function request(path, token, body) {
  const response = await fetch(`${api}${path}`, {
    method: body ? 'POST' : 'GET',
    headers: { Accept: 'application/json', ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(body ? { 'Content-Type': 'application/json' } : {}) },
    ...(body ? { body: JSON.stringify(body) } : {}),
    signal: AbortSignal.timeout(15000),
  });
  return { status: response.status, data: await response.json() };
}
async function login(phone, type) {
  const query = new URLSearchParams({ mobile_number: phone, country_code: 'KE', user_type: type, is_login: '1', language: 'en' });
  const issued = await request(`/numbervalidation?${query}`);
  assert.equal(issued.data.status_code, '1');
  query.set('otp', '123456');
  const result = await request(`/otp_verification?${query}`);
  assert.equal(result.data.status_code, '1');
  const token = result.data.access_token;
  assert.ok(token);
  const userId = JSON.parse(Buffer.from(token.split('.')[1], 'base64url')).sub;
  passed(`${type} actual OTP authentication`);
  return { token, userId };
}
function openSocket() {
  // No Origin header, matching Android/iOS native Pusher handshakes.
  const socket = new WebSocket('ws://127.0.0.1:8096/app/corporate-acceptance-public-key?protocol=7&client=acceptance&version=1.0');
  const received = [];
  socket.addEventListener('message', event => {
    const value = JSON.parse(event.data);
    if (value.event === 'pusher:ping') socket.send(JSON.stringify({ event: 'pusher:pong', data: {} }));
    received.push(value);
  });
  const waitFor = async (event, predicate = () => true) => {
    const deadline = Date.now() + 15000;
    while (Date.now() < deadline) {
      const index = received.findIndex(value => value.event === event && predicate(value));
      if (index >= 0) return received.splice(index, 1)[0];
      if (socket.readyState === WebSocket.CLOSED) throw new Error(`Socket closed before ${event}`);
      await new Promise(resolve => setTimeout(resolve, 50));
    }
    throw new Error(`Socket timeout: ${event}`);
  };
  return { socket, waitFor };
}

const driver = await login('700000140', 'Driver');
const rider = await login('700000110', 'Rider');
const channel = `private-driver.${driver.userId}`;
for (const attempt of [1, 2]) {
  const { socket, waitFor } = openSocket();
  try {
    const established = await waitFor('pusher:connection_established');
    const socketId = JSON.parse(established.data).socket_id;
    assert.ok(socketId);
    passed(`Native-origin socket handshake ${attempt}`);
    const denied = await request('/broadcasting/auth', rider.token, { socket_id: socketId, channel_name: channel });
    assert.equal(denied.status, 403);
    passed('Rider denied private driver subscription');
    const auth = await request('/broadcasting/auth', driver.token, { socket_id: socketId, channel_name: channel });
    assert.equal(auth.status, 200);
    assert.ok(auth.data.auth);
    socket.send(JSON.stringify({ event: 'pusher:subscribe', data: { channel, auth: auth.data.auth } }));
    await waitFor('pusher_internal:subscription_succeeded');
    passed(`Own driver channel subscription ${attempt}`);
    const offline = await request('/driver/offline', driver.token, {});
    assert.equal(offline.data.status_code, '1');
    const event = await waitFor('DriverOnlineStatusUpdated');
    const payload = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
    assert.equal(Number(payload.driver_id ?? payload.id), Number(driver.userId));
    assert.equal(Boolean(payload.is_online), false);
    passed(`Real queued availability broadcast received ${attempt}`);
  } finally { socket.close(); }
}
process.stdout.write(`Observed ${checks} socket/auth/reconnect checks; driver left offline.\n`);
