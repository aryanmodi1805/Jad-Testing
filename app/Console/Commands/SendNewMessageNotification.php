<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Message;
use App\Notifications\NewMessageNotification;
use Illuminate\Console\Command;

class SendNewMessageNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-new-message-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $unreadMessagesQuery = Message::whereNull('read_at')->where('notified', false)->where('created_at' , '<=' , now()->subMinutes());
        $unreadMessages = $unreadMessagesQuery->with(['sender', 'response.seller' , 'response.request.country', 'response.request.customer' , 'response.request.service'])->get();

        foreach (array_unique($unreadMessages->pluck('response_id')->toArray()) as $responseId) {
            $messages = $unreadMessages->where('response_id', $responseId);
            $response = $messages->first()->response;
            $request = $response->request;
            $customer = $request->customer;
            $seller = $response->seller;
            $sender = $messages->first()->sender;

            if($sender instanceof Customer) {
                $to = $seller;
                $from = $customer;
            }else{
                $to = $customer;
                $from = $seller;
            }

            $to->notify(new NewMessageNotification(request: $request,response: $response ,service: $request->service, messageCount: $messages->count(), from: $from , tenant: $request->country ));

        }

        $unreadMessagesQuery->update([
            'notified' => true
        ]);
    }
}
