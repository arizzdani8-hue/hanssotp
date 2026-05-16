const PakasirGateway = require('./PakasirGateway');

const gateways = {
  pakasir: new PakasirGateway(),
};

function getGateway(slug) {
  const gw = gateways[slug];
  if (!gw) throw new Error(`Unknown payment gateway: ${slug}`);
  return gw;
}

module.exports = { getGateway };
