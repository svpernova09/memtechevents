<?php

namespace MemtechEvents;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Group extends Model
{

    protected $table = 'groups';

    protected $fillable = [
        'name',
        'website',
        'twitter',
    ];


}
