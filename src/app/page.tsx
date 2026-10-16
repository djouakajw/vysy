export default function Home() {
  return (
    <main className="min-h-screen bg-white text-gray-900">
      <nav className="flex items-center justify-between px-8 py-4 border-b">
        <span className="font-bold text-xl">Wazzap Clone</span>
        <a href="/dashboard" className="bg-black text-white px-4 py-2 rounded">Start free</a>
      </nav>
      <section className="text-center py-24 px-6">
        <h1 className="text-5xl font-bold mb-4">Automate your WhatsApp with AI</h1>
        <p className="text-lg text-gray-600 mb-8">AI assistants that qualify leads, book appointments, 24/7.</p>
        <a href="/dashboard" className="bg-black text-white px-6 py-3 rounded-lg">Get started</a>
      </section>
      <section className="grid grid-cols-1 md:grid-cols-3 gap-6 px-8 pb-16 max-w-5xl mx-auto">
        {["Starter $29/mo", "Pro $49/mo", "Business $199/mo"].map((p) => (
          <div key={p} className="border rounded-lg p-6 text-center">
            <h3 className="font-semibold mb-2">{p}</h3>
            <p className="text-sm text-gray-500">1 agent, 2000 credits</p>
          </div>
        ))}
      </section>
    </main>
  );
}
