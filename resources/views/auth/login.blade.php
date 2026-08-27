<x-layouts.guest>
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-[#09131f] text-slate-200">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <!-- Brand Logo -->
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-emerald-600 to-emerald-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20 ring-1 ring-white/10 mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white tracking-tight">FreshDeal</h2>
            <p class="text-xs text-slate-400 mt-1">Wholesale Vegetables Management System</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
            <div class="bg-[#112233] py-8 px-6 shadow-2xl rounded-2xl border border-slate-800 sm:px-10">
                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 mb-1">Email Address</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', 'admin@freshdeal.com') }}"
                            required
                            autocomplete="email"
                            class="w-full text-sm bg-[#0a1522] border border-slate-700 text-white rounded-lg px-3 py-2.5 focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none placeholder-slate-500"
                            placeholder="admin@freshdeal.com"
                        >
                        @error('email')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-xs font-semibold text-slate-300">Password</label>
                        </div>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            value="password"
                            required
                            autocomplete="current-password"
                            class="w-full text-sm bg-[#0a1522] border border-slate-700 text-white rounded-lg px-3 py-2.5 focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none placeholder-slate-500"
                            placeholder="••••••••"
                        >
                        @error('password')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" checked class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-700 rounded bg-[#0a1522]">
                            <label for="remember" class="ml-2 block text-xs text-slate-400">Remember session</label>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors"
                        >
                            Sign In to Dashboard
                        </button>
                    </div>
                </form>

                <!-- Demo Credentials Box -->
                <div class="mt-6 pt-5 border-t border-slate-800/80 text-xs">
                    <p class="text-slate-400 font-medium mb-2 text-center text-[11px] uppercase tracking-wider">Demo Access Accounts</p>
                    <div class="space-y-1 text-[11px] text-slate-400 bg-[#070e17] p-3 rounded-lg border border-slate-800">
                        <div class="flex justify-between">
                            <span class="text-slate-300 font-semibold">Admin:</span>
                            <span class="font-mono text-emerald-400">admin@freshdeal.com / password</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-300 font-semibold">Order Desk:</span>
                            <span class="font-mono text-slate-400">orders@freshdeal.com / password</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-300 font-semibold">Accounts:</span>
                            <span class="font-mono text-slate-400">accounts@freshdeal.com / password</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.guest>
