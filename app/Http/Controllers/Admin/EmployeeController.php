<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use App\Exports\EmployeeListExport;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Validation\Rules\Password;

class EmployeeController extends Controller
{

    /**
     * @return Application|Factory|View
     */
    public function add_new(Request $request): View|Factory|Application
    {
        $queryParam = [];
        $search = $request['search'];
        $roles = AdminRole::whereNotIn('id', [1])->get();

        if ($request->has('search')) {
            $key = explode(' ', $search);
            $employees = Admin::with(['role'])->where('role_id', '!=','1')->latest()
                ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });

            $queryParam = ['search' => $request['search']];
        } else {
            $employees = Admin::with(['role'])->where('role_id', '!=','1')->latest();
        }

        $employees = $employees
            ->paginate(\App\CPU\Helpers::pagination_limit())
            ->appends($queryParam);
        return view('admin-views.employee.add-new', compact('roles','employees', 'search'));

    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'f_name' => 'required',
            'l_name' => 'nullable|max:100',
            'role_id' => 'required',
            'image'=>'image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'required|email|unique:admins,email',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|max:20|unique:admins,phone',
            'password' => 'required|confirmed',
        ]);

        if ($request->role_id == 1) {
            Toastr::warning(\App\CPU\translate('access_denied'));
            return back();
        }

        if (!empty($request->file('image'))) {
            $image_name =  \App\CPU\Helpers::upload('admin/', 'png', $request->file('image'));
        } else {
            $image_name = 'def.png';
        }

        Admin::insert([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'password' => bcrypt($request->password),
            'image' => $image_name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Toastr::success(\App\CPU\translate('employee_added_successfully'));
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function list(Request $request): Factory|View|Application
    {
        $key = explode(' ', $request['search']);
        $employees = Admin::with(['role'])->where('role_id', '!=','1')
        ->when(isset($key) , function($q) use($key){
            $q->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%");
                    $q->orWhere('l_name', 'like', "%{$value}%");
                    $q->orWhere('phone', 'like', "%{$value}%");
                    $q->orWhere('email', 'like', "%{$value}%");
                }
            });
        })
        ->latest()->paginate(config('default_pagination'));
        return view('admin-views.employee.list', compact('employees'));
    }

    /**
     * @param $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function edit($id): View|Factory|RedirectResponse|Application
    {
        $employee = Admin::where('role_id', '!=','1')->where(['id' => $id])->first();
        if (auth('admin')->id()  == $employee['id']){
            Toastr::error(\App\CPU\translate('You_can_not_edit_your_own_info'));
            return redirect()->route('admin.employee.list');
        }
        $roles = AdminRole::whereNotIn('id', [1])->get();
        return view('admin-views.employee.edit', compact('roles', 'employee'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'f_name' => 'required|max:100',
            'l_name' => 'nullable|max:100',
            'role_id' => 'required',
            'email' => 'required|email|unique:admins,email,'.$id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|max:20|unique:admins,phone,'.$id,
            'password' => ['nullable','confirmed', Password::min(8)],
            'image'=>'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'f_name.required' => \App\CPU\translate('first_name_is_required'),
        ]);


        if ($request->role_id == 1) {
            Toastr::warning(\App\CPU\translate('access_denied'));
            return back();
        }

        $employee = Admin::where('role_id','!=',1)->findOrFail($id);
        if (auth('admin')->id()  == $employee['id']){
            Toastr::error(\App\CPU\translate('You_can_not_edit_your_own_info'));
            return redirect()->route('admin.employee.list');
        }

        if ($request['password'] == null) {
            $password = $employee['password'];
        } else {
            $password = bcrypt($request['password']);
        }

        if ($request->has('image')) {
            $employee['image'] = \App\CPU\Helpers::update(dir:'admin/', old_image: $employee->image, format: 'png', image:$request->file('image'));
        }

       Admin::where(['id' => $id])->update([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'password' => $password,
            'image' => $employee['image'],
            'updated_at' => now(),
        ]);

        Toastr::success(\App\CPU\translate('employee_updated_successfully'));
        return redirect()->back();
    }

    public function distroy($id)
    {
        $role=Admin::where('role_id', '!=','1')->where(['id'=>$id])->first();
        if (auth('admin')->id()  == $role['id']){
            Toastr::error(\App\CPU\translate('You_can_not_edit_your_own_info'));
            return redirect()->route('admin.employee.list');
        }
        $role->delete();
        Toastr::info(\App\CPU\translate('employee_deleted_successfully'));
        return back();
    }

    public function employee_list_export(Request $request)
    {
        try{
            $key = explode(' ', $request['search']);
            $employees=Admin::zone()->with(['role'])->where('role_id', '!=','1')
            ->when(isset($key) , function($q) use($key){
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
                'employees'=>$employees,
                'search'=>$request->search??null,
            ];

            if ($request->type == 'excel') {
                return Excel::download(new EmployeeListExport($data), 'Employees.xlsx');
            } else if ($request->type == 'csv') {
                return Excel::download(new EmployeeListExport($data), 'Employees.csv');
            }

        } catch(\Exception $e) {
                Toastr::error("line___{$e->getLine()}",$e->getMessage());
                info(["line___{$e->getLine()}",$e->getMessage()]);
                return back();
            }
    }
}
