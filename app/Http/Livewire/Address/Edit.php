<?php
namespace App\Http\Livewire\Address;

use Livewire\Component;
use App\Models\Address;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Province;
use App\Models\User;

class Edit extends Component
{
    protected $listeners = ['setAddressField' => 'handleSetAddressField', 'saveAddressFromParent' => 'saveAddress'];

    public $hideActions = false;

    public function handleSetAddressField($field, $value)
    {
        // Only allow specific fields to be set via JS
        if (in_array($field, ['province_id','city_id','barangay_id'])) {
            $this->{$field} = $value ?: null;
            if ($field === 'province_id') {
                $this->updatedProvinceId();
            }
            if ($field === 'city_id') {
                $this->updatedCityId();
            }
        }
    }
    public $userId;
    public $addressId;
    public $house_number;
    public $block_number;
    public $lot_number;
    public $street;
    public $city_id;
    public $barangay_id;
    public $province_id;

    public $cities = [];
    public $barangays = [];
    public $provinces = [];

    protected function loadCities()
    {
        if ($this->province_id) {
            $this->cities = City::where('province_id', $this->province_id)->orderBy('city_name')->get();
        } else {
            $this->cities = collect();
        }
    }

    protected function loadBarangays()
    {
        if ($this->city_id) {
            $this->barangays = Barangay::where('city_id', $this->city_id)->orderBy('barangay_name')->get();
        } else {
            $this->barangays = collect();
        }
    }

    protected function rules()
    {
        return [
            'house_number' => ['nullable','string','max:255'],
            'block_number' => ['nullable','string','max:255'],
            'lot_number' => ['nullable','string','max:255'],
            'street' => ['nullable','string','max:255'],
            'city_id' => ['required','exists:cities,city_id'],
            'barangay_id' => ['required','exists:barangays,barangay_id'],
            'province_id' => ['nullable','exists:provinces,province_id'],
        ];
    }

    public function mount($userId, $hideActions = false)
    {
        $this->userId = $userId;
        $this->hideActions = (bool) $hideActions;
    // start with empty lists; populate based on selections
    $this->cities = collect();
    $this->barangays = collect();
    $this->provinces = Province::orderBy('province_name')->get();

        $user = User::findOrFail($this->userId);
        $profile = $user->userProfile;
        $addr = $profile && $profile->address ? $profile->address : null;
        if ($addr) {
            $this->addressId = $addr->address_id;
            $this->house_number = $addr->house_number;
            $this->block_number = $addr->block_number ?? null;
            $this->lot_number = $addr->lot_number ?? null;
            $this->street = $addr->street;
            $this->city_id = $addr->city_id;
            $this->barangay_id = $addr->barangay_id;
            $this->province_id = $addr->province_id ?? null;
            // If province chosen, load cities in that province
            $this->loadCities();
            // If city chosen, load barangays for that city
            $this->loadBarangays();
        }
    }

    public function updatedCityId()
    {
        // when city changes, reset barangay and load barangays for selected city
        $this->barangay_id = null;
        $this->loadBarangays();
    }

    public function updatedProvinceId()
    {
        // when province changes, reset city and barangay and load cities for selected province
        $this->city_id = null;
        $this->barangay_id = null;
        $this->loadCities();
        $this->barangays = collect();
    }

    // Wrapper to be called from wire:change or from JS bridge safely
    public function changeProvince($provinceId)
    {
        $this->province_id = $provinceId ?: null;
        // reuse existing lifecycle logic
        $this->updatedProvinceId();
    }

    // Wrapper to be called from wire:change or from JS bridge safely
    public function changeCity($cityId)
    {
        $this->city_id = $cityId ?: null;
        $this->updatedCityId();
    }

    protected function filterBarangays()
    {
        if ($this->city_id) {
            $this->barangays = Barangay::where('city_id', $this->city_id)->orderBy('barangay_name')->get();
        } else {
            $this->barangays = Barangay::orderBy('barangay_name')->get();
        }
    }

    public function saveAddress()
    {
        $data = $this->validate();

        $addrData = [
            'house_number' => $this->house_number ?: null,
            'block_number' => $this->block_number ?: null,
            'lot_number' => $this->lot_number ?: null,
            'street' => $this->street ?: null,
            'city_id' => $this->city_id,
            'barangay_id' => $this->barangay_id,
            'province_id' => $this->province_id ?: null,
        ];

        if ($this->addressId) {
            $addr = Address::find($this->addressId);
            if ($addr) {
                $addr->update($addrData);
            }
        } else {
            $addr = Address::create($addrData);
            $this->addressId = $addr->address_id;
        }

        // ensure user profile points to this address
        $user = User::findOrFail($this->userId);
        $profile = $user->userProfile;
        if ($profile) {
            $profile->address_id = $addr->address_id;
            $profile->save();
        }

        session()->flash('status', 'Address saved');
        // Use the same internal dispatch/emit technique as PersonalInformationEdit
        // so behavior is consistent across components in this project.
        if (method_exists($this, 'dispatch')) {
            try { $this->dispatch('addressSaved', $addr->address_id); } catch (\Throwable $e) { /* ignore */ }
        } elseif (method_exists($this, 'emit')) {
            try { $this->emit('addressSaved', $addr->address_id); } catch (\Throwable $e) { /* ignore */ }
        }
    }

    public function render()
    {
        return view('livewire.address.edit');
    }
}
