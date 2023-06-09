<?php

namespace Database\Seeders;

use App\Models\Statusflow;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use App\Models\StatusflowDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'code' => 'DELIVERY',
                'name' => 'Delivery',
                'description' => 'Normal delivery status flow',
                'details' => [
                    [
                        'current_status' => null,
                        'next_status' => 'pending',
                        'level' => 1,
                        'row_active' => 1,
                        'row_order' => 1,
                        'description' => null,
                        'created_at' => Carbon::now()
                    ],
                    [
                        'current_status' => 'pending',
                        'next_status' => 'cancel',
                        'level' => 2,
                        'row_active' => 1,
                        'row_order' => 1,
                        'description' => null,
                        'created_at' => Carbon::now()
                    ],
                    [
                        'current_status' => 'pending',
                        'next_status' => 'process',
                        'level' => 2,
                        'row_active' => 1,
                        'row_order' => 2,
                        'description' => null,
                        'created_at' => Carbon::now()
                    ],
                    [
                        'current_status' => 'process',
                        'next_status' => 'delivering',
                        'level' => 3,
                        'row_active' => 1,
                        'row_order' => 1,
                        'description' => null,
                        'created_at' => Carbon::now()
                    ],
                    [
                        'current_status' => 'process',
                        'next_status' => 'cancel',
                        'level' => 3,
                        'row_active' => 1,
                        'row_order' => 2,
                        'description' => null,
                        'created_at' => Carbon::now()
                    ],
                    [
                        'current_status' => 'delivering',
                        'next_status' => 'done',
                        'level' => 4,
                        'row_active' => 1,
                        'row_order' => 1,
                        'description' => null,
                        'created_at' => Carbon::now()
                    ],
                ],
            ]
        ];


        foreach ($data as $key => $one) {

            $statusflow = Statusflow::create([
                'code' => $one['code'],
                'name' => $one['name'],
                'description' => $one['description']
            ]);

            if ($one['details']) {

                $statusflowDetail = StatusflowDetail::insert(array_map(fn ($item) => ['statusflow_id' => $statusflow->id, ...$item], $one['details']));
            }
        }
    }
}
