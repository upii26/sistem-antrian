<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Relasi: Admin melayani banyak antrian
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function queues()
    {
        return $this->hasMany(Queue::class, 'served_by');
    }

    /**
     * Get antrian yang dilayani hari ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function todayQueues()
    {
        return $this->queues()
                    ->whereDate('served_at', today())
                    ->orderBy('served_at', 'desc');
    }

    /**
     * Get total antrian yang sudah diselesaikan
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function completedQueues()
    {
        return $this->queues()
                    ->where('status', 'completed');
    }

    /**
     * Get antrian yang sedang dilayani oleh admin ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function currentlyServing()
    {
        return $this->queues()
                    ->where('status', 'serving');
    }

    /**
     * Get statistik performa admin
     * 
     * @return array
     */
    public function getPerformanceStats()
    {
        return [
            'total_served' => $this->queues()->count(),
            'completed' => $this->completedQueues()->count(),
            'today_served' => $this->todayQueues()->count(),
            'currently_serving' => $this->currentlyServing()->count(),
            'avg_service_time' => $this->getAverageServiceTime(),
        ];
    }

    /**
     * Hitung rata-rata waktu pelayanan (dalam menit)
     * 
     * @return float
     */
    public function getAverageServiceTime()
    {
        $queues = $this->completedQueues()
                       ->whereNotNull('served_at')
                       ->whereNotNull('completed_at')
                       ->get();

        if ($queues->isEmpty()) {
            return 0;
        }

        $totalMinutes = $queues->sum(function ($queue) {
            return $queue->served_at->diffInMinutes($queue->completed_at);
        });

        return round($totalMinutes / $queues->count(), 2);
    }

    /**
     * Check apakah admin sedang melayani antrian
     * 
     * @return bool
     */
    public function isServing(): bool
    {
        return $this->currentlyServing()->exists();
    }

    /**
     * Get antrian yang dilayani dalam periode tertentu
     * 
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function queuesInPeriod($startDate, $endDate)
    {
        return $this->queues()
                    ->whereBetween('served_at', [$startDate, $endDate])
                    ->orderBy('served_at', 'desc');
    }
}
