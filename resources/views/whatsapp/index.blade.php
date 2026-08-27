<x-layouts.app :headerTitle="'WhatsApp Accounts & Meta Cloud API'" :headerSubtitle="'Manage connected WhatsApp numbers, automation mode and Meta integration settings'">
    <div class="p-6 space-y-6">
        <!-- Prominent Demo Mode Notice -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 shadow-xs flex items-start gap-3.5 text-xs text-emerald-900">
            <div class="w-8 h-8 rounded-lg bg-[#25D366] text-white flex items-center justify-center font-bold text-sm shrink-0">
                WA
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-emerald-950">Active Driver: {{ $driverStatus['mode'] }}</h4>
                <p class="text-emerald-800 leading-relaxed">{{ $driverStatus['notice'] }}</p>
                <div class="pt-1 flex items-center gap-3">
                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Webhook Endpoint: <code class="font-mono bg-emerald-100/80 px-1.5 py-0.5 rounded text-[11px]">{{ url('/api/whatsapp/webhook') }}</code>
                    </span>
                </div>
            </div>
        </div>

        <!-- Metrics Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card
                title="Total Conversations"
                :value="$totalConversations"
                subtitle="All time chat threads"
                color="blue"
            />
            <x-stat-card
                title="Messages Today"
                :value="$messagesToday"
                subtitle="Inbound & bot replies"
                color="emerald"
            />
            <x-stat-card
                title="Orders Via WhatsApp"
                :value="$ordersToday"
                subtitle="Converted today"
                color="purple"
            />
        </div>

        <!-- Connected Accounts Grid -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Connected Phone Lines</h3>
                <span class="text-xs text-slate-500">Supports Multi-number Routing (WA 1 - WA 4)</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($accounts as $acc)
                    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#25D366]/10 text-[#25D366] font-extrabold flex items-center justify-center text-sm border border-emerald-200">
                                    {{ $acc->name }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">{{ $acc->name }} ({{ $acc->phone_number }})</h4>
                                    <p class="text-xs text-slate-400 capitalize">Provider: {{ $acc->provider }} • Mode: {{ $acc->mode }}</p>
                                </div>
                            </div>
                            <x-status-badge :status="$acc->status" size="xs" />
                        </div>

                        <form action="{{ route('whatsapp.update', $acc) }}" method="POST" class="space-y-3 pt-3 border-t border-slate-100 text-xs">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="name" value="{{ $acc->name }}">
                            <input type="hidden" name="phone_number" value="{{ $acc->phone_number }}">

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-600 mb-1">Driver Mode</label>
                                    <select name="mode" class="w-full text-xs rounded-lg border-slate-300 py-1.5">
                                        <option value="simulated" {{ $acc->mode === 'simulated' ? 'selected' : '' }}>Simulated (Demo Bot)</option>
                                        <option value="live" {{ $acc->mode === 'live' ? 'selected' : '' }}>Live Meta Cloud API</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-600 mb-1">Line Status</label>
                                    <select name="status" class="w-full text-xs rounded-lg border-slate-300 py-1.5">
                                        <option value="connected" {{ $acc->status === 'connected' ? 'selected' : '' }}>Connected</option>
                                        <option value="disconnected" {{ $acc->status === 'disconnected' ? 'selected' : '' }}>Disconnected</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Meta Phone Number ID</label>
                                <input type="text" name="phone_number_id" value="{{ $acc->phone_number_id }}" placeholder="e.g. 104928192840192" class="w-full text-xs rounded-lg border-slate-300 py-1.5 font-mono">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Meta Permanent Access Token</label>
                                <input type="password" name="api_key" value="{{ $acc->api_key }}" placeholder="EAAG..." class="w-full text-xs rounded-lg border-slate-300 py-1.5 font-mono">
                            </div>

                            <input type="hidden" name="provider" value="{{ $acc->provider }}">

                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                                    Save Line Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
