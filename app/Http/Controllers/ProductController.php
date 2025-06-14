<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use DB;
use Illuminate\Validation\Rule;


class ProductController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'entry_date');
        $order = $request->get('order', 'desc');
        
        $products = Product::where('status', 1)
        ->orderBy($sort, $order)
        ->paginate(10);
        
        if ($request->ajax()) {
           return response()->json([
                'html' => view('product.item.table', compact('products'))->render(),
                'pagination' => view('product.item.pagination', compact('products'))->render()
            ]);
        }
        return view('product.item.index', compact('products'));
    }

    public function store(Request $request)
    {
        // En este punto se crea el escenario que en algún punto algo sale mal, es mejor revertir todo el proceso en lugar de guardar lo que se pueda
        // Uso el manejo de errores con try catch
        try{
            DB::beginTransaction();

            $validated = $request->validate([
                // Evitar caracteres especiales
                'code' => 'required|unique:product|regex:/^[a-zA-Z0-9]+$/',
    
                // Evitar caracteres especiales
                'name' => 'required|regex:/^[a-zA-Z\s]+$/',
    
                'quantity' => 'required|integer|min:1',
                // Validación para el tamaño de la imagen
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1536',
    
                'price' => 'required|numeric|min:0',
                'entry_date' => 'required|date',
                'expiration_date' => 'required|date|after:entry_date',
            ]);
    
            
            if ($request->hasFile('image')) {
                try {
                    $image = $request->file('image');
                    // Almacenar la imagen en storage/app/public/product_images
                    $imagePath = $request->file('image')->store('product_images', 'public');

                    $name_image = uniqid() . '_' . now()->format('YmdHis') . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('product_images/'), $name_image);
                    
                    // Guardar solo la ruta relativa en la base de datos
                    $validated['image_path'] = $name_image;
                } catch (\Exception $e) {
                    return back()->withError('Error al subir la imagen: '.$e->getMessage());
                }
            }
            $validated['status'] = 1;
    
            // Verifico el formato de fechas por si acaso
            // $validated['entry_date'] = Carbon::createFromFormat('d/m/Y', $validated['entry_date']);
            // $validated['expiration_date'] = Carbon::createFromFormat('d/m/Y', $validated['expiration_date']);
    
            $product = Product::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'product' => $product
            ]);
        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Error de validación'
            ], 422);
        }

        return response()->json(['success' => true, 'product' => $product]);
    }

    public function create()
    {
        return view('product.item.create');
    }

    public function edit(Product $product)
    {
        return view('product.item.edit', compact('product'));
    }

    public function get_all()
    {
    }

    public function show($id)
    {
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => 'required|regex:/^[a-zA-Z0-9]+$/|unique:product,code,' . ($product->id ?? ''),
            'name' => 'required|regex:/^[a-zA-Z\s]+$/',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'entry_date' => 'required|date',
            'expiration_date' => 'required|date|after:entry_date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1536'
        ]);

        $to_use = 0;
        if ($request->has('remove_image') && $request->remove_image == 'on') {
            if ($product->image_path) {
                $imagePath = public_path('product_images/'.$product->image_path);
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Elimina el archivo físico
                }
                $validated['image_path'] = null; // Elimina la referencia en la BD
                $to_use = 1;
            }
        }

        // Manejo de la imagen
        if ($request->hasFile('image')) {
            try {
                // Eliminar imagen anterior si existe
                if ($product->image_path) {
                    $oldImagePath = public_path('product_images/'.$product->image_path);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $image = $request->file('image');
                $name_image = uniqid().'_'.now()->format('YmdHis').'.'.$image->getClientOriginalExtension();
                $image->move(public_path('product_images/'), $name_image);
                
                $validated['image_path'] = $name_image;
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al subir la imagen: '.$e->getMessage()
                ], 500);
            }
        } elseif (!isset($validated['image_path']) && $to_use == 0) {
            // Mantener la imagen existente si no se marca para eliminar ni se sube nueva
            $validated['image_path'] = $product->image_path;
        }

        try{
            DB::beginTransaction();
            // Unicamente verificamos la actualización en el try catch
            $product->update($validated);
            DB::commit();
        } catch (\Exception $e)
        {
            // Por si algo sale mal
            DB::rollback();
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }
    
    public function destroy(Request $request, $id)
    {
        // Prueba directa de cambio de estado
        $product = Product::findOrFail($id);
        // $product->status = 0;
        // $product->update();

        if ($product->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar productos con status activo (1)'
            ], 403); // Código 403 Forbidden
        }

        try {
            DB::beginTransaction();
            // Eliminar la imagen asociada si existe
            if ($product->image_path) {
                $imagePath = public_path('product_images/'.$product->image_path);
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Elimina el archivo físico
                }
            }

            $product->status = 0;
            $product->update();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto: '.$e->getMessage()
            ], 500); // Código 500 Internal Server Error
        }
    }
}
