<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Barangay;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function indexCities()
    {
        $this->authorize('viewAny', City::class);
        return response()->json(City::all());
    }

    public function storeCity(Request $request)
    {
        $this->authorize('create', City::class);
        $data = $request->validate(['city_name'=>'required|string']);
        $c = City::create($data);
        return response()->json($c,201);
    }

    public function indexBarangays()
    {
        $this->authorize('viewAny', Barangay::class);
        return response()->json(Barangay::with('city')->get());
    }

    public function storeBarangay(Request $request)
    {
        $this->authorize('create', Barangay::class);
        $data = $request->validate(['city_id'=>'required|exists:cities,city_id','barangay_name'=>'required|string']);
        $b = Barangay::create($data);
        return response()->json($b,201);
    }

    public function indexAddresses()
    {
        $this->authorize('viewAny', Address::class);
        return response()->json(Address::with('city','barangay')->paginate(20));
    }

    public function storeAddress(Request $request)
    {
        $this->authorize('create', Address::class);
        $data = $request->validate([
            'house_number'=>'nullable|string',
            'street'=>'nullable|string',
            'barangay_id'=>'nullable|exists:barangays,barangay_id',
            'city_id'=>'nullable|exists:cities,city_id',
            'province_id'=>'nullable|exists:provinces,province_id',
        ]);

        $a = Address::create($data);
        return response()->json($a,201);
    }

    // Web: show create address form
    public function createAddressForm()
    {
        if (! \Illuminate\Support\Facades\Auth::check()) {
            abort(403);
        }
        $cities = City::orderBy('city_name')->get();
        $barangays = Barangay::orderBy('barangay_name')->get();
    $provinces = \App\Models\Province::orderBy('province_name')->get();
    return view('addresses.create', compact('cities','barangays','provinces'));
    }

    // Web: store address from HTML form
    public function storeAddressForm(Request $request)
    {
        if (! \Illuminate\Support\Facades\Auth::check()) {
            abort(403);
        }
        $data = $request->validate([
            'house_number'=>'nullable|string',
            'street'=>'nullable|string',
            'barangay_id'=>'nullable|exists:barangays,barangay_id',
            'city_id'=>'nullable|exists:cities,city_id',
            'province_id'=>'nullable|exists:provinces,province_id',
            'return_to'=>'nullable|url'
        ]);
        // Title case some string fields
        if (! empty($data['street'])) {
            $data['street'] = mb_convert_case(trim($data['street']), MB_CASE_TITLE, 'UTF-8');
        }
        // province_id is an id; no casing needed
        $a = Address::create($data);
        if (! empty($data['return_to'])) {
            return redirect($data['return_to'])->with('status','Address added');
        }
        return redirect()->route('addresses.create')->with('status','Address added');
    }

    // Web: show create city form
    public function createCityForm()
    {
        if (! \Illuminate\Support\Facades\Auth::check()) {
            abort(403);
        }
        return view('cities.create');
    }

    // Web: store city from HTML form
    public function storeCityForm(Request $request)
    {
        if (! \Illuminate\Support\Facades\Auth::check()) {
            abort(403);
        }
        $data = $request->validate(['city_name'=>'required|string','return_to'=>'nullable|url']);
        $data['city_name'] = mb_convert_case(trim($data['city_name']), MB_CASE_TITLE, 'UTF-8');
        $c = City::create($data);
        if ($request->filled('return_to')) {
            return redirect($request->input('return_to'))->with('status','City added');
        }
        return redirect()->route('cities.create')->with('status','City added');
    }

    // Web: show create barangay form
    public function createBarangayForm()
    {
        if (! \Illuminate\Support\Facades\Auth::check()) {
            abort(403);
        }
        $cities = City::orderBy('city_name')->get();
        return view('barangays.create', compact('cities'));
    }

    // Web: store barangay from HTML form
    public function storeBarangayForm(Request $request)
    {
        if (! \Illuminate\Support\Facades\Auth::check()) {
            abort(403);
        }
        $data = $request->validate(['city_id'=>'required|exists:cities,city_id','barangay_name'=>'required|string','return_to'=>'nullable|url']);
        $data['barangay_name'] = mb_convert_case(trim($data['barangay_name']), MB_CASE_TITLE, 'UTF-8');
        $b = Barangay::create($data);
        if ($request->filled('return_to')) {
            return redirect($request->input('return_to'))->with('status','Barangay added');
        }
        return redirect()->route('barangays.create')->with('status','Barangay added');
    }
}
