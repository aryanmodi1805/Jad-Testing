<?php

namespace App\Jobs;

use App\Models\AdminNotification;
use App\Models\Customer;
use App\Models\Seller;
use App\Notifications\AdminSentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendAdminNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public AdminNotification $adminNotification
    ) {
        // Job will be handled by queue
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get recipients based on type
        $recipients = $this->getRecipients();
        
        // Send notifications
        $sentCount = 0;
        foreach ($recipients as $recipient) {
            try {
                $notification = new AdminSentNotification($this->adminNotification);
                
                // Set locale based on recipient preference
                if (isset($recipient->locale)) {
                    $notification->locale($recipient->locale);
                }
                
                $recipient->notify($notification);
                $sentCount++;
            } catch (\Exception $e) {
                // Log error but continue sending to other recipients
                \Log::error('Failed to send notification to user ' . $recipient->id . ': ' . $e->getMessage());
            }
        }

        // Update metadata with sent count
        $this->adminNotification->update([
            'metadata' => [
                'sent_count' => $sentCount, 
                'total_recipients' => count($recipients),
                'processed_at' => now()
            ]
        ]);
    }

    /**
     * Get recipients using lazy collection to avoid memory issues with large datasets.
     * For 'all', 'customers', 'sellers' - uses cursor() for memory-efficient iteration.
     */
    public function getRecipients(): Collection|\Illuminate\Support\LazyCollection
    {
        return match ($this->adminNotification->recipient_type) {
            'all' => Customer::cursor()->merge(Seller::cursor()),
            'customers' => Customer::cursor(),
            'sellers' => Seller::cursor(),
            'specific_customers' => Customer::whereIn('id', $this->adminNotification->recipient_ids ?? [])->get(),
            'specific_sellers' => Seller::whereIn('id', $this->adminNotification->recipient_ids ?? [])->get(),
            default => collect([])
        };
    }
}
