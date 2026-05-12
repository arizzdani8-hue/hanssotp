const { pool } = require('../config/database');

const cache = new Map();
let cacheTimestamp = 0;
const CACHE_TTL = 60000;

async function getAll() {
  if (Date.now() - cacheTimestamp < CACHE_TTL && cache.size > 0) {
    return Object.fromEntries(cache);
  }
  const [rows] = await pool.query('SELECT `key`, value, type FROM website_settings');
  cache.clear();
  for (const row of rows) {
    let val = row.value;
    if (row.type === 'number') val = parseFloat(val);
    else if (row.type === 'boolean') val = val === 'true';
    else if (row.type === 'json') {
      try { val = JSON.parse(val); } catch { /* keep string */ }
    }
    cache.set(row.key, val);
  }
  cacheTimestamp = Date.now();
  return Object.fromEntries(cache);
}

async function get(key) {
  const all = await getAll();
  return all[key];
}

async function set(key, value) {
  await pool.query(
    'UPDATE website_settings SET value = ? WHERE `key` = ?',
    [String(value), key]
  );
  cache.delete(key);
  cacheTimestamp = 0;
}

async function setMany(pairs) {
  const conn = await pool.getConnection();
  try {
    await conn.beginTransaction();
    for (const [key, value] of Object.entries(pairs)) {
      await conn.query('UPDATE website_settings SET value = ? WHERE `key` = ?', [String(value), key]);
    }
    await conn.commit();
    cacheTimestamp = 0;
    cache.clear();
  } catch (err) {
    await conn.rollback();
    throw err;
  } finally {
    conn.release();
  }
}

module.exports = { getAll, get, set, setMany };
