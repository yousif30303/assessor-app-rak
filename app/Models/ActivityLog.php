<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sb_id',
        'student_id',
        'action',
        'request_data',
        'response_data',
        'assessment_detail'
    ];

    protected $casts = [
        'request_data' => 'collection',
        'response_data' => 'collection',
        'assessment_detail' => 'collection',
    ];

}
