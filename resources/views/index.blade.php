<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Display Antrian</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 48px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .header p {
            font-size: 18px;
            opacity: 0.9;
        }

        .main-display {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .current-label {
            color: #666;
            font-size: 24px;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .current-number {
            font-size: 120px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .add-queue-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 16px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .add-queue-btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .waiting-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .waiting-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e1e8ed;
        }

        .waiting-header h2 {
            color: #333;
            font-size: 28px;
        }

        .waiting-count {
            background: #667eea;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
        }

        .queue-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .queue-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .queue-item:hover {
            background: #e9ecef;
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .queue-item-number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .queue-item-name {
            color: #666;
            font-size: 14px;
        }

        .no-waiting {
            text-align: center;
            padding: 60px 20px;
            color: #999;
            font-size: 18px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            margin-bottom: 24px;
        }

        .modal-header h3 {
            font-size: 24px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #666;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .error-message {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 6px;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-cancel {
            background: #e1e8ed;
            color: #666;
        }

        .btn-cancel:hover {
            background: #d1d8dd;
        }

        .btn-submit {
            background: #667eea;
            color: white;
        }

        .btn-submit:hover {
            background: #5568d3;
        }

        .success-modal {
            text-align: center;
        }

        .success-icon {
            font-size: 64px;
            color: #27ae60;
            margin-bottom: 20px;
        }

        .success-number {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistem Antrian Online</h1>
            <p>Silakan ambil nomor antrian Anda</p>
        </div>

        <div class="main-display">
            <div class="current-label">Nomor Antrian Saat Ini</div>
            <div class="current-number" id="currentQueue">
                {{ $currentQueue ? $currentQueue->queue_number : '-' }}
            </div>
            <button class="add-queue-btn" onclick="openModal()">
                Ambil Nomor Antrian
            </button>
        </div>

        <div class="waiting-section">
            <div class="waiting-header">
                <h2>Antrian Menunggu</h2>
                <div class="waiting-count">
                    <span id="waitingCount">{{ $waitingQueues->count() }}</span> Menunggu
                </div>
            </div>
            <div class="queue-grid" id="queueGrid">
                @forelse($waitingQueues as $queue)
                <div class="queue-item">
                    <div class="queue-item-number">{{ $queue->queue_number }}</div>
                    <div class="queue-item-name">{{ $queue->customer_name ?? 'Guest' }}</div>
                </div>
                @empty
                <div class="no-waiting">Tidak ada antrian yang menunggu</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal Tambah Antrian -->
    <div class="modal" id="addQueueModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ambil Nomor Antrian</h3>
            </div>
            <form id="queueForm">
                <div class="form-group">
                    <label for="customerName">Nama (Opsional)</label>
                    <input 
                        type="text" 
                        id="customerName" 
                        name="customer_name"
                        placeholder="Masukkan nama Anda"
                        maxlength="100"
                    >
                    <span class="error-message" id="nameError"></span>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-submit">Ambil Antrian</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Success -->
    <div class="modal" id="successModal">
        <div class="modal-content success-modal">
            <div class="success-icon">✓</div>
            <h3>Antrian Berhasil Dibuat!</h3>
            <p>Nomor antrian Anda:</p>
            <div class="success-number" id="successNumber"></div>
            <button class="btn btn-submit" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function openModal() {
            document.getElementById('addQueueModal').classList.add('active');
            document.getElementById('customerName').value = '';
            document.getElementById('nameError').textContent = '';
        }

        function closeModal() {
            document.getElementById('addQueueModal').classList.remove('active');
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.remove('active');
        }

        document.getElementById('queueForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const nameInput = document.getElementById('customerName');
            const nameError = document.getElementById('nameError');
            const name = nameInput.value.trim();
            
            // Validasi client-side
            nameError.textContent = '';
            
            if (name.length > 100) {
                nameError.textContent = 'Nama maksimal 100 karakter';
                return;
            }

            try {
                const response = await fetch('{{ route("queue.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        customer_name: name || null
                    })
                });

                const data = await response.json();

                if (data.success) {
                    closeModal();
                    document.getElementById('successNumber').textContent = data.queue.queue_number;
                    document.getElementById('successModal').classList.add('active');
                    updateDisplay();
                }
            } catch (error) {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });

        async function updateDisplay() {
            try {
                const response = await fetch('{{ route("queue.data") }}');
                const data = await response.json();

                // Update current queue
                document.getElementById('currentQueue').textContent = 
                    data.current_queue ? data.current_queue.queue_number : '-';

                // Update waiting count
                document.getElementById('waitingCount').textContent = data.waiting_count;

                // Update queue grid
                const grid = document.getElementById('queueGrid');
                grid.innerHTML = '';

                if (data.waiting_queues.length === 0) {
                    grid.innerHTML = '<div class="no-waiting">Tidak ada antrian yang menunggu</div>';
                } else {
                    data.waiting_queues.forEach(queue => {
                        grid.innerHTML += `
                            <div class="queue-item">
                                <div class="queue-item-number">${queue.queue_number}</div>
                                <div class="queue-item-name">${queue.customer_name || 'Guest'}</div>
                            </div>
                        `;
                    });
                }
            } catch (error) {
                console.error('Error updating display:', error);
            }
        }

        // Auto refresh every 3 seconds
        setInterval(updateDisplay, 3000);

        // Close modal when clicking outside
        document.getElementById('addQueueModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.getElementById('successModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSuccessModal();
            }
        });
    </script>
</body>
</html>