<?php

namespace App\Http\Controllers\Api\V1;

use App\CPU\Helpers;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\Categoy\CategoryUpdateRequest;
use function App\CPU\translate;

class SubCategoryController extends Controller
{
    public function __construct(
        private Category $category
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getIndex(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $categoryId = $request['category_id'] ?? 1;

        try {
            $subCategories = $this->category->where(['position' => 1,'parent_id' => $categoryId])->latest()->paginate($limit, ['*'], 'page', $offset);
            $data =  [
                'total' => $subCategories->total(),
                'limit' => $limit,
                'offset' => $offset,
                'subCategories' => $subCategories->items()
            ];
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['Result' => translate('Data not found')], 404);
        }
    }

    /**
     * @param Request $request
     * @param Category $subCategory
     * @return JsonResponse
     */
    public function postStore(Request $request, Category $subCategory): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $parentId = $request->parent_id ?? 0;
        $allCategory = $this->category->where(['parent_id' => $parentId])->pluck('name')->toArray();

        if (in_array($request->name, $allCategory)) {
            return response()->json(['success' => false, 'message' => translate('Sub category already exists!')], 403);
        }

        if (!empty($request->file('image'))) {
            $imageName =  Helpers::upload('category/', 'png', $request->file('image'));
        } else {
            $imageName = 'def.png';
        }
        try {
            $subCategory->name = $request->name;
            $subCategory->parent_id = $request->parent_id;
            $subCategory->position = 1;
            $subCategory->image = $imageName;
            $subCategory->save();
            return response()->json([
                'success' => true,
                'message' => translate('Sub Category saved successfully'),
            ], 200);
        } catch (\Exception $th) {
            info($th);
            return response()->json([
                'success' => false,
                'message' => translate('Category not saved')
            ], 403);
        }
    }

    /**
     * @param CategoryUpdateRequest $request
     * @return JsonResponse
     */
    public function postUpdate(CategoryUpdateRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' =>'required|unique:categories,name,'.$request->id
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $category = $this->category->findOrFail($request->id);
        $category->name = $request->name;
        $category->image = $request->has('image') ? Helpers::update('category/', $category->image, 'png', $request->file('image')) : $category->image;
        $category->update();
        return response()->json([
            'success' => true,
            'message' => translate('Category updated successfully'),
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function delete(Request $request)
    {
        try {
            $category = $this->category->findOrFail($request->id);
            $imagePath  = public_path('storage/category/') . $category->image;
            if (!is_null($category)) {
                $category->delete();
                unlink($imagePath);
                return response()->json(
                    [
                        'success' => true,
                        'message' => translate('Category deleted successfully'),
                    ],
                    200
                );
            }
        } catch (\Exception $th) {
            info($th);
            return response()->json([
                'success' => false,
                'message' => translate('Category not deleted'),
                'err' > $th
            ], 403);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getSearch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $result = $this->category->active()->where('name', 'LIKE', '%' . $request->name . '%')->get();
        if (count($result)) {
            return Response()->json($result, 200);
        } else {
            return response()->json(['message' => translate('Data not found')], 404);
        }
    }
}
