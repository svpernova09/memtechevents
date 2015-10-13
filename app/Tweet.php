<?php

namespace MemtechEvents;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Tweet extends Model
{

    protected $table = 'tweets';

    protected $fillable = [
        'content',
        'tweet_at',
        'sent',
        'event_id',
        'days_before'
    ];

    public function isSent()
    {
        return $this->sent;
    }
}
