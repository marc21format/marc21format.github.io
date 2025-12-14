<?php
namespace App\Http\Livewire\Profile\HighschoolRecords;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\HighschoolRecord;
use App\Models\User;

class Create extends Component
{
    public $userId;
    public $highschool_id;
    public $level;
    public $year_start;
    public $year_end;
    public $highschools = [];

    protected $rules = [
        'highschool_id' => 'nullable|integer|exists:highschools,highschool_id',
        'level' => 'nullable|string|max:255',
        'year_start' => 'nullable|integer',
        'year_end' => 'nullable|integer',
    ];

    public function mount($userId = null)
    {
        $this->userId = $userId ?? Auth::id();
        $this->highschools = \App\Models\Highschool::orderBy('highschool_name')->get();
    }

    public function store()
    {
        $this->validate();

        // Determine DB enum values and map to the expected label in a robust way.
        $enumValues = $this->getEnumValues('highschool_records', 'level');

        $level = null;
        if (is_string($this->level) && $this->level !== '') {
            if (in_array($this->level, $enumValues, true)) {
                $level = $this->level;
            } else {
                foreach ($enumValues as $ev) {
                    if (stripos($ev, $this->level) !== false || stripos($this->level, $ev) !== false) {
                        $level = $ev;
                        break;
                    }
                }
                if ($level === null) {
                    $short = strtolower($this->level);
                    $mappingCandidates = ['junior' => 'junior', 'senior' => 'senior'];
                    foreach ($enumValues as $ev) {
                        foreach ($mappingCandidates as $key => $needle) {
                            if ($short === $key || stripos($ev, $needle) !== false) {
                                $level = $ev;
                                break 2;
                            }
                        }
                    }
                }
                if ($level === null) {
                    // If enum introspection failed or no match found, fall back
                    // to canonical labels so we don't persist a stray character.
                    $lower = strtolower($this->level);
                    if (stripos($lower, 'junior') !== false) {
                        $level = 'Junior Highschool';
                    } elseif (stripos($lower, 'senior') !== false) {
                        $level = 'Senior Higschool';
                    } else {
                        $level = trim($this->level);
                    }
                }
            }
        }

        $record = HighschoolRecord::create([
            'user_id' => $this->userId,
            'highschool_id' => $this->highschool_id ?: null,
            'level' => $level,
            'year_start' => $this->year_start ?: null,
            'year_end' => $this->year_end ?: null,
        ]);

        // After creating, redirect back to the appropriate profile page
        $user = User::find($this->userId);
        $roleId = $user->role_id ?? optional($user->role)->role_id ?? null;

        if (in_array((int) $roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $this->userId]);
        }

        return redirect()->route('profile.student.show', ['user' => $this->userId]);
        // (Old behavior: dispatch an event) -- kept intentionally out because
        // we now redirect after creation to keep flow consistent with edit.
    }

        /**
         * Get enum values for a table column (cached per-request).
         */
        protected ?array $__enumCache = null;

        protected function getEnumValues(string $table, string $column): array
        {
            if ($this->__enumCache !== null) return $this->__enumCache;
            try {
                $row = DB::selectOne("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column]);
                if (! $row) return [];
                $type = $row->Type;
                // type looks like: enum('a','b','c')
                if (preg_match("/^enum\((.*)\)$/", $type, $m)) {
                    $vals = str_getcsv($m[1], ',', "'");
                    // normalize
                    $vals = array_map(function($v){ return trim($v, "'\""); }, $vals);
                    $this->__enumCache = $vals;
                    return $vals;
                }
            } catch (\Throwable $e) {
                // ignore and return empty
            }
            $this->__enumCache = [];
            return [];
        }
    public function render()
    {
        $user = User::find($this->userId);
        return view('livewire.profile.highschool-records.create', [
            'highschools' => $this->highschools,
            'user' => $user,
        ]);
    }
}
