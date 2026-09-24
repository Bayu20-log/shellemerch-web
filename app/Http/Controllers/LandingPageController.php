<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hero;
use App\Models\Product;
use App\Models\News;
use App\Models\Sponsor;

class LandingPageController extends Controller
{
    public function index()
    {
        $heroes = Hero::latest()->get();
        // Membatasi produk yang tampil di beranda hanya 6 terbaru
        $products = Product::latest()->take(6)->get();
        $news = News::orderBy('published_date', 'desc')->take(3)->get();
        $sponsors = Sponsor::latest()->get();

        return view('welcome', compact('heroes', 'products', 'news', 'sponsors',));
    }

    // Fungsi untuk Halaman Semua Produk (Kini Mendukung Search & Sort)
    public function products(Request $request)
    {
        $query = Product::query();

        // 1. Fitur Pencarian (Berdasarkan nama produk)
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // 2. Fitur Urutkan (Sort by)
        if ($request->filled('sort')) {
            if ($request->sort == 'price_asc') {
                $query->orderBy('price', 'asc'); // Harga terendah ke tertinggi
            } elseif ($request->sort == 'price_desc') {
                $query->orderBy('price', 'desc'); // Harga tertinggi ke terendah
            } else {
                $query->latest(); // Default: Terbaru
            }
        } else {
            $query->latest(); // Default jika tidak ada filter
        }

        $products = $query->get();
        return view('products', compact('products'));
    }

    // Fungsi untuk Halaman About Us (Statis)
    public function about()
    {
        return view('about');
    }

    // Fungsi untuk Halaman Kumpulan Berita (Semua Berita)
    public function news()
    {
        // Mengambil semua berita dari database dan di-paginate (misal 6 per halaman)
        $news = News::orderBy('published_date', 'desc')->paginate(6);
        return view('news', compact('news'));
    }

    // Fungsi untuk Halaman Detail Berita
    public function newsDetail($id)
    {
        // Mencari berita berdasarkan ID, jika tidak ada akan muncul error 404
        $newsItem = News::findOrFail($id);
        return view('news-detail', compact('newsItem'));
    }
    
}