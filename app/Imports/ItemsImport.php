<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Item;
use App\Traits\ImportTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class ItemsImport implements ToCollection
{
    use ImportTrait;

    private $columnNames = ['name_en', 'name_ar', 'quantity', 'basic_price', 'discount'];
    private $branch;

    public function __construct(Branch $branch=null)
    {
        $this->branch = $branch;
    }

    public function collection(Collection $rows)
    {
        $this->validateColumnNames($rows[0]->toArray());
        unset($rows[0]);

        DB::beginTransaction();
        foreach ($rows as $key => $row) {
            $rowData = array_slice($row->toArray(), 0, 5);
            $mappedDataToModel = $this->mapToModel($this->columnNames, $rowData);

            $this->validation($mappedDataToModel, $key, [
                'name_en' => 'required',
                'name_ar' => 'required',
                'quantity' => 'required|int|min:0',
                'basic_price' => 'required',
                'discount' => 'required|int|min:0'
            ]);

            $mappedDataToModel['branch_id'] = $this->branch->id;
            $item = Item::create($mappedDataToModel);
        }
        DB::commit();
    }
}
