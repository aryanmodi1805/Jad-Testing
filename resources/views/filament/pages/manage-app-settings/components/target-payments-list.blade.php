<div class="overflow-x-auto border rounded-xl" x-data="{ expanded: null }" wire:ignore.self>
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-4 py-3"></th> <!-- Toggle Column -->
                <th scope="col" class="px-4 py-3">#</th>
                <th scope="col" class="px-4 py-3">Charge ID</th>
                <th scope="col" class="px-4 py-3">Seller</th>
                <th scope="col" class="px-4 py-3">Amount</th>
                <th scope="col" class="px-4 py-3">Status</th>
                <th scope="col" class="px-4 py-3">Reason</th>
                <th scope="col" class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr wire:key="payment-row-{{ $payment->id }}" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    <td class="px-4 py-3">
                        <button 
                            type="button"
                            @click="expanded === {{ $payment->id }} ? expanded = null : expanded = {{ $payment->id }}"
                            class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            <svg x-show="expanded !== {{ $payment->id }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="9 5l7 7-7 7"></path></svg>
                            <svg x-show="expanded === {{ $payment->id }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="19 9l-7 7-7-7"></path></svg>
                        </button>
                    </td>
                    <td class="px-4 py-3 text-gray-400 font-medium whitespace-nowrap">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $payment->charge_id }}</td>
                    <td class="px-4 py-3">{{ $payment->user?->name ?? 'Unknown' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ number_format((float)$payment->amount, 2) }} {{ $payment->currency }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            @if($payment->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                            @elseif($payment->status === 'failed') bg-red-100 text-red-800 dark:bg-red-900 dark:text-green-300
                            @elseif($payment->status === 'expired') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300
                            @elseif($payment->status === 'verifying') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 @endif">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3" title="{{ $payment->failure_reason }}">
                        {{ $payment->failure_reason ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <button 
                            type="button"
                            @click="expanded === {{ $payment->id }} ? expanded = null : expanded = {{ $payment->id }}"
                            class="text-primary-600 hover:text-primary-900 dark:text-primary-400 font-medium mr-3"
                        >
                            Details
                        </button>
                        <button 
                            wire:click="deletePayment({{ $payment->id }})"
                            wire:confirm="Are you sure you want to delete this payment record?"
                            class="text-red-600 hover:text-red-900 dark:text-red-400 font-medium"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
                <tr wire:key="payment-details-{{ $payment->id }}" x-show="expanded === {{ $payment->id }}" x-cloak class="bg-gray-50 dark:bg-gray-900/50">
                    <td colspan="8" class="px-8 py-4 border-b">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase text-xs tracking-wider">Payment Info</h4>
                                <ul class="space-y-2 text-xs">
                                    <li><span class="text-gray-400 font-medium">Status Reason:</span> <span class="text-red-600 dark:text-red-400 font-bold italic">{{ $payment->failure_reason ?? 'No specific reason provided' }}</span></li>
                                    <li><span class="text-gray-400">Type:</span> {{ str_replace('_', ' ', ucfirst($payment->payment_type)) }}</li>
                                    <li><span class="text-gray-400">Attempts:</span> {{ $payment->verification_attempts }}</li>
                                    <li><span class="text-gray-400">Last Verified:</span> {{ $payment->last_verified_at?->format('Y-m-d H:i:s') ?? 'Never' }}</li>
                                    <li><span class="text-gray-400">Expires:</span> {{ $payment->expires_at->format('Y-m-d H:i:s') }}</li>
                                    @if($payment->response_id)
                                        <li><span class="text-gray-400">Response ID:</span> <span class="font-mono">{{ $payment->response_id }}</span></li>
                                    @endif
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase text-xs tracking-wider">Raw Metadata</h4>
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 font-mono text-[10px] overflow-auto max-h-40 shadow-inner">
                                    @if($payment->metadata)
                                        <pre class="text-gray-600 dark:text-gray-400">{{ json_encode($payment->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @else
                                        <span class="text-gray-400 italic">No metadata available</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($payments->isEmpty())
        <div class="p-8 text-center text-gray-500 italic">
            No payments found in the target window (last 30 days).
        </div>
    @endif
</div>
