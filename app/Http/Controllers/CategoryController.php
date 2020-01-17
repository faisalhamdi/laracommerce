<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() {
        $category = Category::with(['parent'])->orderBy('created_at', 'DESC')->paginate(10);

        $parent = Category::getParent()->orderBy('name', 'ASC')->where('deleted_at', '=', 'NULL')->get();

        return view('categories.index', compact('category', 'parent'));
    }

    public function store(Request $r) {
        $this->validate($r, [
            'name' => 'required|string|max:50|unique:categories'
        ]);

        $r->request->add(['slug' => $r->name]);

        Category::create($r->except('_token'));

        return redirect(route('categories.index'))->with(['success' => 'New category added!']);
    }

    public function edit($id) {
        $category = Category::find($id);
        $parent = Category::getParent()->orderBy('name', 'ASC')->get();

        return view('categories.edit', compact('category', 'parent'));
    }

    public function update(Request $r, $id) {
        $this->validate($r, [
            'name' => 'required|string|max:50|unique:categories,name,' . $id     
        ]);

        $category = Category::find($id);

        $category->update([
            'name' => $r->name,
            'parent_id' => $r->parent_id
        ]);
        
        return redirect(route('categories.index'))->with(['success' => 'Category updated!']);
    }

    public function destroy($id) {
        $category = Category::withCount(['child'])->find($id);

        if ($category->child_count == 0) {            
            $category->delete();            
            return redirect(route('categories.index'))->with(['success' => 'Category successfully move to trash!']);
        }
        
        return redirect(route('categories.index'))->with(['error' => 'This Category has child category!']);

    }

    public function trash() {
        $deleted_category = Category::onlyTrashed()->paginate(10);

        return view('categories.trash', ['categories' => $deleted_category]);
    }

    public function restore($id) {
        $category = Category::withTrashed()->findOrFail($id);

        if ($category->trashed()) {
            $category->restore();
        } else {
            return redirect(route('categories.index'))->with(['success' => 'Category is not in trash']);
        }
        
        return redirect(route('categories.index'))->with(['success' => 'Category successfully restore']);
    }

    public function deletePermanent($id) {
        $category = Category::withTrashed()->findOrFail($id);

        if (!$category->trashed()) {
            return redirect(route('categories.index'))->with(['success' => 'Can not delete permanent active category']);
        } else {
            $category->forceDelete();
            return redirect(route('categories.index'))->with(['success' => 'Category permanently deleted']);
        }
    }
}
