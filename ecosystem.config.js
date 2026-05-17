module.exports = {
  apps: [
    {
      name: 'nyooapp-backend',
      cwd: '/www/wwwroot/nyooapp.shop/backend',
      script: 'server.js',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '512M',
      env: {
        NODE_ENV: 'production',
        PORT: 5000,
      },
      error_file: '/www/wwwroot/nyooapp.shop/logs/backend-error.log',
      out_file: '/www/wwwroot/nyooapp.shop/logs/backend-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      merge_logs: true,
    },
  ],
};
