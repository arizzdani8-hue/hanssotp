export default function Pagination({ page, total, limit, onChange }) {
  const totalPages = Math.ceil(total / limit);
  if (totalPages <= 1) return null;

  const pages = [];
  for (let i = Math.max(1, page - 2); i <= Math.min(totalPages, page + 2); i++) {
    pages.push(i);
  }

  return (
    <div className="flex items-center justify-center gap-2 mt-4">
      <button onClick={() => onChange(page - 1)} disabled={page <= 1}
        className="px-3 py-1.5 rounded-lg text-sm border border-gray-700/50 text-gray-400 hover:text-white hover:border-gray-600 disabled:opacity-50 transition-colors">
        Prev
      </button>
      {pages.map((p) => (
        <button key={p} onClick={() => onChange(p)}
          className={`px-3 py-1.5 rounded-lg text-sm border transition-colors ${p === page ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-700/50 text-gray-400 hover:text-white hover:border-gray-600'}`}>
          {p}
        </button>
      ))}
      <button onClick={() => onChange(page + 1)} disabled={page >= totalPages}
        className="px-3 py-1.5 rounded-lg text-sm border border-gray-700/50 text-gray-400 hover:text-white hover:border-gray-600 disabled:opacity-50 transition-colors">
        Next
      </button>
    </div>
  );
}
