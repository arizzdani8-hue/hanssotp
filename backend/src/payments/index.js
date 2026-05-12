const TripayGateway = require('./TripayGateway');
const QrispyGateway = require('./QrispyGateway');

const gateways = {
  tripay: new TripayGateway(),
  qrispy: new QrispyGateway(),
};

function getGateway(slug) {
  const gw = gateways[slug];
  if (!gw) throw new Error(`Unknown payment gateway: ${slug}`);
  return gw;
}

module.exports = { getGateway };
