<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs | RCA Compliance</title>
    {{-- Load Tailwing CSS via CDN for rapid responsive layout prototyping --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 sm:p-10 font-sans text-gray-800">
<div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-6 sm:p-8">
    {{-- Header Section: displays view title and navigation back to main calculator --}}
    <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
        <div>
            <h1 class="text-2xl font-black text-gray-900">RCA Audit Trail & API Logs</h1>
            <p class="text-sm text-gray-500">Transaction and queries to the broker Life Is Hard (ASF / BAAR)</p>
        </div>
        <a href="/" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow transition">
            &larr; Back to the Calculator
        </a>
    </div>

    {{-- Responsive Table Container: handles horizontal scrolling on smaller screens--}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-black tracking-wider text-[11px] border-b border-gray-200">
            <tr>
                <th class="py-3 px-4">ID</th>
                <th class="py-3 px-4">Date & Hour</th>
                <th class="py-3 px-4">Action</th>
                <th class="py-3 px-4">Status HTTP</th>
                <th class="py-3 px-4">IP User</th>
                <th class="py-3 px-4">Technical Details</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            {{-- Iterate through paginated audit records; displays fallback row if empty --}}
            @forelse($logs as $log)
                <tr class="hover:bg-gray-50/70 transition">
                    {{-- Record identifier rendered in monospaced font --}}
                    <td class="py-3 px-4 font-mono font-bold text-gray-900">#{{ $log->id }}</td>
                    {{-- Carbon Date formatting to standard SQL timestamp format --}}
                    <td class="py-3 px-4 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    {{-- Action pill with dynamic color depending on operation type --}}
                    <td class="py-3 px-4">
                                <span class="inline-block px-2.5 py-1 rounded-full font-bold text-[10px]
                                    {{ $log->action === 'ISSUE_POLICY' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $log->action }}
                                </span>
                    </td>

                    {{-- HTTP status code badge: green for 2xx success codes, red for errors --}}
                    <td class="py-3 px-4">
                                <span class="font-bold {{ $log->http_status >= 200 && $log->http_status < 300 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $log->http_status ?? '-' }}
                                </span>
                    </td>

                    {{-- Client IP address fallback --}}
                    <td class="py-3 px-4 font-mono">{{ $log->ip_address ?? '127.0.0.1' }}</td>

                    {{-- Expandable disclosure widget rendering formatted JSON payloads --}}
                    <td class="py-3 px-4">
                        <details class="cursor-pointer text-blue-600 hover:text-blue-800">
                            <summary class="font-semibold">See JSON Request / Response</summary>
                            <div class="mt-2 p-3 bg-gray-900 text-gray-100 rounded-lg font-mono text-[10px] space-y-2 max-w-lg overflow-x-auto">
                                <div>
                                    <strong class="text-blue-400">Request:</strong>
                                    {{-- Pretty-print JSON object while keeping raw forward slashes --}}
                                    <pre class="mt-1">{{ json_encode($log->provider_request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                                <div>
                                    <strong class="text-emerald-400">Response:</strong>
                                    <pre class="mt-1">{{ json_encode($log->provider_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            </div>
                        </details>
                    </td>
                </tr>
            @empty
                {{-- Fallback empty state when no audit records are found --}}
                <tr>
                    <td colspan="6" class="py-6 text-center text-gray-400">No logs in the audit.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Render pagination navigation controls dinamically --}}
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
</body>
</html>
