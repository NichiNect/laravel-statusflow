<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $paginate = $request->get('paginate', 10);
        $filter = $request->get('filter', "[]");
        $orderField = $request->get('order_field', 'created_at');
        $orderDirection = $request->get('order_direction', 'DESC');

        DB::enableQueryLog();
        $deliveries = Delivery::leftJoin('delivery_items', 'delivery_items.delivery_id', 'deliveries.id');

        // * Implement search
        if ($request->get('search') != '') {

            $searchable = [
                'deliveries.delivery_number', 
                'deliveries.sender_name',
                'deliveries.receiver_name',
            ];

            $deliveries = $deliveries->where(function ($query) use ($request, $searchable) {
                foreach ($searchable as $searchColumn) {
                    $query->orWhere($searchColumn, 'LIKE', '%' . $request->get('search') . '%');
                }
            });
        }

        // * Implement filters
        if($filter) {

            $filters = [];
            try {
                $filters = json_decode($filter);
            } catch (\Throwable $th) {
                return response([
                    'message' => "Error: failed to parsing request filter",
                    'error' => $th
                ], 500);
            }

            foreach ($filters as $key => $filter_item) {
                $deliveries = $deliveries->whereIn($filter_item->column, $filter_item->value);
            }
        }

        // * Commit
        $deliveries = $deliveries->select(['deliveries.*', DB::raw('SUM(delivery_items.quantity) as total_items')])
            ->orderBy($orderField, $orderDirection)
            ->groupBy('deliveries.id')
            ->paginate($paginate);

        // * Return if empty
        if (empty($deliveries->items())) {
            return response([
                'message' => 'Empty data',
                'data' => [],
            ], 200);
        }

        return response([
            'message' => 'Success',
            'query' => DB::getQueryLog(),
            'data' => $deliveries->all(),
            'total_row' => $deliveries->total()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /**
         * * Validation Request
         */
        $validate = Validator::make($request->all(), [
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:18',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:18',
            'receiver_address' => 'required|string|max:255',
            'delivery_fee' => 'required|numeric|min:0',
            'sprinter' => 'required|string|max:255',
            'note' => 'nullable|string|max:255',

            'delivery_items' => 'nullable|array',
            'delivery_items.*.item_name' => 'required|string|max:255',
            'delivery_items.*.quantity' => 'required|numeric|min:0',
            'delivery_items.*.note' => 'nullable|string|max:255',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => "Error: unprocessable entity, validation error!",
                'errors' => $validate->errors(),
            ], 422);
        }

        DB::beginTransaction();

        /**
         * * Delivery Creation
         */
        $delivery = new Delivery();

        // * Process
        foreach ($delivery->getFillable() as $field) {

            if ($request->{$field} && $request->{$field} !== null) {

                $delivery->{$field} = $request->{$field};
            }
        }

        $delivery->delivery_number = $delivery->generateDeliveryNumber();
        $delivery->status = Delivery::getStatusEnum('PENDING');

        try {
            $delivery->save();
        } catch (\Throwable $th) {
            DB::rollback();
            return response([
                'message' => "Error: failed to insert new delivery",
                'error' => $th
            ], 500);
        }

        /**
         * * Delivery Items Creation
         */
        $prepareDeliveryItems = [];
        foreach ($request->delivery_items as $item) {

            array_push($prepareDeliveryItems, [
                'delivery_id' => $delivery->id,
                'item_name' => $item['item_name'],
                'quantity' => $item['quantity'],
                'note' => isset($item['note']) ? $item['note'] : null,
            ]);
        }

        try {
            DeliveryItem::insert($prepareDeliveryItems);
        } catch (\Throwable $th) {
            DB::rollback();
            return response([
                'message' => "Error: failed to batch insert new delivery items",
                'error' => $th
            ], 500);
        }

        DB::commit();

        $delivery->delivery_items = DeliveryItem::where('delivery_id', $delivery->id)->get();

        return response([
            'message' => "Success",
            'data' => $delivery
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $delivery = Delivery::with('delivery_items')->findOrFail($id);

        return response([
            'message' => "Success",
            'data' => $delivery
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        /**
         * * Validation Request
         */
        $validate = Validator::make($request->all(), [
            'sender_name' => 'nullable|string|max:255',
            'sender_phone' => 'nullable|string|max:18',
            'receiver_name' => 'nullable|string|max:255',
            'receiver_phone' => 'nullable|string|max:18',
            'receiver_address' => 'nullable|string|max:255',
            'delivery_fee' => 'nullable|numeric|min:0',
            'sprinter' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',

            // 'delivery_items' => 'nullable|array',
            // 'delivery_items.*.item_name' => 'required|string|max:255',
            // 'delivery_items.*.quantity' => 'required|numeric|min:0',
            // 'delivery_items.*.note' => 'nullable|string|max:255',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => "Error: unprocessable entity, validation error!",
                'errors' => $validate->errors(),
            ], 422);
        }

        DB::beginTransaction();

        /**
         * * Delivery Update
         */
        $delivery = Delivery::find($id);

        if (!$delivery) {
            return response()->json([
                'message' => "Delivery data not found",
            ], 404);
        }

        // * Process
        foreach ($delivery->getFillable() as $field) {

            if ($request->{$field} && $request->{$field} !== null) {

                $delivery->{$field} = $request->{$field};
            }
        }

        try {
            $delivery->save();
        } catch (\Throwable $th) {
            DB::rollback();
            return response([
                'message' => "Error: failed to update delivery",
                'error' => $th
            ], 500);
        }

        DB::commit();

        return response([
            'message' => 'Success',
            'data' => $delivery
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $delivery = Delivery::findOrFail($id);

        // * Delete Delivery Items
        try {
            DeliveryItem::where('delivery_id', $delivery->id)->delete();
        } catch (\Throwable $th) {
            DB::rollback();
            return response([
                'message' => "Error: failed to batch delete delivery items",
                'error' => $th
            ], 500);
        }

        // * Delete Delivery
        try {
            $delivery->delete();
        } catch (\Throwable $th) {
            DB::rollback();
            return response([
                'message' => "Error: failed to delete delivery data",
                'error' => $th
            ], 500);
        }

        return response([
            'message' => "Success",
            'data' => 1
        ]);
    }
}
