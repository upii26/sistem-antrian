<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    /**
     * Dashboard admin - list antrian
     */
    public function index()
    {
        $currentQueue = Queue::serving()->first();
        $waitingQueues = Queue::waiting()
            ->orderBy('created_at')
            ->get();
        $completedToday = Queue::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();

        return view('admin.dashboard', compact('currentQueue', 'waitingQueues', 'completedToday'));
    }

    /**
     * Panggil antrian berikutnya
     */
    public function next(Request $request)
    {
        // Selesaikan antrian yang sedang dilayani
        Queue::serving()->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        // Ambil antrian berikutnya
        $nextQueue = Queue::waiting()
            ->orderBy('created_at')
            ->first();

        if ($nextQueue) {
            $nextQueue->update([
                'status' => 'serving',
                'served_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Antrian berikutnya dipanggil',
                'queue' => $nextQueue
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada antrian yang menunggu'
        ]);
    }

    /**
     * Kembali ke antrian sebelumnya
     */
    public function previous(Request $request)
    {
        $currentQueue = Queue::serving()->first();
        
        if (!$currentQueue) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada antrian yang sedang dilayani'
            ]);
        }

        // Kembalikan status ke waiting
        $currentQueue->update([
            'status' => 'waiting',
            'served_at' => null
        ]);

        // Ambil antrian sebelumnya yang sudah selesai
        $previousQueue = Queue::where('status', 'completed')
            ->where('id', '<', $currentQueue->id)
            ->orderBy('id', 'desc')
            ->first();

        if ($previousQueue) {
            $previousQueue->update([
                'status' => 'serving',
                'served_at' => now(),
                'completed_at' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kembali ke antrian sebelumnya',
                'queue' => $previousQueue
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada antrian sebelumnya'
        ]);
    }

    /**
     * Get data realtime untuk update otomatis
     */
    public function getData()
    {
        $currentQueue = Queue::serving()->first();
        $waitingQueues = Queue::waiting()
            ->orderBy('created_at')
            ->get();
        $completedToday = Queue::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'current_queue' => $currentQueue,
            'waiting_queues' => $waitingQueues,
            'completed_today' => $completedToday,
            'waiting_count' => $waitingQueues->count()
        ]);
    }
}
