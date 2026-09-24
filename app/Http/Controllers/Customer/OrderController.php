<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PinSize;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->withCount('items')->latest()->get();

        return view('customer.orders.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $sizes = PinSize::active()->orderBy('price')->orderBy('name')->get();
        $reference = $request->filled('product') ? Product::find($request->integer('product')) : null;

        return view('customer.orders.create', compact('sizes', 'reference'));
    }

    // Menambah satu item ke draft pesanan milik pelanggan (draft dibuat otomatis jika belum ada).
    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'pin_size_id' => ['required', Rule::exists('pin_sizes', 'id')->where('is_active', true)],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'design' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'pin_size_id.required' => 'Pilih ukuran pin.',
            'pin_size_id.exists' => 'Ukuran yang dipilih tidak tersedia.',
            'quantity.required' => 'Isi jumlah pesanan.',
            'quantity.min' => 'Jumlah minimal 1.',
            'quantity.max' => 'Jumlah maksimal 1000 per item.',
            'design.required' => 'Unggah foto desain.',
            'design.image' => 'File harus berupa gambar.',
            'design.mimes' => 'Format gambar harus JPG, PNG, atau WEBP.',
            'design.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $size = PinSize::active()->findOrFail($data['pin_size_id']);
        $path = $request->file('design')->store('designs', 'local'); // disk privat, tidak bisa dibuka lewat URL langsung

        try {
            $order = DB::transaction(function () use ($request, $data, $size, $path) {
                $order = Order::where('user_id', $request->user()->id)
                    ->where('status', Order::STATUS_DRAFT)
                    ->lockForUpdate()
                    ->first()
                    ?? Order::create(['user_id' => $request->user()->id, 'status' => Order::STATUS_DRAFT]);

                $order->items()->create([
                    'pin_size_id' => $size->id,
                    'size_name' => $size->name,
                    'unit_price' => $size->price,
                    'quantity' => $data['quantity'],
                    'design_path' => $path,
                    'notes' => $data['notes'] ?? null,
                ]);
                $order->recalculateTotal();

                return $order;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Item ditambahkan ke pesanan.');
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeOwner($request, $order);
        $order->load('items');

        return view('customer.orders.show', compact('order'));
    }

    public function destroyItem(Request $request, Order $order, OrderItem $item)
    {
        $this->authorizeOwner($request, $order);
        abort_unless($item->order_id === $order->id, 404);

        if (! $order->isEditable()) {
            return back()->withErrors(['order' => 'Pesanan ini sudah tidak bisa diubah.']);
        }

        Storage::disk('local')->delete($item->design_path);
        $item->delete();
        $order->recalculateTotal();

        return back()->with('success', 'Item dihapus dari pesanan.');
    }

    // Foto desain hanya bisa dibuka pemilik pesanan atau admin.
    public function design(Request $request, Order $order, OrderItem $item)
    {
        abort_unless($request->user()->isAdmin() || $order->user_id === $request->user()->id, 404);
        abort_unless($item->order_id === $order->id, 404);
        abort_unless(Storage::disk('local')->exists($item->design_path), 404);

        return Storage::disk('local')->response($item->design_path, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    // 404 (bukan 403) agar pelanggan lain tidak bisa menebak nomor pesanan yang ada.
    private function authorizeOwner(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 404);
    }
}
