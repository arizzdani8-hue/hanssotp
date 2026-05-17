const HeroSmsProvider = require('./HeroSmsProvider');
const settingsService = require('../services/settingsService');
const config = require('../config');
const logger = require('../utils/logger');

const providerInstances = {};

async function getProvider(slug) {
  if (providerInstances[slug]) return providerInstances[slug];

  const settings = await settingsService.getAll();

  switch (slug) {
    case 'herosms': {
      const apiKey = settings.api_key_herosms || config.herosms.apiKey;
      if (!apiKey) throw new Error('HeroSMS API key not configured');
      const apiUrl = config.herosms.apiUrl || 'https://api.hero-sms.com';
      providerInstances[slug] = new HeroSmsProvider(apiKey, apiUrl);
      break;
    }
    default:
      throw new Error(`Unknown provider: ${slug}`);
  }

  return providerInstances[slug];
}

function clearProviderCache() {
  for (const key of Object.keys(providerInstances)) {
    delete providerInstances[key];
  }
}

module.exports = { getProvider, clearProviderCache };
