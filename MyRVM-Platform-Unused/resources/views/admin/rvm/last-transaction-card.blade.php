<!-- Last Transaction Card -->
@if($lastTransactions->count() > 0)
<div class="col-md-6 mb-4">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-gradient-success text-white">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                    <span class="avatar-initial rounded bg-label-success">
                        <i class="fas fa-receipt"></i>
                    </span>
                </div>
                <div>
                    <h6 class="mb-0">Last Transaction</h6>
                    <small>Recent {{ $lastTransactions->count() }} transactions</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-3 py-2" style="font-size: 0.75rem;">
                                <i class="fas fa-user me-1 text-muted"></i>User
                            </th>
                            <th class="border-0 py-2" style="font-size: 0.75rem;">
                                <i class="fas fa-calendar me-1 text-muted"></i>Date
                            </th>
                            <th class="border-0 py-2" style="font-size: 0.75rem;">
                                <i class="fas fa-info-circle me-1 text-muted"></i>Status
                            </th>
                            <th class="border-0 pe-3 py-2" style="font-size: 0.75rem;">
                                <i class="fas fa-coins me-1 text-muted"></i>Reward
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lastTransactions as $transaction)
                        <tr class="border-0">
                            <td class="ps-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-2">
                                        @if($transaction->user_avatar)
                                            <img src="{{ $transaction->user_avatar }}" alt="{{ $transaction->user_name }}" class="rounded-circle" style="width: 24px; height: 24px;">
                                        @else
                                            <div class="avatar-title bg-soft-primary text-primary rounded-circle" style="width: 24px; height: 24px; font-size: 0.6rem;">
                                                {{ substr($transaction->user_name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $transaction->user_name }}</div>
                                        <small class="text-muted" style="font-size: 0.65rem;">{{ $transaction->user_email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2">
                                <div style="font-size: 0.75rem;">
                                    <div class="fw-medium">{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d') }}</div>
                                    <small class="text-muted" style="font-size: 0.65rem;">{{ \Carbon\Carbon::parse($transaction->created_at)->format('H:i') }}</small>
                                </div>
                            </td>
                            <td class="py-2">
                                @php
                                    $statusClass = 'success';
                                    $statusText = 'Completed';
                                    $statusIcon = 'check-circle';
                                    
                                    if($transaction->type === 'deposit_reward') {
                                        $statusClass = 'success';
                                        $statusText = 'Rewarded';
                                        $statusIcon = 'gift';
                                    } elseif($transaction->type === 'withdrawal') {
                                        $statusClass = 'warning';
                                        $statusText = 'Withdrawn';
                                        $statusIcon = 'arrow-up';
                                    } elseif($transaction->type === 'penalty') {
                                        $statusClass = 'danger';
                                        $statusText = 'Penalty';
                                        $statusIcon = 'exclamation-triangle';
                                    }
                                @endphp
                                <span class="badge bg-{{ $statusClass }} text-white px-2 py-1" style="font-size: 0.65rem;">
                                    <i class="fas fa-{{ $statusIcon }} me-1 animated-icon"></i>{{ $statusText }}
                                </span>
                            </td>
                            <td class="pe-3 py-2">
                                <div class="d-flex align-items-center">
                                    <span class="fw-bold text-success" style="font-size: 0.75rem;">
                                        <i class="fas fa-plus me-1"></i>{{ number_format($transaction->amount, 2) }}
                                    </span>
                                    <small class="text-muted ms-1" style="font-size: 0.65rem;">pts</small>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Animasi untuk icon status */
.animated-icon {
    animation: pulse 2s infinite;
}

/* Animasi pulse untuk icon reward */
.fa-gift {
    animation: bounce 1.5s infinite;
}

/* Animasi untuk icon penalty */
.fa-exclamation-triangle {
    animation: shake 0.5s infinite;
}

/* Animasi untuk icon withdrawal */
.fa-arrow-up {
    animation: float 2s ease-in-out infinite;
}

/* Keyframes untuk animasi */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-3px); }
    60% { transform: translateY(-2px); }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-2px); }
}

/* Hover effect untuk badge */
.badge:hover .animated-icon {
    animation-duration: 0.5s;
}
</style>
@endif
