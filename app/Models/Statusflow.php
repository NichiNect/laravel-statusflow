<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Statusflow extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code', 'name', 'description'
    ];

    /**
     * * Relation to `StatusflowDetail` model
     */
    public function statusflow_details()
    {
        return $this->hasMany(StatusflowDetail::class, 'statusflow_id', 'id');
    }

    /**
     * * Method to get next status
     */
    public function getNextStatus($statusflowCode, $status = null)
    {
        $fieldSelect = [
            'statusflow_details.id', 'statusflow_details.current_status', 'statusflow_details.next_status', 'statusflow_details.level'
        ];

        $result = DB::table('statusflows')
            ->select($fieldSelect)
            ->join('statusflow_details', 'statusflows.id', 'statusflow_details.statusflow_id')
            ->where('statusflows.code', $statusflowCode);

        if ($status) {

            $result = $result->where(function ($q) use ($status) {
                return $q->where('statusflow_details.next_status', $status)
                    ->orWhere('statusflow_details.current_status', $status);
            });
        }

        return $result->get();
    }

    /**
     * * Method to check is allowed change status
     */
    public function isAllowedChangeStatus($statusflowCode, $currentStatus = null, $nextStatus = null)
    {
        $fieldSelect = [
            'statusflow_details.id', 'statusflow_details.current_status', 'statusflow_details.next_status', 'statusflow_details.level'
        ];

        $result = DB::table('statusflows')
            ->select($fieldSelect)
            ->join('statusflow_details', 'statusflows.id', 'statusflow_details.statusflow_id')
            ->where('statusflows.code', $statusflowCode);
        
        if ($currentStatus) {

            $result = $result->where(function ($q) use ($currentStatus) {
                return $q->where('statusflow_details.current_status', $currentStatus);
            });
        }

        if ($nextStatus) {

            $result = $result->where('statusflow_details.next_status', $nextStatus);
        }

        $result = $result->get();

        if ($result && count($result) > 0) {
            
            return [
                'valid' => true,
                'data' => $result
            ];
        }

        return [
            'valid' => false,
            'data' => []
        ];
    }
}
