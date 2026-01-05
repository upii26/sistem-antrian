<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_number',
        'customer_name',
        'status',
        'served_at',
        'completed_at'
    ];

    protected $casts = [
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Generate nomor antrian baru
     */
    public static function generateQueueNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = 'A';
        
        $lastQueue = self::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastQueue) {
            $lastNumber = intval(substr($lastQueue->queue_number, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope untuk antrian yang sedang menunggu
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope untuk antrian yang sedang dilayani
     */
    public function scopeServing($query)
    {
        return $query->where('status', 'serving');
    }
}
