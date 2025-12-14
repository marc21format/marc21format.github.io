<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;

class ProvinceController extends Controller
{
    public function index()
    {
        return view('provinces.index');
    }

    public function create()
    {
        return view('provinces.create');
    }

    public function edit(Province $province)
    {
        return view('provinces.edit', compact('province'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Province::class);
        $data = $request->validate(['province_name' => 'required|string|max:255']);
        $data['province_name'] = mb_convert_case(trim($data['province_name']), MB_CASE_TITLE, 'UTF-8');
        Province::create($data);
        return redirect()->route('provinces.index')->with('status','Province created');
    }

    public function update(Request $request, Province $province)
    {
        $this->authorize('update', $province);
        $data = $request->validate(['province_name' => 'required|string|max:255']);
        $data['province_name'] = mb_convert_case(trim($data['province_name']), MB_CASE_TITLE, 'UTF-8');
        $province->update($data);
        return redirect()->route('provinces.index')->with('status','Province updated');
    }

    public function destroy(Province $province)
    {
        $this->authorize('delete', $province);
        $province->delete();
        return redirect()->route('provinces.index')->with('status','Province deleted');
    }
}
