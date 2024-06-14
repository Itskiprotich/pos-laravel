<?php

namespace App\Http\Controllers\api\v1;

use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use App\Exports\EmployeeListExport;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Validation\Rules\Password;

class EmployeeController extends Controller
{

    public function list()
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $employees = Admin::with(['role'])->where('role_id', '!=', '1')->latest()->paginate($limit, ['*'], 'page', $offset);
        if ($employees->count() > 0) {
            $employees->each(function ($employee) {
                if (isset($employee->role->modules) && gettype($employee->role->modules) == 'string') {
                    $employee->role->modules = json_decode($employee->role->modules);
                }
            });
        }
        $data = [
            'total' => $employees->total(),
            'limit' => $limit,
            'offset' => $offset,
            'employees' => $employees->items()
        ];
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'nullable|max:100',
            'role_id' => 'required',
            'image' => 'required|max:2048',
            'email' => 'required|unique:admins',
            'phone' => 'required',
            'password' => ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => \App\CPU\Helpers::error_processor($validator)], 403);
        }

        if ($request->role_id == 1) {
            return response()->json([
                'success' => true,
                'message' => 'access denied',
            ], 200);
        }

        if (!empty($request->file('image'))) {
            $imageName = \App\CPU\Helpers::upload('admin/', 'png', $request->file('image'));
        } else {
            $imageName = 'def.png';
        }

        Admin::insert([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'password' => bcrypt($request->password),
            'image' => $imageName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee saved successfully',
        ], 200);
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|max:100',
            'l_name' => 'nullable|max:100',
            'role_id' => 'required',
            'email' => 'required|unique:admins,email,' . $id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|max:20|unique:admins,phone,' . $id,
            'password' => ['nullable', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            'image' => 'nullable|max:2048',
        ], [
            'f_name.required' => \App\CPU\translate('first_name_is_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => \App\CPU\Helpers::error_processor($validator)], 403);
        }

        if ($request->role_id == 1) {
            return response()->json([
                'success' => true,
                'message' => 'access denied',
            ], 200);
        }

        $employee = Admin::where('role_id', '!=', 1)->findOrFail($id);

        if (auth('admin')->id() == $employee['id']) {
            return response()->json([
                'success' => true,
                'message' => 'You_can_not_edit_your_own_info',
            ], 200);
        }

        if ($request['password'] == null) {
            $pass = $employee['password'];
        } else {

            $pass = bcrypt($request['password']);
        }

        if ($request->has('image')) {
            $employee['image'] = \App\CPU\Helpers::update(dir: 'admin/', old_image: $employee->image, format: 'png', image: $request->file('image'));
        }

        Admin::where(['id' => $id])->update([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'password' => $pass,
            'image' => $employee['image'],
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'employee_ saved successfully',
        ], 200);
    }

    public function delete(Request $request)
    {
        $id = $request->id;
        $role = Admin::where('role_id', '!=', '1')->where(['id' => $id])->first();

        if (auth('admin')->id() == $role['id']) {
            return response()->json([
                'success' => true,
                'message' => 'You_can_not_edit_your_own_info',
            ], 200);
        }
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'employee_deleted_successfully',
        ], 200);
    }

    function employee_list_export(Request $request)
    {
        try {
            $key = explode(' ', $request['search']);
            $employees = Admin::zone()->with(['role'])->where('role_id', '!=', '1')
                ->when(isset($key), function ($q) use ($key) {
                    $q->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%");
                            $q->orWhere('l_name', 'like', "%{$value}%");
                            $q->orWhere('phone', 'like', "%{$value}%");
                            $q->orWhere('email', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()->get();
            $data = [
                'employees' => $employees,
                'search' => $request->search ?? null,
            ];

            if ($request->type == 'excel') {
                return Excel::download(new EmployeeListExport($data), 'Employees.xlsx');
            } else if ($request->type == 'csv') {
                return Excel::download(new EmployeeListExport($data), 'Employees.csv');
            }

        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }
}
