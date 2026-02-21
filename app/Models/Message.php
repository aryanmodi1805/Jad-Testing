<?php

namespace App\Models;

use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\HtmlString;

class Message extends Model
{
    use HasUuids;
    protected $fillable = [
        'response_id',
        'sender_id',
        'sender_type',
        'message',
        'attachments',
        'original_attachment_file_names',
        'payload',
        'read_at',
        'notified'
    ];

    protected $casts = [
        'attachments' => 'array',
        'original_attachment_file_names' => 'array',
        'payload' => 'array'
    ];
    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    public function payloadType() : MessageType {
        if($this->payload == null){
            return MessageType::Message;
        }

        return MessageType::from($this->payload["type"]);
    }

    public function estimatePrice() : ?string
    {
        if($this->payloadType() == MessageType::Estimate){
            return __('string.chat.estimate_price',[
                'price' => ($this->payload["data"]["amount"] ?? '') . ' ' .  (getCurrencySample() ?? '') . ' ' .($this->payload["data"]["estimate_base"]["name"][app()->getLocale()] ?? ''),
            ]) ;
        }

        return null;

    }

    public function hasDetails() : bool
    {
        if($this->payloadType() == MessageType::Estimate){
            return !empty($this->payload["data"]["details"]??'');
        }

        return false;

    }

    public function estimateDetails() : ?HtmlString
    {
        if($this->payloadType() == MessageType::Estimate){
            return new HtmlString(nl2br(addBullets($this->payload["data"]["details"] ?? '')));
        }

        return null;

    }





}
