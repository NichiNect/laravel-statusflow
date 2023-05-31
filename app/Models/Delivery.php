<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Delivery extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'delivery_number', 'sender_name', 'sender_phone', 'receiver_name', 'receiver_phone', 'receiver_address',
        'delivery_fee', 'sprinter', 'note', 'status', 'delivered_at', 'received_at'
    ];

    /**
     * Declare the status value to synchronize the database enum corectly
     * 
     * @param $key
     */
    public static function getStatusEnum($key) {

        $lists = [
            'PENDING' => 'pending',
            'PROCESS' => 'process',
            'DELIVERING' => 'delivering',
            'DONE' => 'done',
            'CANCEL' => 'cancel'
        ];

        if (isset($lists[$key])) {

            return $lists[$key];
        }

        return null;
    }

    /**
     * * Relation to `DeliveryItem` model
     */
    public function delivery_items()
    {
        return $this->hasMany(DeliveryItem::class, 'delivery_id', 'id');
    }

    /**
     * * Generate Delivery Number Code
     */
    public function generateDeliveryNumber()
    {
        $zeroPadding = "000000";
        $prefixCode = "DV-";
        $code = $prefixCode . date('dmY');

        $increment = 0;
        $similiarCode = DB::table('deliveries')->select('delivery_number')
            ->whereRaw('DATE(created_at) = DATE(NOW())')
            ->orderBy('delivery_number', 'desc')
            ->first();

        if (!$similiarCode) {
            $increment = 1;
        } else {
            $increment = (int) substr($similiarCode->delivery_number, strlen($code));
            $increment = $increment + 1;
        }

        $code = $code . substr($zeroPadding, strlen("$increment")) . $increment;

        return $code;
    }
}
