<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Controller as BaseController;
use Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function handleResponse($status, $data)
    {
        if($status == 1)
            return response()->json(['status'=>$status,'data'=>$data]);
        else
            return response()->json(['status'=>$status,'errors'=>$data]);
    }

    public function handlePaginateResponse($status, $data)
    {
        if($status == 1) {
            $result['items'] = $data->items();
            $result['total'] = $data->total();
            return response()->json(['status'=>$status,'data'=>$result]);
        }
        else
            return response()->json(['status'=>$status,'errors'=>$data]);
    }

    public function validation($request, $rule, $messages = [])
    {
        $validator = Validator::make($request->all(), $rule, $messages);
        if ($validator->fails()) {
            $errors = $validator->messages()->toArray();

            foreach ($errors as $key => $error)
                $errorR[$key] = $error[0];

            $data['status']=0;
            $data['errors']['validation']=$errorR;
            throw new HttpResponseException(response()->json($data, 422));
        }
        return true;
    }
}
