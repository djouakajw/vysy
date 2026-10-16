export default function Dashboard() {
  return (
    <main className="min-h-screen bg-gray-50 p-8">
      <h1 className="text-2xl font-bold mb-4">Dashboard</h1>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div className="bg-white rounded p-6 shadow">Conversations: 0</div>
        <div className="bg-white rounded p-6 shadow">Leads: 0</div>
      </div>
    </main>
  );
}
