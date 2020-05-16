<?php

namespace App\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;
use Validator;

trait ImportTrait
{
    public function validation($row, $rowKey, $rules)
    {
        $validator = Validator::make($row, $rules);
        if ($validator->fails()) {
            $errors = $validator->messages()->toArray();

            foreach ($errors as $key => $error)
                $errorR[$key] = $error[0]. ' at row ' . $rowKey+1;

            $data['status']=0;
            $data['errors']['validation']=$errorR;
            throw new HttpResponseException(response()->json($data, 422));
        }
        return true;
    }

    public function mapToModel($keys, $values)
    {
        if(count($keys) != count($values)) {
            $data['status']=0;
            $data['errors']['validation']=['error in mapToModel function line 60 in BranchesImport'];
            throw new HttpResponseException(response()->json($data, 422));
        }

        $result = [];
        $index = 0;
        foreach ($keys as $key)
            $result[$key] = $values[$index++];

        return $result;
    }

    public function validateColumnNames($names)
    {
        $errors = array_diff($this->columnNames, $names);

        if($errors){
            $messages = [];
            foreach ($errors as $error)
                $messages[] = $error . ' column name is missed.';
            $data['status']=0;
            $data['errors']['validation']=$messages;
            throw new HttpResponseException(response()->json($data, 422));
        }
    }
}
