<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;

class UserNotification extends Model
{

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}
