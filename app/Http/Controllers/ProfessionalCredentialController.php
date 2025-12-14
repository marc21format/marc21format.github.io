<?php

namespace App\Http\Controllers;

use App\Models\FieldOfWork;
use App\Models\PrefixTitle;
use App\Models\SuffixTitle;
use App\Models\ProfessionalCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfessionalCredentialController extends Controller
{
    public function indexFields()
    {
        if (! Auth::check()) abort(403);
        $me = Auth::user();
        $role = $me->role ? $me->role->role_title : null;
        if (! in_array($role, ['Admin','Administrator','Executive','Exec'], true)) {
            abort(403);
        }
        return response()->json(FieldOfWork::all());
    }

    public function storeField(Request $request)
    {
        $this->authorize('create', FieldOfWork::class);
        $data = $request->validate(['name' => 'required|string']);
        $f = FieldOfWork::create($data);
        return response()->json($f,201);
    }

    public function indexPrefixes()
    {
        if (! Auth::check()) abort(403);
        $me = Auth::user();
        $role = $me->role ? $me->role->role_title : null;
        if (! in_array($role, ['Admin','Administrator','Executive','Exec'], true)) {
            abort(403);
        }
        return response()->json(PrefixTitle::all());
    }

    public function storePrefix(Request $request)
    {
        $this->authorize('create', PrefixTitle::class);
        $data = $request->validate(['title'=>'required|string','abbreviation'=>'nullable|string|max:10']);
        $p = PrefixTitle::create($data);
        return response()->json($p,201);
    }

    public function indexSuffixes()
    {
        if (! Auth::check()) abort(403);
        $me = Auth::user();
        $role = $me->role ? $me->role->role_title : null;
        if (! in_array($role, ['Admin','Administrator','Executive','Exec'], true)) {
            abort(403);
        }
        return response()->json(SuffixTitle::all());
    }

    public function storeSuffix(Request $request)
    {
        $this->authorize('create', SuffixTitle::class);
        $data = $request->validate(['title'=>'required|string','abbreviation'=>'nullable|string|max:10']);
        $s = SuffixTitle::create($data);
        return response()->json($s,201);
    }

    public function indexCredentials()
    {
        $this->authorize('viewAny', ProfessionalCredential::class);
        return response()->json(ProfessionalCredential::with(['user','fieldOfWork','prefixTitle','suffixTitle'])->paginate(20));
    }

    public function storeCredential(Request $request) 
    {
        $this->authorize('create', ProfessionalCredential::class);
        $data = $request->validate([
            'user_id'=>'required|exists:users,id',
            'fieldofwork_id'=>'nullable|exists:fields_of_work,fieldofwork_id',
            'prefix_id'=>'nullable|exists:prefix_titles,prefix_id',
            'suffix_id'=>'nullable|exists:suffix_titles,suffix_id',
            'issued_on'=>'nullable|string',
            'notes'=>'nullable|string|max:255',
        ]);

        // Normalize issued_on to year-only (database column is YEAR)
        if (! empty($data['issued_on'])) {
            // Accept either YYYY or YYYY-MM-DD (HTML date). Convert to YYYY.
            if (preg_match('/^\d{4}$/', $data['issued_on'])) {
                // good
            } else {
                $ts = strtotime($data['issued_on']);
                if ($ts !== false) {
                    $data['issued_on'] = date('Y', $ts);
                } else {
                    $data['issued_on'] = null;
                }
            }
        } else {
            $data['issued_on'] = null;
        }

        $c = ProfessionalCredential::create($data);
        return response()->json($c,201);
    }

    // Web form: show create Field of Work
    public function createFieldForm()
    {
        if (! Auth::check()) abort(403);
        return view('professional.fields_of_work.create');
    }

    // Web: index fields of work (with prefixes/suffixes)
    public function indexFieldsPage()
    {
        if (! Auth::check()) abort(403);
        $me = Auth::user();
        $role = $me->role ? $me->role->role_title : null;
        if (! in_array($role, ['Admin','Administrator','Executive','Exec'], true)) {
            abort(403);
        }
        $fields = FieldOfWork::with(['professionalCredentials'])->orderBy('name')->paginate(20);
        $prefixes = PrefixTitle::orderBy('title')->get();
        $suffixes = SuffixTitle::orderBy('title')->get();
        return view('professional.fields_of_work.index', compact('fields','prefixes','suffixes'));
    }

    // Web form: store Field of Work
    public function storeFieldForm(Request $request)
    {
    if (! Auth::check()) abort(403);
    $data = $request->validate(['name'=>'required|string']);
    $data['name'] = mb_convert_case(trim($data['name']), MB_CASE_TITLE, 'UTF-8');
        $f = FieldOfWork::create($data);
        if ($request->filled('return_to')) {
            return redirect($request->input('return_to'))->with('status','Field added');
        }
        return redirect()->route('fields_of_work.index')->with('status','Field added');
    }

    // Web: show edit form for a Field of Work
    public function editFieldForm(FieldOfWork $field)
    {
        if (! Auth::check()) abort(403);
        $fields = FieldOfWork::orderBy('name')->get();
        return view('professional.fields_of_work.edit', compact('field'));
    }

    // Web: update Field of Work
    public function updateFieldForm(Request $request, FieldOfWork $field)
    {
        if (! Auth::check()) abort(403);
        $data = $request->validate(['name'=>'required|string']);
        $data['name'] = mb_convert_case(trim($data['name']), MB_CASE_TITLE, 'UTF-8');
        $field->update($data);
        return redirect()->route('fields_of_work.index')->with('status','Field updated');
    }

    // Web: delete Field of Work
    public function destroyFieldForm(FieldOfWork $field)
    {
        if (! Auth::check()) abort(403);
        $field->delete();
        return redirect()->route('fields_of_work.index')->with('status','Field removed');
    }

    // Web form: create prefix
    public function createPrefixForm()
    {
    if (! Auth::check()) abort(403);
    $fields = FieldOfWork::orderBy('name')->get();
        return view('professional.prefixes.create', compact('fields'));
    }

    // Web form: store prefix
    public function storePrefixForm(Request $request)
    {
    if (! Auth::check()) abort(403);
        $data = $request->validate(['title'=>'required|string','abbreviation'=>'nullable|string|max:10','fieldofwork_id'=>'nullable|exists:fields_of_work,fieldofwork_id','return_to'=>'nullable|url']);
        $data['title'] = mb_convert_case(trim($data['title']), MB_CASE_TITLE, 'UTF-8');
        if (empty($data['abbreviation'])) {
            $data['abbreviation'] = $this->generateAbbreviation($data['title']);
        }
        $p = PrefixTitle::create($data);
        if ($request->filled('return_to')) {
            return redirect($request->input('return_to'))->with('status','Prefix added');
        }
        return redirect()->route('prefixes.index')->with('status','Prefix added');
    }

    // Web: list prefixes
    public function indexPrefixesPage()
    {
        if (! Auth::check()) abort(403);
        $me = Auth::user();
        $role = $me->role ? $me->role->role_title : null;
        if (! in_array($role, ['Admin','Administrator','Executive','Exec'], true)) {
            abort(403);
        }
        $prefixes = PrefixTitle::with('professionalCredentials')->orderBy('title')->paginate(20);
        return view('professional.prefixes.index', compact('prefixes'));
    }

    // Web: edit prefix
    public function editPrefixForm(PrefixTitle $prefix)
    {
        if (! Auth::check()) abort(403);
        $fields = FieldOfWork::orderBy('name')->get();
        return view('professional.prefixes.edit', compact('prefix','fields'));
    }

    public function updatePrefixForm(Request $request, PrefixTitle $prefix)
    {
        if (! Auth::check()) abort(403);
        $data = $request->validate(['title'=>'required|string','abbreviation'=>'nullable|string|max:10','fieldofwork_id'=>'nullable|exists:fields_of_work,fieldofwork_id']);
        $data['title'] = mb_convert_case(trim($data['title']), MB_CASE_TITLE, 'UTF-8');
        $prefix->update($data);
        return redirect()->route('prefixes.index')->with('status','Prefix updated');
    }

    public function destroyPrefixForm(PrefixTitle $prefix)
    {
        if (! Auth::check()) abort(403);
        $prefix->delete();
        return redirect()->route('prefixes.index')->with('status','Prefix removed');
    }

    // Web form: create suffix
    public function createSuffixForm()
    {
        if (! Auth::check()) abort(403);
    $fields = FieldOfWork::orderBy('name')->get();
        return view('professional.suffixes.create', compact('fields'));
    }

    // Web form: store suffix
    public function storeSuffixForm(Request $request)
    {
        if (! Auth::check()) abort(403);
        $data = $request->validate(['title'=>'required|string','abbreviation'=>'nullable|string|max:10','fieldofwork_id'=>'nullable|exists:fields_of_work,fieldofwork_id','return_to'=>'nullable|url']);
        $data['title'] = mb_convert_case(trim($data['title']), MB_CASE_TITLE, 'UTF-8');
        if (empty($data['abbreviation'])) {
            $data['abbreviation'] = $this->generateAbbreviation($data['title']);
        }
        $s = SuffixTitle::create($data);
        if ($request->filled('return_to')) {
            return redirect($request->input('return_to'))->with('status','Suffix added');
        }
        return redirect()->route('suffixes.index')->with('status','Suffix added');
    }

    // Web: list suffixes
    public function indexSuffixesPage()
    {
        if (! Auth::check()) abort(403);
        $me = Auth::user();
        $role = $me->role ? $me->role->role_title : null;
        if (! in_array($role, ['Admin','Administrator','Executive','Exec'], true)) {
            abort(403);
        }
        $suffixes = SuffixTitle::with('professionalCredentials')->orderBy('title')->paginate(20);
        return view('professional.suffixes.index', compact('suffixes'));
    }

    // Web: edit suffix
    public function editSuffixForm(SuffixTitle $suffix)
    {
        if (! Auth::check()) abort(403);
        $fields = FieldOfWork::orderBy('name')->get();
        return view('professional.suffixes.edit', compact('suffix','fields'));
    }

    public function updateSuffixForm(Request $request, SuffixTitle $suffix)
    {
        if (! Auth::check()) abort(403);
        $data = $request->validate(['title'=>'required|string','abbreviation'=>'nullable|string|max:10','fieldofwork_id'=>'nullable|exists:fields_of_work,fieldofwork_id']);
        $data['title'] = mb_convert_case(trim($data['title']), MB_CASE_TITLE, 'UTF-8');
        $suffix->update($data);
        return redirect()->route('suffixes.index')->with('status','Suffix updated');
    }

    public function destroySuffixForm(SuffixTitle $suffix)
    {
        if (! Auth::check()) abort(403);
        $suffix->delete();
        return redirect()->route('suffixes.index')->with('status','Suffix removed');
    }

    // Web: show create form for a user's professional credential
    public function createProfessionalCredential(\App\Models\User $user)
    {
        if (! Auth::check()) abort(403);
        /** @var \App\Models\User $me */
        $me = Auth::user();
        if (! ($me->id === $user->id || $me->isAdmin() || $me->isExecutive())) {
            abort(403);
        }
    $fields = FieldOfWork::orderBy('name')->get();
    $prefixes = PrefixTitle::orderBy('title')->get();
    $suffixes = SuffixTitle::orderBy('title')->get();
    return view('profile.volunteer.professional_credentials.create', compact('user','fields','prefixes','suffixes'));
    }

    // Web: store professional credential from HTML form
    public function storeProfessionalCredential(Request $request, \App\Models\User $user)
    {
        if (! Auth::check()) abort(403);
        /** @var \App\Models\User $me */
        $me = Auth::user();
        if (! ($me->id === $user->id || $me->isAdmin() || $me->isExecutive())) {
            abort(403);
        }
            $data = $request->validate([
                'fieldofwork_id'=>'nullable|exists:fields_of_work,fieldofwork_id',
                'prefix_id'=>'nullable|exists:prefix_titles,prefix_id',
                'suffix_id'=>'nullable|exists:suffix_titles,suffix_id',
                'issued_on'=>'nullable|string',
                'notes'=>'nullable|string|max:255',
            ]);

            // Normalize issued_on to year-only (database column is YEAR)
            if (! empty($data['issued_on'])) {
                if (preg_match('/^\d{4}$/', $data['issued_on'])) {
                    // already a year
                } else {
                    $ts = strtotime($data['issued_on']);
                    if ($ts !== false) {
                        $data['issued_on'] = date('Y', $ts);
                    } else {
                        $data['issued_on'] = null;
                    }
                }
            } else {
                $data['issued_on'] = null;
            }
        $data['user_id'] = $user->id;
        $pc = ProfessionalCredential::create($data);
        return redirect()->route('users.profile.show', $user->id)->with('status','Professional credential added');
    }

    // Web: list professional credentials for admin overview
    public function indexProfessionalCredentials()
    {
        if (! Auth::check()) abort(403);
        $this->authorize('viewAny', ProfessionalCredential::class);
        $creds = ProfessionalCredential::with(['user','fieldOfWork','prefix','suffix'])->orderBy('issued_on','desc')->paginate(30);
        return view('professional.credentials.index', compact('creds'));
    }

    // Web: edit form
    public function editProfessionalCredential(\App\Models\User $user, ProfessionalCredential $credential)
    {
        if (! Auth::check()) abort(403);
        /** @var \App\Models\User $me */
        $me = Auth::user();
        if (! ($me->id === $user->id || $me->isAdmin() || $me->isExecutive())) {
            abort(403);
        }
        if ($credential->user_id != $user->id && ! ($me->isAdmin() || $me->isExecutive())) {
            abort(403);
        }
    $fields = FieldOfWork::orderBy('name')->get();
    $prefixes = PrefixTitle::orderBy('title')->get();
    $suffixes = SuffixTitle::orderBy('title')->get();
    return view('profile.volunteer.professional_credentials.edit', compact('user','credential','fields','prefixes','suffixes'));
    }

    // Web: update credential
    public function updateProfessionalCredential(Request $request, \App\Models\User $user, ProfessionalCredential $credential)
    {
        if (! Auth::check()) abort(403);
        /** @var \App\Models\User $me */
        $me = Auth::user();
        if (! ($me->id === $user->id || $me->isAdmin() || $me->isExecutive())) {
            abort(403);
        }
        if ($credential->user_id != $user->id && ! ($me->isAdmin() || $me->isExecutive())) {
            abort(403);
        }
            $data = $request->validate([
                'fieldofwork_id'=>'nullable|exists:fields_of_work,fieldofwork_id',
                'prefix_id'=>'nullable|exists:prefix_titles,prefix_id',
                'suffix_id'=>'nullable|exists:suffix_titles,suffix_id',
                'issued_on'=>'nullable|string',
                'notes'=>'nullable|string|max:255',
            ]);

            // Normalize issued_on to year-only (database column is YEAR)
            if (! empty($data['issued_on'])) {
                if (preg_match('/^\d{4}$/', $data['issued_on'])) {
                    // already a year
                } else {
                    $ts = strtotime($data['issued_on']);
                    if ($ts !== false) {
                        $data['issued_on'] = date('Y', $ts);
                    } else {
                        $data['issued_on'] = null;
                    }
                }
            } else {
                $data['issued_on'] = null;
            }
        $credential->update($data);
        return redirect()->route('users.profile.show', $user->id)->with('status','Professional credential updated');
    }

    // Web: delete
    public function destroyProfessionalCredential(\App\Models\User $user, ProfessionalCredential $credential)
    {
        if (! Auth::check()) abort(403);
        /** @var \App\Models\User $me */
        $me = Auth::user();
        if (! ($me->id === $user->id || $me->isAdmin() || $me->isExecutive())) {
            abort(403);
        }
        if ($credential->user_id != $user->id && ! ($me->isAdmin() || $me->isExecutive())) {
            abort(403);
        }
        $credential->delete();
        return redirect()->route('users.profile.show', $user->id)->with('status','Professional credential removed');
    }

    /**
     * Generate a simple abbreviation from a title string.
     * Rules: take first letter of each word (A-Z), up to 3 letters; if single word, take first 2 letters.
     */
    private function generateAbbreviation(string $text): string
    {
        $tokens = preg_split('/[^\p{L}0-9]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $letters = [];
        foreach ($tokens as $t) {
            $first = mb_substr($t, 0, 1, 'UTF-8');
            if ($first !== '') $letters[] = mb_strtoupper($first, 'UTF-8');
            if (count($letters) >= 3) break;
        }
        if (count($letters) === 0) return strtoupper(mb_substr($text, 0, 2, 'UTF-8'));
        if (count($letters) === 1) {
            return mb_strtoupper(mb_substr($tokens[0], 0, 2, 'UTF-8'), 'UTF-8');
        }
        return implode('', $letters);
    }
}
