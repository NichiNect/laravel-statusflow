<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusflowDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'statusflow_id', 'current_status', 'next_status', 'level', 'description'
    ];

    /**
     * * Relation to `Statusflow` model
     */
    public function statusflow()
    {
        return $this->belongsTo(Statusflow::class, 'statusflow_id', 'id');
    }
}
