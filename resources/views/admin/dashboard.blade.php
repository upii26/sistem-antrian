<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin - Sistem Antrian</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 24px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-name {
            font-size: 14px;
        }

        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid white;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: white;
            color: #667eea;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .current-queue {
            background: white;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .queue-label {
            color: #666;
            font-size: 16px;
            margin-bottom: 12px;
        }

        .queue-number {
            font-size: 72px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 20px;
        }

        .queue-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-prev {
            background: #95a5a6;
            color: white;
        }

        .btn-prev:hover {
            background: #7f8c8d;
        }

        .btn-next {
            background: #667eea;
            color: white;
        }

        .btn-next:hover {
            background: #5568d3;
        }

        .waiting-list {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .waiting-list h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .queue-table {
            width: 100%;
            border-collapse: collapse;
        }

        .queue-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #e1e8ed;
        }

        .queue-table td {
            padding: 12px;
            border-bottom: 1px solid #e1e8ed;
        }

        .queue-table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-waiting {
            background: #fff3cd;
            color: #856404;
        }

        .no-queue {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Admin</h1>
        <div class="header-right">
            <span class="admin-name">{{ Auth::guard('admin')->user()->name }}</span>
            <form method="POST" action="{{ route('admin.logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div id="alertContainer"></div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Antrian Saat Ini</div>
                <div class="stat-value" id="currentQueueDisplay">
                    {{ $currentQueue ? $currentQueue->queue_number : '-' }}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Menunggu</div>
                <div class="stat-value" id="waitingCount">{{ $waitingQueues->count() }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Selesai Hari Ini</div>
                <div class="stat-value" id="completedCount">{{ $completedToday }}</div>
            </div>
        </div>

        <div class="current-queue">
            <div class="queue-label">Antrian Yang Sedang Dilayani</div>
            <div class="queue-number" id="currentQueue">
                {{ $currentQueue ? $currentQueue->queue_number : '-' }}
            </div>
            <div class="queue-controls">
                <button class="btn btn-prev" onclick="previousQueue()">
                    ← Sebelumnya
                </button>
                <button class="btn btn-next" onclick="nextQueue()">
                    Selanjutnya →
                </button>
            </div>
        </div>

        <div class="waiting-list">
            <h2>Daftar Antrian Menunggu</h2>
            <table class="queue-table">
                <thead>
                    <tr>
                        <th>No. Antrian</th>
                        <th>Nama</th>
                        <th>Waktu Daftar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="waitingListBody">
                    @forelse($waitingQueues as $queue)
                    <tr>
                        <td><strong>{{ $queue->queue_number }}</strong></td>
                        <td>{{ $queue->customer_name ?? '-' }}</td>
                        <td>{{ $queue->created_at->format('H:i:s') }}</td>
                        <td><span class="badge badge-waiting">Menunggu</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="no-queue">Tidak ada antrian yang menunggu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function showAlert(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            
            const container = document.getElementById('alertContainer');
            container.innerHTML = '';
            container.appendChild(alertDiv);
            
            setTimeout(() => alertDiv.remove(), 3000);
        }

        async function nextQueue() {
            try {
                const response = await fetch('{{ route("admin.queue.next") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    updateDisplay();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('Terjadi kesalahan', 'error');
            }
        }

        async function previousQueue() {
            try {
                const response = await fetch('{{ route("admin.queue.previous") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    updateDisplay();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('Terjadi kesalahan', 'error');
            }
        }

        async function updateDisplay() {
            try {
                const response = await fetch('{{ route("admin.queue.data") }}');
                const data = await response.json();

                // Update current queue
                document.getElementById('currentQueue').textContent = 
                    data.current_queue ? data.current_queue.queue_number : '-';
                document.getElementById('currentQueueDisplay').textContent = 
                    data.current_queue ? data.current_queue.queue_number : '-';

                // Update counts
                document.getElementById('waitingCount').textContent = data.waiting_count;
                document.getElementById('completedCount').textContent = data.completed_today;

                // Update waiting list
                const tbody = document.getElementById('waitingListBody');
                tbody.innerHTML = '';

                if (data.waiting_queues.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="no-queue">Tidak ada antrian yang menunggu</td></tr>';
                } else {
                    data.waiting_queues.forEach(queue => {
                        const time = new Date(queue.created_at).toLocaleTimeString('id-ID');
                        tbody.innerHTML += `
                            <tr>
                                <td><strong>${queue.queue_number}</strong></td>
                                <td>${queue.customer_name || '-'}</td>
                                <td>${time}</td>
                                <td><span class="badge badge-waiting">Menunggu</span></td>
                            </tr>
                        `;
                    });
                }
            } catch (error) {
                console.error('Error updating display:', error);
            }
        }

        // Auto refresh every 3 seconds
        setInterval(updateDisplay, 3000);
    </script>
</body>
</html>