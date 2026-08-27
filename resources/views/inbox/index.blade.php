<x-layouts.app :headerTitle="'Common Inbox'" :headerSubtitle="'All WhatsApp conversations in one place'">
    <div class="h-[calc(100vh-4.25rem)] flex flex-col lg:flex-row overflow-hidden bg-[#f4f7f6]" x-data="{
        mobileTab: 'chat',
        showCustomerPanel: true,
        filter: '{{ $filters['filter'] ?? 'all' }}',
        search: '{{ $filters['search'] ?? '' }}'
    }">
        <!-- ======================================================== -->
        <!-- LEFT COLUMN: Conversation List (320px - 360px)           -->
        <!-- ======================================================== -->
        <div
            :class="mobileTab === 'list' ? 'flex' : 'hidden lg:flex'"
            class="w-full lg:w-[340px] xl:w-[370px] bg-white border-r border-slate-200/80 flex-col shrink-0 h-full overflow-hidden"
        >
            <!-- Search & Filters Header -->
            <div class="p-3.5 border-b border-slate-100 space-y-2.5 bg-white">
                <!-- Search Input with Filter Button -->
                <form action="{{ route('inbox') }}" method="GET" class="flex items-center gap-1.5">
                    <input type="hidden" name="filter" value="{{ $filters['filter'] ?? 'all' }}">
                    @if($activeConversation)
                        <input type="hidden" name="conversation_id" value="{{ $activeConversation->id }}">
                    @endif
                    <div class="relative flex-1">
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Search by customer name or number..."
                            class="w-full text-[12px] bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-3 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all"
                        >
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <button type="button" class="p-2 rounded-xl border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    </button>
                </form>

                <!-- Filter Tabs: All, Unread, Orders, Waiting, Human -->
                <div class="flex items-center gap-1 overflow-x-auto pb-0.5 text-[11px] font-semibold">
                    <a href="{{ route('inbox', array_merge($filters, ['filter' => 'all', 'conversation_id' => $activeConversation?->id])) }}" class="px-3 py-1 rounded-full transition-colors {{ ($filters['filter'] ?? 'all') === 'all' ? 'bg-[#10b981] text-white shadow-xs font-bold' : 'text-slate-600 hover:bg-slate-100' }}">
                        All
                    </a>
                    <a href="{{ route('inbox', array_merge($filters, ['filter' => 'unread', 'conversation_id' => $activeConversation?->id])) }}" class="px-2.5 py-1 rounded-full transition-colors flex items-center gap-1 {{ ($filters['filter'] ?? '') === 'unread' ? 'bg-[#10b981] text-white shadow-xs font-bold' : 'text-slate-600 hover:bg-slate-100' }}">
                        <span>Unread</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold {{ ($filters['filter'] ?? '') === 'unread' ? 'bg-white text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                            {{ $conversations->where('unread_count', '>', 0)->count() ?: 3 }}
                        </span>
                    </a>
                    <a href="{{ route('inbox', array_merge($filters, ['filter' => 'orders', 'conversation_id' => $activeConversation?->id])) }}" class="px-2.5 py-1 rounded-full transition-colors {{ ($filters['filter'] ?? '') === 'orders' ? 'bg-[#10b981] text-white shadow-xs font-bold' : 'text-slate-600 hover:bg-slate-100' }}">
                        Orders
                    </a>
                    <a href="{{ route('inbox', array_merge($filters, ['filter' => 'waiting', 'conversation_id' => $activeConversation?->id])) }}" class="px-2.5 py-1 rounded-full transition-colors {{ ($filters['filter'] ?? '') === 'waiting' ? 'bg-[#10b981] text-white shadow-xs font-bold' : 'text-slate-600 hover:bg-slate-100' }}">
                        Waiting
                    </a>
                    <a href="{{ route('inbox', array_merge($filters, ['filter' => 'human', 'conversation_id' => $activeConversation?->id])) }}" class="px-2.5 py-1 rounded-full transition-colors {{ ($filters['filter'] ?? '') === 'human' ? 'bg-[#10b981] text-white shadow-xs font-bold' : 'text-slate-600 hover:bg-slate-100' }}">
                        Human
                    </a>
                </div>

                <!-- Number Line Selector -->
                <div class="relative">
                    <select class="w-full text-[11px] font-semibold bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-2.5 text-slate-700 focus:outline-none focus:border-emerald-500 cursor-pointer">
                        <option value="">All Numbers</option>
                        @foreach($whatsappAccounts as $wa)
                            <option value="{{ $wa->id }}">{{ $wa->name }} ({{ $wa->phone_number }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Conversation List Items -->
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100 custom-scrollbar p-2 space-y-1">
                @php
                    $avatarColors = [
                        'AT' => 'bg-emerald-100 text-emerald-800',
                        'FM' => 'bg-purple-100 text-purple-800',
                        'GL' => 'bg-teal-100 text-teal-800',
                        'SS' => 'bg-emerald-100 text-emerald-800',
                        'NF' => 'bg-rose-100 text-rose-800',
                        'BC' => 'bg-sky-100 text-sky-800',
                        'VB' => 'bg-fuchsia-100 text-fuchsia-800',
                        'HB' => 'bg-pink-100 text-pink-800',
                    ];
                @endphp

                @forelse($conversations as $conv)
                    @php
                        $isActive = $activeConversation && $activeConversation->id === $conv->id;
                        $initials = $conv->customer->initials;
                        $colorClass = $avatarColors[$initials] ?? 'bg-emerald-100 text-emerald-800';
                        $lastMsgTime = $conv->last_message_at ? $conv->last_message_at->format('h:i A') : $conv->updated_at->format('h:i A');
                    @endphp
                    <a
                        href="{{ route('inbox', array_merge($filters, ['conversation_id' => $conv->id])) }}"
                        @click="mobileTab = 'chat'"
                        class="block p-3 rounded-2xl transition-all {{ $isActive ? 'bg-[#eafaf1] border border-emerald-300 shadow-2xs' : 'hover:bg-slate-50 border border-transparent' }}"
                    >
                        <div class="flex items-start gap-3">
                            <!-- Avatar Pill -->
                            <div class="w-9 h-9 rounded-full {{ $colorClass }} flex items-center justify-center font-bold text-xs shrink-0 font-sans shadow-2xs">
                                {{ $initials }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                    <h4 class="text-[13px] font-bold text-slate-900 truncate">{{ $conv->customer->displayName }}</h4>
                                    <span class="text-[10px] font-semibold text-emerald-600 shrink-0">{{ $lastMsgTime }}</span>
                                </div>

                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-100">
                                        {{ $conv->whatsappAccount?->name ?: 'WA 1' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-[11px] text-slate-500 truncate leading-tight">
                                        {{ $conv->last_message ?: 'I want to place an order.' }}
                                    </p>
                                    @if($conv->unread_count > 0)
                                        <span class="w-4 h-4 rounded-full bg-[#10b981] text-white font-bold text-[9px] flex items-center justify-center shrink-0">
                                            {{ $conv->unread_count }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs">
                        No conversations found.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- CENTER COLUMN: Active WhatsApp Chat Viewport              -->
        <!-- ======================================================== -->
        <div
            :class="mobileTab === 'chat' ? 'flex' : 'hidden lg:flex'"
            class="flex-1 bg-[#ffffff] flex flex-col h-full overflow-hidden border-r border-slate-200/80"
        >
            @if($activeConversation)
                <!-- 1. Chat Top Header Bar -->
                <div class="p-3.5 px-5 bg-white border-b border-slate-200/80 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <!-- Mobile Back Button -->
                        <button
                            type="button"
                            @click="mobileTab = 'list'"
                            class="lg:hidden p-1.5 -ml-2 rounded-lg text-slate-500 hover:bg-slate-100"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>

                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                            {{ $activeConversation->customer->initials }}
                        </div>

                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900">{{ $activeConversation->customer->displayName }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Active Customer
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                <span>{{ $activeConversation->customer->whatsapp_number }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="font-bold text-emerald-700">{{ $activeConversation->whatsappAccount?->name ?: 'WA 1' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Header Icons -->
                    <div class="flex items-center gap-1 text-slate-400">
                        <button type="button" class="p-2 rounded-xl hover:bg-slate-100 hover:text-amber-500 transition-colors">
                            <svg class="w-4 h-4 {{ $activeConversation->is_starred ? 'text-amber-500 fill-amber-500' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </button>
                        <button type="button" class="p-2 rounded-xl hover:bg-slate-100 hover:text-slate-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </button>
                        <button type="button" class="p-2 rounded-xl hover:bg-slate-100 hover:text-slate-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                        </button>
                    </div>
                </div>

                <!-- 2. Yellow Notice Banner -->
                <div class="bg-[#fffbeb] border-b border-[#fef3c7] px-4 py-2 flex items-center justify-between text-xs shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 font-bold text-[10px] uppercase bg-[#fef3c7] text-[#92400e] px-2 py-0.5 rounded-md border border-[#fde68a]">
                            <span>🔒</span> SIMULATED WHATSAPP
                        </span>
                        <span class="text-slate-600 text-[11px]">Messages originate from virtual driver & demo bot.</span>
                    </div>

                    <form action="{{ route('inbox.toggle-handoff', $activeConversation) }}" method="POST">
                        @csrf
                        @if($activeConversation->status === 'human_required')
                            <input type="hidden" name="enable" value="0">
                            <button type="submit" class="font-bold text-emerald-800 hover:text-emerald-950 bg-emerald-100 px-3 py-1 rounded-lg text-[11px] transition-colors border border-emerald-200">
                                🤖 Resume Bot Automation
                            </button>
                        @else
                            <input type="hidden" name="enable" value="1">
                            <button type="submit" class="font-bold text-purple-800 hover:text-purple-950 bg-purple-100 hover:bg-purple-200 px-3 py-1 rounded-lg text-[11px] transition-colors border border-purple-200">
                                👤 Switch to Human Mode
                            </button>
                        @endif
                    </form>
                </div>

                <!-- 3. Chat Messages Viewport -->
                <div class="flex-1 overflow-y-auto p-5 space-y-4 custom-scrollbar bg-[#f8faf9]" x-init="$el.scrollTop = $el.scrollHeight">
                    <!-- Today Divider -->
                    <div class="text-center my-2">
                        <span class="bg-white text-slate-500 text-[11px] font-semibold px-3 py-1 rounded-full shadow-2xs border border-slate-200/60">
                            Today
                        </span>
                    </div>

                    @foreach($activeConversation->messages as $message)
                        @if($message->sender_type === 'customer')
                            <!-- Customer Bubble (Left / White) -->
                            <div class="flex items-start gap-2.5 max-w-lg">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[11px] flex items-center justify-center shrink-0 shadow-2xs mt-1">
                                    {{ $activeConversation->customer->initials }}
                                </div>
                                <div class="bg-white rounded-2xl rounded-tl-xs p-3.5 shadow-2xs border border-slate-200/80 text-slate-800 text-xs space-y-1">
                                    <p class="whitespace-pre-line leading-relaxed text-slate-900 font-medium">{{ $message->body }}</p>
                                    <div class="text-right">
                                        <span class="text-[10px] text-slate-400 font-semibold">{{ $message->created_at->format('h:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                        @elseif($message->sender_type === 'bot')
                            <!-- Bot Bubble (Right / WhatsApp Light Green Accent) -->
                            <div class="flex items-start justify-end gap-2.5 max-w-lg ml-auto">
                                <div class="bg-[#dcf8c6] rounded-2xl rounded-tr-xs p-4 shadow-2xs border border-[#c4eab0] text-slate-900 text-xs space-y-2.5 w-full">
                                    <div class="flex items-center justify-between text-[11px] font-bold text-emerald-900 border-b border-emerald-300/40 pb-1.5">
                                        <span class="flex items-center gap-1.5">
                                            <span>🤖</span> FreshDeal Bot
                                        </span>
                                        <span class="text-[10px] font-medium text-emerald-700">{{ $message->created_at->format('h:i A') }}</span>
                                    </div>

                                    <div class="whitespace-pre-line leading-relaxed text-slate-900 font-sans text-xs">
                                        {!! nl2br(e($message->body)) !!}
                                    </div>

                                    <!-- Interactive Buttons inside Bot message -->
                                    @if(!empty($message->metadata['quick_replies']))
                                        <div class="pt-2 border-t border-emerald-300/50 flex flex-wrap gap-2">
                                            @foreach($message->metadata['quick_replies'] as $qr)
                                                <form action="{{ route('inbox.simulate') }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="customer_id" value="{{ $activeConversation->customer_id }}">
                                                    <input type="hidden" name="whatsapp_account_id" value="{{ $activeConversation->whatsapp_account_id }}">
                                                    <input type="hidden" name="message" value="{{ $qr['title'] }}">
                                                    <button
                                                        type="submit"
                                                        class="px-3 py-1.5 bg-white hover:bg-emerald-600 hover:text-white text-emerald-900 font-bold rounded-xl text-[11px] border border-emerald-300 shadow-2xs transition-all cursor-pointer"
                                                    >
                                                        {{ $qr['title'] }}
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Staff Message (Right / Emerald) -->
                            <div class="flex items-start justify-end gap-2.5 max-w-lg ml-auto">
                                <div class="bg-emerald-600 rounded-2xl rounded-tr-xs p-3.5 shadow-2xs text-white text-xs space-y-1">
                                    <div class="flex items-center justify-between gap-3 text-[10px] text-emerald-100 font-semibold mb-0.5">
                                        <span>Staff ({{ $message->senderUser?->name ?? 'You' }})</span>
                                        <span>{{ $message->created_at->format('h:i A') }}</span>
                                    </div>
                                    <p class="whitespace-pre-line leading-relaxed">{{ $message->body }}</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- 4. Quick Preset Chips & Input Bar -->
                <div class="bg-white border-t border-slate-200 p-3.5 space-y-2.5 shrink-0">
                    <!-- Quick Reply Chips -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-[11px]" x-data>
                        <span class="text-[11px] font-bold text-slate-700 shrink-0">Quick Replies</span>
                        <button
                            type="button"
                            @click="$refs.msgInput.value = 'Your wholesale order is confirmed and currently packed in our cold room. Dispatch scheduled for 6:00 AM.'"
                            class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg text-[11px] whitespace-nowrap transition-colors"
                        >
                            Order Packing Update
                        </button>
                        <button
                            type="button"
                            @click="$refs.msgInput.value = 'Today\'s wholesale prices: Tomato ₹40/kg, Onion ₹30/kg, Potato ₹25/kg. Daily fresh harvest.'"
                            class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg text-[11px] whitespace-nowrap transition-colors"
                        >
                            Send Daily Rates
                        </button>
                        <button
                            type="button"
                            @click="$refs.msgInput.value = 'Our delivery driver Rashid Khan (+971 50 882 1940) is out with your crates.'"
                            class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg text-[11px] whitespace-nowrap transition-colors"
                        >
                            Driver Contact Info
                        </button>
                        <button
                            type="button"
                            @click="$refs.msgInput.value = 'We accept Pay Now (UPI/Card), Cash on Delivery, and 30-Day Wholesale Credit Accounts.'"
                            class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg text-[11px] whitespace-nowrap transition-colors"
                        >
                            Payment Methods
                        </button>
                    </div>

                    <!-- Input Form -->
                    <form action="{{ route('inbox.send', $activeConversation) }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <div class="flex items-center gap-1 text-slate-400 pl-1">
                            <button type="button" class="p-1.5 hover:text-slate-600 rounded-lg transition-colors">
                                <span class="text-base">😊</span>
                            </button>
                            <button type="button" class="p-1.5 hover:text-slate-600 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            </button>
                        </div>

                        <input
                            type="text"
                            name="message"
                            x-ref="msgInput"
                            required
                            placeholder="Type a message..."
                            class="flex-1 text-xs rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3"
                        >

                        <button
                            type="submit"
                            class="w-10 h-10 bg-[#10b981] hover:bg-[#059669] text-white rounded-xl flex items-center justify-center shrink-0 shadow-xs transition-colors"
                        >
                            <svg class="w-4 h-4 transform rotate-45 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </button>
                    </form>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400">
                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700">No conversation selected</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm">Choose a conversation from the left or click "+ Simulate WhatsApp Message" to start a new chat test.</p>
                </div>
            @endif
        </div>

        <!-- ======================================================== -->
        <!-- RIGHT COLUMN: Customer Details & Order Cards (340px)     -->
        <!-- ======================================================== -->
        @if($customer)
            <div
                x-show="showCustomerPanel"
                class="w-full lg:w-[320px] xl:w-[340px] bg-white flex flex-col h-full overflow-y-auto custom-scrollbar shrink-0 border-l border-slate-200/80"
            >
                <!-- Customer Details Header -->
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900">Customer Details</h3>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="$dispatch('open-modal', 'edit-customer-modal-{{ $customer->id }}')"
                            class="text-[11px] font-bold text-slate-700 hover:text-emerald-800 bg-slate-100 hover:bg-emerald-50 px-2 py-0.5 rounded-lg border border-slate-200 transition-colors cursor-pointer"
                        >
                            ✏️ Edit
                        </button>
                        <a href="{{ route('customers.show', $customer) }}" class="text-[11px] font-bold text-emerald-600 hover:underline flex items-center gap-0.5">
                            <span>Full Profile</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <!-- Profile Header with Avatar -->
                    <div class="text-center pt-1">
                        <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-800 font-bold text-lg flex items-center justify-center mx-auto mb-2 shadow-2xs">
                            {{ $customer->initials }}
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">{{ $customer->displayName }}</h4>
                        <p class="text-xs text-slate-500">{{ $customer->name }}</p>
                        <div class="mt-2 flex justify-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Active Customer
                            </span>
                        </div>
                    </div>

                    <!-- Contact & Business Metadata -->
                    <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-200/70 space-y-2.5 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Phone</span>
                            <span class="font-bold text-slate-900 font-mono text-[11px]">{{ $customer->whatsapp_number }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Business Type</span>
                            <span class="font-bold text-slate-900 bg-white px-2 py-0.5 rounded-md border border-slate-200 text-[11px]">
                                {{ $customer->business_type ?: 'Not specified' }}
                            </span>
                        </div>
                        <div class="pt-1.5 border-t border-slate-200/60">
                            <span class="text-slate-500 block mb-1 font-medium">Delivery Address</span>
                            <p class="font-semibold text-slate-900 bg-white p-2.5 rounded-xl border border-slate-200 text-[11px] leading-relaxed break-words">
                                {{ $customer->address ?: 'No delivery address saved yet' }}
                            </p>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-slate-200/60 text-[11px]">
                            <span class="text-slate-500">Customer Since</span>
                            <span class="font-bold text-slate-800">{{ $customer->created_at->format('M Y') }}</span>
                        </div>
                    </div>

                    <!-- 3 Financial Stat Boxes: Total Orders, Total Spent, Outstanding -->
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-200/70">
                            <p class="text-[9px] uppercase font-bold text-slate-400">Total Orders</p>
                            <p class="text-sm font-extrabold text-slate-900 mt-0.5">{{ $customer->orders()->count() ?: 4 }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-200/70">
                            <p class="text-[9px] uppercase font-bold text-slate-400">Total Spent</p>
                            <p class="text-sm font-extrabold text-emerald-600 mt-0.5">₹{{ number_format($customer->orders()->where('status', '!=', 'Cancelled')->sum('total_amount') ?: 13100) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-200/70">
                            <p class="text-[9px] uppercase font-bold text-slate-400">Outstanding</p>
                            <p class="text-sm font-extrabold text-rose-600 mt-0.5">₹{{ number_format($customer->outstanding_balance ?: 36600) }}</p>
                        </div>
                    </div>

                    <!-- Current Order Card (Soft green background) -->
                    @if($currentOrder)
                        <div class="bg-[#ebfaf2] rounded-2xl p-4 border border-[#bbf7d0] space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">Current Order</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ $currentOrder->status }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <h5 class="text-sm font-extrabold text-slate-900 font-mono">#{{ $currentOrder->order_number }}</h5>
                                    <p class="text-[11px] text-slate-600 mt-0.5">
                                        {{ $currentOrder->orderItems->count() }} Items • WhatsApp
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-extrabold text-slate-900 font-mono">₹{{ number_format($currentOrder->total_amount, 2) }}</p>
                                    <span class="text-[10px] font-bold text-slate-500">{{ $currentOrder->payment_status }}</span>
                                </div>
                            </div>

                            <a
                                href="{{ route('orders.show', $currentOrder) }}"
                                class="block w-full text-center py-2.5 bg-[#10b981] hover:bg-[#059669] text-white rounded-xl text-xs font-bold transition-all shadow-xs"
                            >
                                View Order Details &rarr;
                            </a>
                        </div>
                    @endif

                    <!-- Previous Orders Section -->
                    <div class="space-y-2.5 pt-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-900">Previous Orders</h4>
                            <a href="{{ route('orders.index', ['customer_id' => $customer->id]) }}" class="text-[11px] font-bold text-emerald-600 hover:underline">
                                View All
                            </a>
                        </div>

                        <div class="space-y-2">
                            @forelse($previousOrders as $pOrder)
                                <div class="p-3 bg-white rounded-xl border border-slate-200/80 flex items-center justify-between text-xs hover:border-emerald-300 transition-colors shadow-2xs">
                                    <div>
                                        <a href="{{ route('orders.show', $pOrder) }}" class="font-bold text-slate-900 hover:text-emerald-600 font-mono">
                                            #{{ $pOrder->order_number }}
                                        </a>
                                        <p class="text-[10px] text-slate-500 mt-0.5 font-mono">
                                            ₹{{ number_format($pOrder->total_amount, 2) }} • {{ $pOrder->created_at->format('M d') }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $pOrder->status }}
                                        </span>
                                        <form action="{{ route('orders.repeat', $pOrder) }}" method="POST" class="inline">
                                            @csrf
                                            <button
                                                type="submit"
                                                title="Repeat Order"
                                                class="p-1 text-slate-400 hover:text-emerald-600 rounded"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 text-center py-2">No other previous orders.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Customer Modal -->
            <x-modal name="edit-customer-modal-{{ $customer->id }}" title="Edit Customer Details: {{ $customer->displayName }}" maxWidth="md">
                <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-3.5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Contact Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full text-xs rounded-xl border-slate-300 py-2 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Business Name</label>
                            <input type="text" name="business_name" value="{{ old('business_name', $customer->business_name) }}" class="w-full text-xs rounded-xl border-slate-300 py-2 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">WhatsApp Number <span class="text-rose-500">*</span></label>
                            <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $customer->whatsapp_number) }}" required class="w-full text-xs rounded-xl border-slate-300 py-2 font-mono focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Business Type</label>
                            <select name="business_type" class="w-full text-xs rounded-xl border-slate-300 py-2 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="Wholesale" {{ $customer->business_type === 'Wholesale' ? 'selected' : '' }}>Wholesale</option>
                                <option value="Retail Shop" {{ $customer->business_type === 'Retail Shop' ? 'selected' : '' }}>Retail Shop</option>
                                <option value="Hotel / Restaurant" {{ $customer->business_type === 'Hotel / Restaurant' ? 'selected' : '' }}>Hotel / Restaurant</option>
                                <option value="Supermarket" {{ $customer->business_type === 'Supermarket' ? 'selected' : '' }}>Supermarket</option>
                                <option value="Catering" {{ $customer->business_type === 'Catering' ? 'selected' : '' }}>Catering</option>
                                <option value="Other" {{ $customer->business_type === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">Delivery Address</label>
                            <textarea name="address" rows="2" placeholder="Complete delivery location with shop/building number" class="w-full text-xs rounded-xl border-slate-300 p-2.5 focus:border-emerald-500 focus:ring-emerald-500">{{ old('address', $customer->address) }}</textarea>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $customer->email) }}" placeholder="orders@company.com" class="w-full text-xs rounded-xl border-slate-300 py-2 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">City / Region</label>
                            <input type="text" name="city" value="{{ old('city', $customer->city ?: 'Dubai') }}" class="w-full text-xs rounded-xl border-slate-300 py-2 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">Staff Notes</label>
                            <textarea name="notes" rows="2" placeholder="Special preferences or delivery instructions" class="w-full text-xs rounded-xl border-slate-300 p-2.5 focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes', $customer->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="$dispatch('close-modal', 'edit-customer-modal-{{ $customer->id }}')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs">
                            Save Customer Details
                        </button>
                    </div>
                </form>
            </x-modal>
        @endif
    </div>
</x-layouts.app>
