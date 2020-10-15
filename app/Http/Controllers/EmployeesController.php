<?php

namespace App\Http\Controllers;

use App\Employee;
use App\FileUpload;
use App\Validation;
use Illuminate\Http\Request;
use App\Http\Controllers\FileUploadController;


class EmployeesController extends Controller {

    public function __construct() {
        $this->middleware('jwt-auth');
    }

    public function index() {
        $data['status'] = true;
        $data['employees'] = Employee::all();
        return response()->json(compact( 'data'));
    }

    public function store(Request $request)
    {
        $param = [
            'name' => 'required',
            'email' => 'required|email|unique:employees',
            'phone' => 'required|numeric',
            'emp_id' => 'required|numeric',
            'company' => 'required | string',
            'location' => 'required | string',
        ];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $data = $request->All();
            try {
                Employee::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'emp_id' => $data['emp_id'],
                    'company' => $data['company'],
                    'location' => $data['location'],
                ]);
                $response = response()->json(array('status' => true, 'msg' => 'Successfully Created'), 200);
            } catch (\Exception $e) {
                $response = response()->json(array('message' => 'could_not_create_employee'), 500);
            }
        }
        return $response;
    }

    public function show($id)
    {
        $response = response()->json(array('message' => 'employee_not_found'), 200);
        try {
            $employee = Employee::where('id', $id)->first();
            if ($employee != null) {
                $response = response()->json(array('status' => true, 'employee' => $employee), 200);
            }
        } catch (\Exception $e) {
            $response = response()->json(array('message' => 'could_not_create_employee'), 500);
        }
        return $response;
    }

    public function update(Request $request, $id)
    {
        $response = response()->json(array('message' => 'employee_not_found'), 200);
        $employee = Employee::where('id', $id)->first();
        $data = $request->All();
        if ($employee != null) {
            try {
                Employee::where('id', $id)->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'emp_id' => $data['emp_id'],
                    'company' => $data['company'],
                    'location' => $data['location'],
                ]);
                $response = response()->json(array('status' => true, 'message' => 'updated_employee'), 200);
            } catch (\Exception $e) {
                $response = response()->json(array('message' => 'could_not_update_employee'), 500);
            }
        }
        return $response;
    }

    public function destroy($id)
    {
        $response = response()->json(array('message' => 'employee_not_found'), 500);
        $employee = Employee::where('id', $id)->first();
        if ($employee != null) {
            try {
                Employee::where('id', $id)->delete();
            } catch (\Exception $e) {
                $response = response()->json(array('message' => 'could_not_update_employee'), 500);
            }
        }
        $response = response()->json(array('status' => true, 'message' => 'employee_deleted'), 200);
        return $response;
    }

    public function fileupload(Request $request) {
        $param = ['file' => 'required'];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            if (isset($data['file'])) {
                $file = $data['file'];
                unset($data['file']);
                $data['name'] = FileUploadController::fileUpload($file, 'uploads/students');
            }
            FileUpload::create($data);
            $response = response()->json(array('status' => true, 'msg' => 'Successfully created'), 200);
        }
        return $response;
    }

    public function filelist() {
        $data['files'] = FileUpload::all();
        return response()->json(compact( 'data'));
    }

    public function filedelete($id)
    {
        $response = response()->json(array('message' => 'file_not_found'), 500);
        try {
            $employee = FileUpload::where('id', $id)->first();
            if ($employee != null) {
                FileUpload::where('id', $id)->delete();
                $response = response()->json(array('status' => true, 'message' => 'file_deleted'), 200);
            }
        } catch (\Exception $e) {
            $response = response()->json(array('message' => 'could_not_file'), 500);
        }
        return $response;
    }
}
