<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPackage;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $customerCount = Order::whereMonth('entry_date', now()->month)
            ->whereYear('entry_date', now()->year)
            ->distinct('customer_id')
            ->count('customer_id');

        $orderCount = Order::whereMonth('entry_date', now()->month)
            ->whereYear('entry_date', now()->year)
            ->count();

        $serviceTypes = OrderPackage::select('type')->distinct()->pluck('type');

        $packages = OrderPackage::with(['discounts' => function ($query) {
            $query->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now());
        }])->get();

        $groupedPackages = $packages->map(function ($package) {
            $package->active_discount = $package->discounts->first();
            return $package;
        })->groupBy('type');

        return view('home', [
            'customerCount' => $customerCount,
            'orderCount' => $orderCount,
            'serviceTypes' => $serviceTypes,
            'groupedPackages' => $groupedPackages,
            'whatsapp' => '6285158803862',
            'mapsUrl' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.7003495675003!2d114.75154297584199!3d-3.422975241696544!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de683ef16a35321%3A0x6b749017e32f77f0!2sSinar%20Laundry!5e0!3m2!1sen!2sid!4v1753339617145!5m2!1sen!2sid',
        ]);
    }
}
