const FiveSimProvider = require('./FiveSimProvider');
const HeroSmsProvider = require('./HeroSmsProvider');
const NokosmurahProvider = require('./NokosmurahProvider');
const settingsService = require('../services/settingsService');
const config = require('../config');
const logger = require('../utils/logger');

const providerInstances = {};

async function getProvider(slug) {
  if (providerInstances[slug]) return providerInstances[slug];

  const settings = await settingsService.getAll();

  switch (slug) {
    case '5sim': {
      const apiKey = settings.api_key_5sim || config.fivesim.apiKey;
      if (!apiKey) throw new Error('5sim API key not configured');
      providerInstances[slug] = new FiveSimProvider(apiKey);
      break;
    }
    case 'herosms': {
      const apiKey = settings.api_key_herosms || config.herosms.apiKey;
      if (!apiKey) throw new Error('HeroSMS API key not configured');
      providerInstances[slug] = new HeroSmsProvider(apiKey);
      break;
    }
    case 'nokosmurah': {
      const apiKey = settings.api_key_nokosmurah || config.nokosmurah.apiKey;
      if (!apiKey) throw new Error('Nokosmurah API key not configured');
      providerInstances[slug] = new NokosmurahProvider(apiKey);
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
