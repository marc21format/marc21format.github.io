<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barangay;
use App\Models\City;

class BarangayController extends Controller
{
    public function index()
    {
        return view('barangays.index');
    }

    public function create()
    {
        $cities = City::orderBy('city_name')->get();
        return view('barangays.create', compact('cities'));
    }

    public function edit(Barangay $barangay)
    {
        $cities = City::orderBy('city_name')->get();
        return view('barangays.edit', compact('barangay','cities'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Barangay::class);
        $data = $request->validate([
            'city_id' => 'required|exists:cities,city_id',
            'barangay_name' => 'required|string|max:255'
        ]);
        $data['barangay_name'] = mb_convert_case(trim($data['barangay_name']), MB_CASE_TITLE, 'UTF-8');
        Barangay::create($data);
        return redirect()->route('barangays.index')->with('status','Barangay created');
    }

    public function update(Request $request, Barangay $barangay)
    {
        $this->authorize('update', $barangay);
        $data = $request->validate([
            'city_id' => 'required|exists:cities,city_id',
            'barangay_name' => 'required|string|max:255'
        ]);
        $data['barangay_name'] = mb_convert_case(trim($data['barangay_name']), MB_CASE_TITLE, 'UTF-8');
        $barangay->update($data);
        return redirect()->route('barangays.index')->with('status','Barangay updated');
    }

    public function destroy(Barangay $barangay)
    {
        $this->authorize('delete', $barangay);
        $barangay->delete();
        return redirect()->route('barangays.index')->with('status','Barangay deleted');
    }
}
