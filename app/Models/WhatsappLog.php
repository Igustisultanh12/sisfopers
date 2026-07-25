<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    protected $fillable = ['recipient_number', 'message', 'status', 'error_response'];
}