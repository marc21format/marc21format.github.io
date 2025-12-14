<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;

class CityController extends Controller
{
    public function index()
    {
        return view('cities.index');
    }

    public function create()
    {
        return view('cities.create');
    }

    public function edit(City $city)
    {
        return view('cities.edit', compact('city'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', City::class);
        $data = $request->validate(['city_name' => 'required|string|max:255']);
        $data['city_name'] = mb_convert_case(trim($data['city_name']), MB_CASE_TITLE, 'UTF-8');
        City::create($data);
        return redirect()->route('cities.index')->with('status','City created');
    }

    public function update(Request $request, City $city)
    {
        $this->authorize('update', $city);
        $data = $request->validate(['city_name' => 'required|string|max:255']);
        $data['city_name'] = mb_convert_case(trim($data['city_name']), MB_CASE_TITLE, 'UTF-8');
        $city->update($data);
        return redirect()->route('cities.index')->with('status','City updated');
    }

    public function destroy(City $city)
    {
        $this->authorize('delete', $city);
        $city->delete();
        return redirect()->route('cities.index')->with('status','City deleted');
    }
}
