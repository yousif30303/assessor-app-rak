<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentReport extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function assessor()
    {
        return $this->belongsTo(Assessor::class)->withTrashed();
    }

    public function activityLog()
    {
        return $this->belongsTo(ActivityLog::class);
    }
}
