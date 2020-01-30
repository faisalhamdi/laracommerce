<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use File;
Use Image;

class ProductController extends Controller
{
    public function index() {
        $products = Product::with(['category'])->orderBy('created_at', 'DESC');

        if (request()->q != '') {
            $products = $products->where('name', 'LIKE', '%' . request()->q . '%');
        }

        $products = $products->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create() {
        $category = Category::orderBy('name', 'DESC')->get();
        
        return view('products.create', compact('category'));
    }

    public function store(Request $r) {
        $this->validate($r, [
            'name' => 'required|string|max:100',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|integer',
            'weight' => 'required|integer',
            'image' => 'required|image|mimes:png,jpg,jpeg'
        ]);
        // echo $r->hasFile('image'); 
        // $image = $this->saveFile($r->name, $r->file('image'));
        // print_r($image); die;
        try {
            $image = null;
            if ($r->hasFile('image')) {
                $image = $this->saveFile($r->name, $r->file('image'));
                // return $image->toString(); die;
            }
            
            $product = Product::create([
                'name' => $r->name,
                'slug' => $r->name,
                'category_id' => $r->category_id,
                'description' => $r->description,
                'image' => $image,
                'price' => $r->price,
                'weight' => $r->weight,
                'status' => $r->status
            ]);

            return redirect(route('products.index'))->with(['success' => 'New Product Added']);

        } catch (Exception $e) {
            return redirect()->back()
            ->with(['error' => $e->getMessage()]);
        }
    }

    private function saveFile($name, $photo) {
        $image = time('Y-m-d') . Str::slug($name) . '.' . $photo->getClientOriginalExtension();
        $path = public_path('uploads/product');
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }
        Image::make($photo)->save($path . '/' . $image);
        return $image;
    }

    /* private function saveFileNative($name, $photo) {
        $images = time() . Str::slug($name) . '.' . $photo->getClientOriginalExtension();
        $path = public_path();
        $paths = explode('/','uploads/product');
        chmod($path, 0777); 
        // return File::isDirectory($path); die;
        foreach($paths as $folder) {
            if (!empty($folder)) {
                $path .= '/' . $folder;
                echo $path;
                if (!File::isDirectory($path)) {
                    mkdir($path, 0777);
                    // File::makeDirectory($path, 0777, true, true);
                    die('test');
                } 
            }
        }
        // if (!File::isDirectory($path)) {
        //     mkdir()
        //     File::makeDirectory($path, 0777, true, true);
        // } 
        Image::make($photo)->save($path . '/' . $images);
        return $images;
    } */

    public function destroy($id) {
        $product = Product::find($id);
        if (!empty($product->image)) {
            \File::delete(public_path('uploads/product/' . $product->image));
        }            
        $product->delete();

        return redirect(route('products.index'))->with(['success' => 'Product deleted']);
    }
}
