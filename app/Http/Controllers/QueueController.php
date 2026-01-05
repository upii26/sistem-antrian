<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Queue;

class QueueController extends Controller
{
     /**
     * Halaman display antrian untuk pengguna
     */
    public function index()
    {
        $currentQueue = Queue::serving()->first();
        $waitingQueues = Queue::waiting()
            ->orderBy('created_at')
            ->get();

        return view('index', compact('currentQueue', 'waitingQueues'));
    }

    /**
     * Tambah antrian baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:100',
        ], [
            'customer_name.max' => 'Nama maksimal 100 karakter',
        ]);

        $queueNumber = Queue::generateQueueNumber();

        $queue = Queue::create([
            'queue_number' => $queueNumber,
            'customer_name' => $validated['customer_name'] ?? null,
            'status' => 'waiting'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Antrian berhasil ditambahkan',
            'queue' => $queue
        ]);
    }

    /**
     * Get data realtime untuk display
     */
    public function getData()
    {
        $currentQueue = Queue::serving()->first();
        $waitingQueues = Queue::waiting()
            ->orderBy('created_at')
            ->take(10) // Ambil 10 antrian berikutnya
            ->get();

        return response()->json([
            'current_queue' => $currentQueue,
            'waiting_queues' => $waitingQueues,
            'waiting_count' => Queue::waiting()->count()
        ]);
    }
}
