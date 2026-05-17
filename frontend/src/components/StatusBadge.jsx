const colors = {
  pending: 'bg-amber-500/10 text-amber-400',
  waiting: 'bg-blue-500/10 text-blue-400',
  received: 'bg-emerald-500/10 text-emerald-400',
  cancelled: 'bg-gray-500/10 text-gray-400',
  expired: 'bg-red-500/10 text-red-400',
  refunded: 'bg-purple-500/10 text-purple-400',
  paid: 'bg-emerald-500/10 text-emerald-400',
  failed: 'bg-red-500/10 text-red-400',
  success: 'bg-emerald-500/10 text-emerald-400',
};

export default function StatusBadge({ status }) {
  return (
    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium ${colors[status] || colors.pending}`}>
      {status}
    </span>
  );
}
