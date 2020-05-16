<?php

namespace App\Imports;

use App\Models\Branch;
use App\Traits\ImportTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class BranchesImport implements ToCollection
{
    use ImportTrait;

    private $columnNames = ['area_id', 'name', 'lng', 'lat', 'mobile', 'working_hours_from', 'working_hours_to', 'working_days_from', 'working_days_to'];

    public function collection(Collection $rows)
    {
        $this->validateColumnNames($rows[0]->toArray());
        unset($rows[0]);

        DB::beginTransaction();
        foreach ($rows as $key => $row) {
            $rowData = array_slice($row->toArray(), 0, 9);
            $mappedDataToModel = $this->mapToModel($this->columnNames, $rowData);

            $this->validation($mappedDataToModel, $key, [
                'area_id' => 'required|exists:areas,id',
                'lng' => 'required',
                'lat' => 'required',
                'mobile' => 'required',
                'working_hours_from' => 'required',
                'working_hours_to' => 'required',
                'working_days_from' => 'required',
                'working_days_to' => 'required',
            ]);

            $mappedDataToModel['store_id'] = auth()->user()->id;
            $branch = Branch::create($mappedDataToModel);
        }
        DB::commit();
    }
}
