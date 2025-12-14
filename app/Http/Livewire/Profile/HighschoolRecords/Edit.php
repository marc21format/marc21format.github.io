<?php
namespace App\Http\Livewire\Profile\HighschoolRecords;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\HighschoolRecord;
use App\Models\User;

class Edit extends Component
{
    public $recordId;
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

    public function mount($recordId)
    {
        $this->recordId = $recordId;
        $record = HighschoolRecord::findOrFail($recordId);
        $this->userId = $record->user_id;
        $this->highschool_id = $record->highschool_id;
        // Normalize the stored DB level to one of our button labels so
        // the edit UI shows the correct active button.
        $stored = (string) ($record->level ?? '');
        if (stripos($stored, 'junior') !== false) {
            $this->level = 'Junior Highschool';
        } elseif (stripos($stored, 'senior') !== false) {
            $this->level = 'Senior Higschool';
        } else {
            $this->level = $stored;
        }
        $this->year_start = $record->year_start;
        $this->year_end = $record->year_end;
        $this->highschools = \App\Models\Highschool::orderBy('highschool_name')->get();
    }

    public function update()
    {
        $this->validate();

        // Determine DB enum values and map to the expected label in a robust way.
        $enumValues = $this->getEnumValues('highschool_records', 'level');

        $level = null;
        if (is_string($this->level) && $this->level !== '') {
            // If the stored value exactly matches an enum, use it.
            if (in_array($this->level, $enumValues, true)) {
                $level = $this->level;
            } else {


                // Map the selected button label to the DB enum value. If the
                // DB already contains a matching label, preserve it. Otherwise
                // try to derive the enum by searching for 'junior'/'senior'.
                $lower = strtolower($this->level);
                if (stripos($lower, 'junior') !== false) {
                    // Prefer an enum value that contains 'junior'
                    foreach ($enumValues as $ev) {
                        if (stripos($ev, 'junior') !== false) {
                            $level = $ev; break;
                        }
                    }
                } elseif (stripos($lower, 'senior') !== false) {
                    foreach ($enumValues as $ev) {
                        if (stripos($ev, 'senior') !== false) {
                            $level = $ev; break;
                        }
                    }
                }
                // Fallback: if nothing matched, try a canonical mapping even
                // when enum introspection failed or returned unexpected values.
                if ($level === null) {
                    $lower = strtolower($this->level);
                    if (stripos($lower, 'junior') !== false) {
                        $level = 'Junior Highschool';
                    } elseif (stripos($lower, 'senior') !== false) {
                        $level = 'Senior Higschool';
                    } else {
                        // final fallback: persist the raw value trimmed
                        $level = trim($this->level);
                    }
                }
            }
        }

        $record = HighschoolRecord::findOrFail($this->recordId);
        $record->update([
            'highschool_id' => $this->highschool_id ?: null,
            'level' => $level,
            'year_start' => $this->year_start ?: null,
            'year_end' => $this->year_end ?: null,
        ]);

       if (method_exists($this, 'dispatch')) {
            $this->dispatch('highschoolRecordUpdated');
        }

        // Redirect back to the proper profile page based on role
        $user = User::find($this->userId);
        $roleId = $user->role_id ?? optional($user->role)->role_id ?? null;
        if (in_array((int) $roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $this->userId]);
        }
        return redirect()->route('profile.student.show', ['user' => $this->userId]);
    }

    /**
     * Delete the current record (called from Livewire).
     */
    public function deleteRecord()
    {
        $record = HighschoolRecord::findOrFail($this->recordId);
        $actor = auth()->user();
        if (! $actor->isAdmin() && ! $actor->isExecutive() && $actor->id !== $record->user_id) {
            abort(403);
        }

        $record->delete();

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('highschoolRecordDeleted');
        }
        // Redirect back to the proper profile page based on role
        $user = User::find($this->userId);
        $roleId = $user->role_id ?? optional($user->role)->role_id ?? null;
        if (in_array((int) $roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $this->userId]);
        }
        return redirect()->route('profile.student.show', ['user' => $this->userId]);
    }

    protected ?array $__enumCache = null;

    protected function getEnumValues(string $table, string $column): array
    {
        if ($this->__enumCache !== null) return $this->__enumCache;
        try {
            $row = DB::selectOne("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column]);
            if (! $row) return [];
            $type = $row->Type;
            if (preg_match("/^enum\((.*)\)$/", $type, $m)) {
                $vals = str_getcsv($m[1], ',', "'");
                $vals = array_map(function($v){ return trim($v, "'\""); }, $vals);
                $this->__enumCache = $vals;
                return $vals;
            }
        } catch (\Throwable $e) {
            // ignore
        }
        $this->__enumCache = [];
        return [];
    }
    public function render()
    {
        $user = User::find($this->userId);
        $record = HighschoolRecord::find($this->recordId);
        return view('livewire.profile.highschool-records.edit', [
            'highschools' => $this->highschools,
            'user' => $user,
            'record' => $record,
        ]);
    }
}
