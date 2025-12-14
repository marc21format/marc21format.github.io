<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Show the advanced search page.
     */
    public function advanced()
    {
        // Collect helper lists where possible; fall back to empty arrays if models missing
        $lists = [];
        try {
            $lists['cities'] = class_exists('\App\Models\City') ? \App\Models\City::all() : collect();
        } catch (\Throwable $e) { $lists['cities'] = collect(); }
        try {
            $lists['barangays'] = class_exists('\App\Models\Barangay') ? \App\Models\Barangay::all() : collect();
        } catch (\Throwable $e) { $lists['barangays'] = collect(); }
        try { $lists['committees'] = class_exists('\App\Models\Committee') ? \App\Models\Committee::all() : collect(); } catch (\Throwable $e) { $lists['committees'] = collect(); }
    try { $lists['committee_positions'] = class_exists('\App\Models\CommitteePosition') ? \App\Models\CommitteePosition::all() : collect(); } catch (\Throwable $e) { $lists['committee_positions'] = collect(); }
        try { $lists['positions'] = class_exists('\App\Models\Position') ? \App\Models\Position::all() : collect(); } catch (\Throwable $e) { $lists['positions'] = collect(); }
    try { $lists['volunteer_subjects'] = class_exists('\App\Models\VolunteerSubject') ? \App\Models\VolunteerSubject::all() : collect(); } catch (\Throwable $e) { $lists['volunteer_subjects'] = collect(); }
    try { $lists['highschool_subjects'] = class_exists('\App\Models\HighschoolSubject') ? \App\Models\HighschoolSubject::all() : collect(); } catch (\Throwable $e) { $lists['highschool_subjects'] = collect(); }
        try { $lists['degreeLevels'] = class_exists('\App\Models\DegreeLevel') ? \App\Models\DegreeLevel::all() : collect(); } catch (\Throwable $e) { $lists['degreeLevels'] = collect(); }
        try { $lists['degreeFields'] = class_exists('\App\Models\DegreeField') ? \App\Models\DegreeField::all() : collect(); } catch (\Throwable $e) { $lists['degreeFields'] = collect(); }
        try { $lists['degreeTypes'] = class_exists('\App\Models\DegreeType') ? \App\Models\DegreeType::all() : collect(); } catch (\Throwable $e) { $lists['degreeTypes'] = collect(); }
        try { $lists['universities'] = class_exists('\App\Models\University') ? \App\Models\University::all() : collect(); } catch (\Throwable $e) { $lists['universities'] = collect(); }
        try { $lists['highschools'] = class_exists('\App\Models\Highschool') ? \App\Models\Highschool::all() : collect(); } catch (\Throwable $e) { $lists['highschools'] = collect(); }
    try { $lists['degreePrograms'] = class_exists('\App\Models\DegreeProgram') ? \App\Models\DegreeProgram::all() : collect(); } catch (\Throwable $e) { $lists['degreePrograms'] = collect(); }
    try { $lists['fieldsOfWork'] = class_exists('\App\Models\FieldOfWork') ? \App\Models\FieldOfWork::all() : collect(); } catch (\Throwable $e) { $lists['fieldsOfWork'] = collect(); }
    try { $lists['roles'] = class_exists('\App\Models\UserRole') ? \App\Models\UserRole::all() : collect(); } catch (\Throwable $e) { $lists['roles'] = collect(); }

        return view('search.advanced', $lists);
    }

    /**
     * Handle advanced search form submissions and display results.
     */
    public function results(Request $request)
    {
        $q = $request->input('q');
        $payload = null;

        // Base user query
        $usersQuery = class_exists(\App\Models\User::class) ? \App\Models\User::query()->with('userProfile') : null;

        // If a JSON query payload is provided (from the builder), parse and apply it
        $queryJson = $request->input('query_json');

        if ($usersQuery) {
            if (!empty($queryJson)) {
                $payload = json_decode($queryJson, true);
                if (json_last_error() === JSON_ERROR_NONE && !empty($payload['groups'])) {
                    $groups = $payload['groups'];
                    $groupMatch = strtolower($payload['group_match'] ?? 'all');

                    // Helper that builds a closure for a group. It respects the group's operator:
                    // - 'and' => combine conditions with AND
                    // - 'or'  => combine conditions with OR
                    // - 'not' => negate the group's combined conditions
                    $groupClosureFactory = function(array $groupConditions, string $operator = 'and') {
                        return function($q) use ($groupConditions, $operator) {
                            if ($operator === 'or') {
                                // one of the conditions must match -> OR across condition closures
                                $first = true;
                                foreach ($groupConditions as $cond) {
                                    $cl = function($sub) use ($cond) { $this->applyConditionToQuery($sub, $cond); };
                                    if ($first) {
                                        $q->where($cl);
                                        $first = false;
                                    } else {
                                        $q->orWhere($cl);
                                    }
                                }
                                return;
                            }

                            if ($operator === 'not') {
                                // negate the combined (AND) conditions
                                $q->whereNot(function($sub) use ($groupConditions) {
                                    foreach ($groupConditions as $cond) {
                                        $this->applyConditionToQuery($sub, $cond);
                                    }
                                });
                                return;
                            }

                            // default: AND across conditions
                            foreach ($groupConditions as $cond) {
                                $this->applyConditionToQuery($q, $cond);
                            }
                        };
                    };

                    if ($groupMatch === 'all') {
                        // All groups must match (AND across groups)
                        $usersQuery->where(function($main) use ($groups, $groupClosureFactory) {
                            foreach ($groups as $group) {
                                $conditions = $group['conditions'] ?? [];
                                if (empty($conditions)) continue;
                                $op = strtolower($group['operator'] ?? 'and');
                                $main->where($groupClosureFactory($conditions, $op));
                            }
                        });
                    } elseif ($groupMatch === 'any') {
                        // Any group may match (OR across groups)
                        $usersQuery->where(function($main) use ($groups, $groupClosureFactory) {
                            $first = true;
                            foreach ($groups as $group) {
                                $conditions = $group['conditions'] ?? [];
                                if (empty($conditions)) continue;
                                $op = strtolower($group['operator'] ?? 'and');
                                if ($first) {
                                    $main->where($groupClosureFactory($conditions, $op));
                                    $first = false;
                                } else {
                                    $main->orWhere($groupClosureFactory($conditions, $op));
                                }
                            }
                        });
                    } elseif ($groupMatch === 'none') {
                        // Exclude any user matching any group -> whereNot( OR of groups )
                        $usersQuery->whereNot(function($main) use ($groups, $groupClosureFactory) {
                            $first = true;
                            foreach ($groups as $group) {
                                $conditions = $group['conditions'] ?? [];
                                if (empty($conditions)) continue;
                                $op = strtolower($group['operator'] ?? 'and');
                                if ($first) {
                                    $main->where($groupClosureFactory($conditions, $op));
                                    $first = false;
                                } else {
                                    $main->orWhere($groupClosureFactory($conditions, $op));
                                }
                            }
                        });
                    } else {
                        // Fallback: per-group operator handling (compat)
                        $usersQuery->where(function($main) use ($groups) {
                            foreach ($groups as $idx => $group) {
                                $operator = strtolower($group['operator'] ?? 'and');
                                $groupConditions = $group['conditions'] ?? [];
                                if (empty($groupConditions)) continue;

                                $cl = function($q) use ($groupConditions) {
                                    foreach ($groupConditions as $cond) {
                                        $this->applyConditionToQuery($q, $cond);
                                    }
                                };

                                if ($idx === 0) {
                                    $main->where($cl);
                                } else {
                                    if ($operator === 'and') {
                                        $main->where($cl);
                                    } elseif ($operator === 'or') {
                                        $main->orWhere($cl);
                                    } elseif ($operator === 'not') {
                                        $main->whereNot($cl);
                                    } else {
                                        $main->where($cl);
                                    }
                                }
                            }
                        });
                    }
                }
            } else {
                // fallback: simple global q search
                if (!empty($q)) {
                    $usersQuery->where(function($qq) use ($q) {
                        $qq->where('email', 'like', "%{$q}%")
                           ->orWhere('name', 'like', "%{$q}%")
                           ->orWhereHas('userProfile', function($q2) use ($q){
                              $q2->where('f_name','like','%'.$q.'%')
                                 ->orWhere('m_name','like','%'.$q.'%')
                                 ->orWhere('s_name','like','%'.$q.'%');
                           });
                    });
                }
            }

            $users = $usersQuery->paginate(20)->withQueryString();
            // Optional debug: if request contains debug_sql=1, log the built SQL and payload
            try {
                if ($request->input('debug_sql')) {
                    Log::debug('AdvancedSearch payload', ['payload' => $payload, 'raw' => $queryJson]);
                    try { Log::debug('AdvancedSearch SQL', ['sql' => $usersQuery->toSql(), 'bindings' => $usersQuery->getBindings()]); } catch (\Throwable $_) { /* ignore */ }
                }
            } catch (\Throwable $_) {}
        } else {
            $users = collect();
        }

        // reuse the advanced view and pass results (and same helper lists as advanced())
        $viewData = [];
        try { $viewData['cities'] = class_exists('\App\Models\City') ? \App\Models\City::all() : collect(); } catch (\Throwable $e) { $viewData['cities'] = collect(); }
        try { $viewData['barangays'] = class_exists('\App\Models\Barangay') ? \App\Models\Barangay::all() : collect(); } catch (\Throwable $e) { $viewData['barangays'] = collect(); }
    try { $viewData['committees'] = class_exists('\App\Models\Committee') ? \App\Models\Committee::all() : collect(); } catch (\Throwable $e) { $viewData['committees'] = collect(); }
    try { $viewData['committee_positions'] = class_exists('\App\Models\CommitteePosition') ? \App\Models\CommitteePosition::all() : collect(); } catch (\Throwable $e) { $viewData['committee_positions'] = collect(); }
    try { $viewData['volunteer_subjects'] = class_exists('\App\Models\VolunteerSubject') ? \App\Models\VolunteerSubject::all() : collect(); } catch (\Throwable $e) { $viewData['volunteer_subjects'] = collect(); }
    try { $viewData['highschool_subjects'] = class_exists('\App\Models\HighschoolSubject') ? \App\Models\HighschoolSubject::all() : collect(); } catch (\Throwable $e) { $viewData['highschool_subjects'] = collect(); }
    try { $viewData['degreePrograms'] = class_exists('\App\Models\DegreeProgram') ? \App\Models\DegreeProgram::all() : collect(); } catch (\Throwable $e) { $viewData['degreePrograms'] = collect(); }
    try { $viewData['fieldsOfWork'] = class_exists('\App\Models\FieldOfWork') ? \App\Models\FieldOfWork::all() : collect(); } catch (\Throwable $e) { $viewData['fieldsOfWork'] = collect(); }
    try { $viewData['roles'] = class_exists('\App\Models\UserRole') ? \App\Models\UserRole::all() : collect(); } catch (\Throwable $e) { $viewData['roles'] = collect(); }

        $viewData['users'] = $users;
        $viewData['q'] = $q;
    // echo back the raw JSON and decoded payload so the view can rehydrate the builder
    $viewData['query_json'] = $queryJson;
    $viewData['initial_query'] = $payload ?? null;

        return view('search.advanced', $viewData);
    }

    /**
     * Apply a single condition into the query builder.
     * This is intentionally defensive: it tries user columns first, then userProfile relations,
     * and uses whereHas for related collections where sensible.
     */
    private function applyConditionToQuery($qb, array $cond)
    {
        $field = $cond['field'] ?? null;
        $value = $cond['value'] ?? null;
        $match = $cond['match'] ?? 'all';
        if (!$field || $value === null || $value === '') return;

        // Terms for text matching
        $terms = preg_split('/\s+/', trim($value));

        // Helper to apply like checks on a builder for a given column
        $applyLike = function($builder, $column) use ($terms, $match) {
            if ($match === 'all') {
                foreach ($terms as $t) {
                    $builder->where($column, 'like', '%'.$t.'%');
                }
            } elseif ($match === 'any') {
                $builder->where(function($q) use ($terms, $column) {
                    foreach ($terms as $t) { $q->orWhere($column, 'like', '%'.$t.'%'); }
                });
            } elseif ($match === 'none') {
                $builder->whereNot(function($q) use ($terms, $column) {
                    foreach ($terms as $t) { $q->orWhere($column, 'like', '%'.$t.'%'); }
                });
            }
        };

        // Simple users table fields
        if (in_array($field, ['name','email','username'])) {
            // map requested 'username' to the users.name column if username not present
            $col = ($field === 'username') ? 'name' : $field;
            $applyLike($qb, $col);
            return;
        }

        // Fields on userProfile
        if (in_array($field, ['f_name','m_name','s_name','volunteer_number'])) {
            $qb->whereHas('userProfile', function($pq) use ($applyLike, $field) {
                $applyLike($pq, $field);
            });
            return;
        }

        // Equality-style fields and relations (defensive)
        if (in_array($field, ['status_id','batch_id','committee_id','position_id','subject_id','city_id','barangay_id','university_id','highschool_id','degree_level','degree_field','degree_type','prefix_id','suffix_id','field_of_work_id'])) {
            $val = $value;
            try {
                // committee
                if ($field === 'committee_id') {
                    if (method_exists(\App\Models\User::class, 'committees')) {
                        $qb->whereHas('committees', function($c) use ($val){ $c->where('committee_id', $val); });
                        return;
                    }
                    if (method_exists(\App\Models\User::class, 'committeeMemberships')) {
                        $qb->whereHas('committeeMemberships', function($c) use ($val){ $c->where('committee_id', $val); });
                        return;
                    }
                }
                // position
                if ($field === 'position_id') {
                    if (method_exists(\App\Models\User::class, 'committeeMemberships')) {
                        $qb->whereHas('committeeMemberships', function($c) use ($val){ $c->where('position_id', $val); });
                        return;
                    }
                    if (method_exists(\App\Models\User::class, 'positions')) {
                        $qb->whereHas('positions', function($c) use ($val){ $c->where('position_id', $val); });
                        return;
                    }
                }
                // subject (volunteer subject)
                if ($field === 'volunteer_subject_id' || $field === 'subject_id') {
                    if (method_exists(\App\Models\User::class, 'volunteerSubjects')) {
                        $qb->whereHas('volunteerSubjects', function($s) use ($val){ $s->where('id', $val); });
                        return;
                    }
                    if (method_exists(\App\Models\User::class, 'subjectTeachers')) {
                        $qb->whereHas('subjectTeachers', function($s) use ($val){ $s->where('subject_id', $val); });
                        return;
                    }
                }

                // highschool subject
                if ($field === 'highschool_subject_id') {
                    if (method_exists(\App\Models\User::class, 'HighschoolSubjectRecords')) {
                        $qb->whereHas('HighschoolSubjectRecords', function($s) use ($val){ $s->where('subject_id', $val); });
                        return;
                    }
                }

                // degree program via educationalRecords
                if ($field === 'degree_program_id') {
                    if (method_exists(\App\Models\User::class, 'educationalRecords')) {
                        $qb->whereHas('educationalRecords', function($er) use ($val){ $er->where('degree_program_id', $val); });
                        return;
                    }
                }

                // role on users table
                if ($field === 'role_id') {
                    $qb->where('role_id', $val);
                    return;
                }

                // Try as a userProfile column
                $qb->whereHas('userProfile', function($pq) use ($field, $val) { $pq->where($field, $val); });
                return;
            } catch (\Throwable $e) {
                // swallow and do nothing
                return;
            }
        }

        // Last-resort: try text match on users table column
        try { $applyLike($qb, $field); } catch (\Throwable $e) { return; }
    }
}
