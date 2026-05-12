const { pool } = require('../config/database');

async function log({ actorType, actorId, action, targetType, targetId, details, ipAddress }) {
  await pool.query(
    `INSERT INTO activity_logs (actor_type, actor_id, action, target_type, target_id, details, ip_address)
     VALUES (?, ?, ?, ?, ?, ?, ?)`,
    [actorType, actorId || null, action, targetType || null, targetId || null, details ? JSON.stringify(details) : null, ipAddress || null]
  );
}

async function getList({ page = 1, limit = 20, actorType, action }) {
  let where = '1=1';
  const params = [];
  if (actorType) { where += ' AND actor_type = ?'; params.push(actorType); }
  if (action) { where += ' AND action LIKE ?'; params.push(`%${action}%`); }
  const offset = (page - 1) * limit;
  const [rows] = await pool.query(
    `SELECT * FROM activity_logs WHERE ${where} ORDER BY created_at DESC LIMIT ? OFFSET ?`,
    [...params, limit, offset]
  );
  const [[{ total }]] = await pool.query(`SELECT COUNT(*) as total FROM activity_logs WHERE ${where}`, params);
  return { data: rows, total, page, limit };
}

module.exports = { log, getList };
