<?php

namespace App\Http\Controllers;

use App\Models\Highschool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HighschoolController extends Controller
{
    public function __construct()
    {
        // require auth for creation/editing actions; index remains public
        $this->middleware('auth')->except(['index']);

        // restrict index/edit/update/destroy to admin/executive where appropriate
        $this->middleware(function ($request, $next) {
            /** @var \App\Models\User $actor */
            $actor = Auth::user();
            if (! $actor || (! $actor->isAdmin() && ! $actor->isExecutive())) {
                abort(403);
            }
            return $next($request);
        })->only(['edit','update','destroy']);
    }
    public function index()
    {
        $highschools = Highschool::orderBy('highschool_name')->paginate(50);
        return view('highschools.index', compact('highschools'));
    }

    public function create()
    {
        return view('highschools.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'highschool_name' => ['required','string','max:255','unique:highschools,highschool_name'],
            'abbreviation' => ['nullable','string','max:50'],
            'type' => ['nullable','in:public,private'],
        ]);

        $name = $request->input('highschool_name');
    $providedAbbr = trim((string) $request->input('abbreviation', ''));
    // compute abbreviation server-side when none provided by the client
    // Also, if the provided abbreviation is a single character that equals
    // the first letter of the name we assume it was an auto-generated
    // fallback (client-side) and compute the full abbreviation here so
    // we don't persist a lone first-letter like "S" for "Sapang Palay...".
    $firstFromName = mb_strtoupper(mb_substr(trim($name), 0, 1, 'UTF-8'), 'UTF-8');
    $providedAbbrNormalized = mb_strtoupper($providedAbbr ?: '', 'UTF-8');
    $isSingleLetterPlaceholder = $providedAbbr !== '' && mb_strlen($providedAbbr, 'UTF-8') === 1 && $providedAbbrNormalized === $firstFromName;

    if ($providedAbbr === '' || $isSingleLetterPlaceholder) {
            // compute from name: first letter of each meaningful word, special-case highschool -> HS
            $stop = ['the','and','of','in','a','an','for','to','on','at','by','with','vs'];
            // split on whitespace
            $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
            $tokens = [];
            foreach ($parts as $w) {
                // normalize: remove non letter/number (unicode aware)
                $lw = mb_strtolower(preg_replace('/[^\p{L}\p{N}]/u', '', $w), 'UTF-8');
                if ($lw === '') continue;
                if (in_array($lw, $stop, true)) continue;
                if ($lw === 'highschool' || $lw === 'high-school' || $lw === 'high_school') {
                    $tokens[] = 'HS';
                    continue;
                }
                $first = mb_substr($lw, 0, 1, 'UTF-8');
                $tokens[] = mb_strtoupper($first, 'UTF-8');
            }
            $abbr = count($tokens) ? implode('', $tokens) : mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
        } else {
            $abbr = $providedAbbr;
        }

        Highschool::create([
            'highschool_name' => $name,
            'abbreviation' => $abbr,
            'type' => $request->input('type'),
        ]);

        // If a user_id was supplied (we came from the highschool-record create flow),
        // redirect back to the record create page for that user so they can select the
        // new highschool immediately.
        $userId = $request->input('user_id');
        if ($userId) {
            return redirect()->route('users.highschool_records.create', $userId)->with('status', 'Highschool added');
        }

        return redirect()->route('highschools.index')->with('status', 'Highschool added');
    }

    public function edit(Highschool $highschool)
    {
        return view('highschools.edit', compact('highschool'));
    }

    public function update(Request $request, Highschool $highschool)
    {
        $request->validate([
            'highschool_name' => ['required','string','max:255','unique:highschools,highschool_name,'.$highschool->highschool_id.',highschool_id'],
            'abbreviation' => ['nullable','string','max:50'],
            'type' => ['nullable','in:public,private'],
        ]);

        $name = $request->input('highschool_name');
    $providedAbbr = trim((string) $request->input('abbreviation', ''));
    $firstFromName = mb_strtoupper(mb_substr(trim($name), 0, 1, 'UTF-8'), 'UTF-8');
    $providedAbbrNormalized = mb_strtoupper($providedAbbr ?: '', 'UTF-8');
    $isSingleLetterPlaceholder = $providedAbbr !== '' && mb_strlen($providedAbbr, 'UTF-8') === 1 && $providedAbbrNormalized === $firstFromName;

    if ($providedAbbr === '' || $isSingleLetterPlaceholder) {
            // compute abbreviation as in store
            $stop = ['the','and','of','in','a','an','for','to','on','at','by','with','vs'];
            $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
            $tokens = [];
            foreach ($parts as $w) {
                $lw = mb_strtolower(preg_replace('/[^\p{L}\p{N}]/u', '', $w), 'UTF-8');
                if ($lw === '') continue;
                if (in_array($lw, $stop, true)) continue;
                if ($lw === 'highschool' || $lw === 'high-school' || $lw === 'high_school') {
                    $tokens[] = 'HS';
                    continue;
                }
                $first = mb_substr($lw, 0, 1, 'UTF-8');
                $tokens[] = mb_strtoupper($first, 'UTF-8');
            }
            $abbr = count($tokens) ? implode('', $tokens) : mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
        } else {
            $abbr = $providedAbbr;
        }

        $highschool->highschool_name = $name;
        $highschool->abbreviation = $abbr;
        $highschool->type = $request->input('type');
        $highschool->save();

        return redirect()->route('highschools.index')->with('status', 'Highschool updated');
    }

    public function destroy(Highschool $highschool)
    {
        $highschool->delete();
        return redirect()->route('highschools.index')->with('status', 'Highschool deleted');
    }
}

