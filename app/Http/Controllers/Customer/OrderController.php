<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\InvalidOrderTransition;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PinSize;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
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

    // Form pin custom: pilih ukuran, unggah desain.
    public function create(Request $request)
    {
        $sizes = PinSize::active()->orderBy('availability')->orderBy('price')->orderBy('name')->get();
        $waitingOrder = $request->user()->orders()->where('status', Order::STATUS_WAITING_PAYMENT)->first();

        return view('customer.orders.create', compact('sizes', 'waitingOrder'));
    }

    // Daftar produk lain (bukan pin custom) yang bisa dipesan.
    public function products()
    {
        $products = Product::orderable()->latest()->get();

        return view('customer.orders.products', compact('products'));
    }

    // Form produk katalog: tanpa ukuran/desain, cukup jumlah. Foto dan harga mengikuti produknya.
    public function createProduct(Request $request, Product $product)
    {
        abort_unless($product->isOrderable(), 404);
        $waitingOrder = $request->user()->orders()->where('status', Order::STATUS_WAITING_PAYMENT)->first();

        return view('customer.orders.create-product', compact('product', 'waitingOrder'));
    }

    // Menambah satu item pin custom. Jika pesanan sedang menunggu pembayaran, otomatis
    // dikembalikan ke draft (pelanggan boleh menambah item sebelum lanjut bayar lagi).
    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'pin_size_id' => ['required', Rule::exists('pin_sizes', 'id')->where('is_active', true)->where('availability', PinSize::AVAILABLE)],
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

        $size = PinSize::active()->orderable()->findOrFail($data['pin_size_id']);

        if ($size->tracksStock() && $data['quantity'] > $size->stock) {
            return back()->withErrors(['quantity' => "Stok ukuran ini tersisa {$size->stock} pcs."])->withInput();
        }

        $path = $request->file('design')->store('designs', 'local'); // disk privat, tidak bisa dibuka lewat URL langsung

        try {
            [$order, $reopened] = $this->addItemToDraft($request, [
                'pin_size_id' => $size->id,
                'size_name' => $size->name,
                'unit_price' => $size->price,
                'quantity' => $data['quantity'],
                'design_path' => $path,
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        $size->decrementStock($data['quantity']);

        return redirect()->route('customer.orders.show', $order)->with('success', $reopened
            ? 'Item ditambahkan. Pesanan kembali ke draft, lanjutkan ke pembayaran setelah selesai menambah.'
            : 'Item ditambahkan ke pesanan.');
    }

    // Menambah satu item produk katalog. Harga dan nama mengikuti produk saat ini; tidak ada desain/ukuran.
    public function storeProductItem(Request $request, Product $product)
    {
        abort_unless($product->isOrderable(), 404);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'quantity.required' => 'Isi jumlah pesanan.',
            'quantity.min' => 'Jumlah minimal 1.',
            'quantity.max' => 'Jumlah maksimal 1000 per item.',
        ]);

        if ($product->tracksStock() && $data['quantity'] > $product->stock) {
            return back()->withErrors(['quantity' => "Stok produk ini tersisa {$product->stock} pcs."])->withInput();
        }

        [$order, $reopened] = $this->addItemToDraft($request, [
            'product_id' => $product->id,
            'size_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $data['quantity'],
            'design_path' => null,
            'notes' => $data['notes'] ?? null,
        ]);

        $product->decrementStock($data['quantity']);

        return redirect()->route('customer.orders.show', $order)->with('success', $reopened
            ? 'Item ditambahkan. Pesanan kembali ke draft, lanjutkan ke pembayaran setelah selesai menambah.'
            : 'Item ditambahkan ke pesanan.');
    }

    /**
     * Menambahkan satu item ke draft milik pelanggan (dibuat otomatis jika belum ada).
     * Jika pesanan sedang menunggu pembayaran, dikembalikan ke draft dulu.
     * Dipakai bersama oleh item pin custom maupun item produk katalog.
     *
     * @return array{0: Order, 1: bool} [$order, $reopened]
     */
    private function addItemToDraft(Request $request, array $itemAttributes): array
    {
        $reopened = false;

        $order = DB::transaction(function () use ($request, $itemAttributes, &$reopened) {
            $order = Order::where('user_id', $request->user()->id)
                ->whereIn('status', [Order::STATUS_DRAFT, Order::STATUS_WAITING_PAYMENT])
                ->lockForUpdate()
                ->first();

            if ($order && $order->status === Order::STATUS_WAITING_PAYMENT) {
                $order->transitionTo(Order::STATUS_DRAFT, $request->user(), 'Item ditambahkan sebelum pembayaran');
                $reopened = true;
            }

            $order ??= Order::create(['user_id' => $request->user()->id, 'status' => Order::STATUS_DRAFT]);

            $order->items()->create($itemAttributes);
            $order->recalculateTotal();

            return $order;
        });

        return [$order, $reopened];
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeOwner($request, $order);
        $order->load(['items.product', 'statusLogs.user']);

        $qris = [
            'ready' => Setting::qrisConfigured(),
            'url' => route('qris.image', ['v' => Setting::qrisVersion()]),
            'merchant' => Setting::get('qris_merchant'),
        ];

        return view('customer.orders.show', compact('order', 'qris'));
    }

    public function destroyItem(Request $request, Order $order, OrderItem $item)
    {
        $this->authorizeOwner($request, $order);
        abort_unless($item->order_id === $order->id, 404);

        if (! $order->isEditable()) {
            return back()->withErrors(['order' => 'Pesanan ini sudah tidak bisa diubah.']);
        }

        $this->restoreItemStock($item);

        if ($item->design_path) {
            Storage::disk('local')->delete($item->design_path);
        }
        $item->delete();
        $order->recalculateTotal();

        return back()->with('success', 'Item dihapus dari pesanan.');
    }

    // Draft -> menunggu pembayaran. Mengunci isi pesanan dan menampilkan QRIS.
    public function checkout(Request $request, Order $order)
    {
        $this->authorizeOwner($request, $order);
        $order->loadCount('items');

        if ($order->status === Order::STATUS_DRAFT) {
            if ($order->items_count < 1 || $order->total < 1) {
                return back()->withErrors(['order' => 'Tambahkan minimal satu item sebelum lanjut ke pembayaran.']);
            }
            if (! Setting::qrisConfigured()) {
                return back()->withErrors(['order' => 'Pembayaran QRIS belum tersedia. Silakan hubungi kami lewat WhatsApp.']);
            }
        }

        return $this->move($order, Order::STATUS_DRAFT, Order::STATUS_WAITING_PAYMENT, $request, null, [], 'Pesanan siap dibayar. Scan QRIS dan unggah bukti pembayaran.');
    }

    // Menunggu pembayaran -> draft, supaya item bisa diubah.
    public function reopen(Request $request, Order $order)
    {
        $this->authorizeOwner($request, $order);

        return $this->move($order, Order::STATUS_WAITING_PAYMENT, Order::STATUS_DRAFT, $request, 'Pelanggan mengubah pesanan', [], 'Pesanan kembali ke draft. Anda bisa menambah atau menghapus item.');
    }

    // Menunggu pembayaran -> menunggu verifikasi (bukti bayar terkirim).
    public function uploadProof(Request $request, Order $order)
    {
        $this->authorizeOwner($request, $order);

        if ($order->status !== Order::STATUS_WAITING_PAYMENT) {
            return back()->withErrors(['order' => 'Bukti pembayaran tidak bisa dikirim pada status pesanan ini.']);
        }

        $request->validate([
            'proof' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'proof.required' => 'Unggah foto bukti pembayaran.',
            'proof.image' => 'File harus berupa gambar.',
            'proof.mimes' => 'Format gambar harus JPG, PNG, atau WEBP.',
            'proof.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $oldProof = $order->payment_proof;
        $path = $request->file('proof')->store('payment_proofs', 'local');

        try {
            $order->transitionTo(Order::STATUS_WAITING_VERIFICATION, $request->user(), 'Bukti pembayaran dikirim', [
                'payment_proof' => $path,
                'paid_at' => now(),
                'payment_note' => null,
            ]);
        } catch (InvalidOrderTransition $e) {
            Storage::disk('local')->delete($path);

            return back()->withErrors(['order' => 'Pesanan ini tidak bisa diproses pada statusnya sekarang.']);
        }

        if ($oldProof) {
            Storage::disk('local')->delete($oldProof);
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Bukti pembayaran terkirim. Kami akan memverifikasinya segera.');
    }

    // Pelanggan hanya boleh membatalkan sebelum membayar.
    public function cancel(Request $request, Order $order)
    {
        $this->authorizeOwner($request, $order);

        if ($order->status === Order::STATUS_WAITING_PAYMENT) {
            $order->load('items');
            foreach ($order->items as $item) {
                $this->restoreItemStock($item);
            }
        }

        return $this->move($order, Order::STATUS_WAITING_PAYMENT, Order::STATUS_CANCELLED, $request, 'Dibatalkan oleh pelanggan', [], 'Pesanan dibatalkan.');
    }

    // Mengembalikan stok satu item (pin custom atau produk katalog) ke sumbernya, jika stoknya dilacak.
    private function restoreItemStock(OrderItem $item): void
    {
        if ($item->pin_size_id) {
            PinSize::find($item->pin_size_id)?->restoreStock($item->quantity);
        } elseif ($item->product_id) {
            Product::find($item->product_id)?->restoreStock($item->quantity);
        }
    }

    // Foto desain (khusus item pin custom) hanya bisa dibuka pemilik pesanan atau admin.
    public function design(Request $request, Order $order, OrderItem $item)
    {
        abort_unless($request->user()->isAdmin() || $order->user_id === $request->user()->id, 404);
        abort_unless($item->order_id === $order->id, 404);
        abort_unless($item->design_path, 404);

        return $this->privateImage($item->design_path);
    }

    // Bukti pembayaran: pemilik pesanan atau admin.
    public function proof(Request $request, Order $order)
    {
        abort_unless($request->user()->isAdmin() || $order->user_id === $request->user()->id, 404);
        abort_unless($order->payment_proof, 404);

        return $this->privateImage($order->payment_proof);
    }

    private function privateImage(string $path)
    {
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function move(Order $order, string $from, string $to, Request $request, ?string $note, array $attributes, string $success): RedirectResponse
    {
        if ($order->status !== $from) {
            return back()->withErrors(['order' => 'Pesanan ini tidak bisa diproses pada statusnya sekarang.']);
        }

        try {
            $order->transitionTo($to, $request->user(), $note, $attributes);
        } catch (InvalidOrderTransition $e) {
            return back()->withErrors(['order' => 'Pesanan ini tidak bisa diproses pada statusnya sekarang.']);
        }

        return redirect()->route('customer.orders.show', $order)->with('success', $success);
    }

    // 404 (bukan 403) agar pelanggan lain tidak bisa menebak nomor pesanan yang ada.
    private function authorizeOwner(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 404);
    }
}
