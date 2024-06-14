<?php

namespace App\Http\Controllers\Api\V1;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class CustomRoleController extends Controller
{
    public function list(Request $request)
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $roles = AdminRole::whereNotIn('id', [1])->latest()->paginate($limit, ['*'], 'page', $offset);
        if ($roles->count() > 0) {
            $roles->each(function ($role) {
                if ($role->modules) {
                    $role->modules = json_decode($role->modules);
                }
            });
        }
        $data =  [
            'total' => $roles->total(),
            'limit' => $limit,
            'offset' => $offset,
            'role' => $roles->items()
        ];
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:admin_roles|max:191',
            'modules'=>'required|array|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $role = new AdminRole();
        $role->name = $request->name;
        $role->modules = json_encode($request->modules);
        $role->status = 1;
        $role->save();

        return response()->json([
            'success' => true,
            'message' => 'Role saved successfully',
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:191|unique:admin_roles,name,'.$request->id,
            'modules'=>'required|array|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $id = $request->id;
        if($id == 1)
        {
            return response()->json([
                'success' => true,
                'message' => translate('Access denied'),
            ],  403);
        }


        $role = AdminRole::find($id);
        $role->name = $request->name;
        $role->modules = json_encode($request['modules']);
        $role->status = 1;
        $role->save();

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
        ],  200);
    }
    public function delete(Request $request): JsonResponse
    {
        if ($request->id == 1) {
            return response()->json(['success' => false, 'message' => translate('Not permitted')],  403);
        }

        $role = AdminRole::where('id', $request->id)->first();
        if (!isset($role)) return response()->json(['success' => false, 'message' => translate('Not found')],  404);

        $role->delete();
        return response()->json(['success' => true, 'message' => translate('Role deleted successfully')],  200);
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $roles=AdminRole::where('id','!=','1')
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        })->latest()->limit(50)->get();
        return response()->json([
            'view'=>$roles->render(),
            'count'=>$roles->count()
        ]);
    }
}
