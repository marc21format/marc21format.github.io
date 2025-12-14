<?php
namespace App\Http\Livewire\Traits;

trait RosterFilters
{
    /**
     * Apply a roster filter to a User query builder.
     * Field keys are simple identifiers like 'name','student_number','student_group','committee','subject', etc.
     */
    protected function applyRosterFilter($qb, ?string $field, $value)
    {
        if (! $field || $value === null || $value === '') return;

        $val = $value;

        // 'all' runs the global search across many relations
        if ($field === 'all') {
            $this->applyGlobalRosterSearch($qb, (string) $val);
            return;
        }
        // username/name/email/phone text search. For 'name' also search userProfile f_name/m_name/s_name.
        if (in_array($field, ['name','username','email','phone'])) {
            // Map 'username' to users.name (this project uses users.name as display/username)
            if ($field === 'username') {
                $col = 'name';
            } elseif ($field === 'phone') {
                // phone is stored on userProfile.phone_number
                $qb->whereHas('userProfile', function($pq) use ($val) {
                    $pq->where('phone_number','like', "%{$val}%");
                });
                return;
            } else {
                $col = $field;
            }

            $qb->where(function($q) use ($col, $field, $val) {
                $q->where($col, 'like', "%{$val}%");
                if ($field === 'name') {
                    // search profile name parts too
                    $q->orWhereHas('userProfile', function($pq) use ($val) {
                        $pq->where('f_name','like', "%{$val}%")
                           ->orWhere('m_name','like', "%{$val}%")
                           ->orWhere('s_name','like', "%{$val}%");
                    });
                }
            });
            return;
        }

        // FCEER profile fields
        if (in_array($field, ['student_number','volunteer_number','fceer_batch'])) {
            $qb->whereHas('fceerProfile', function($q) use ($field, $val) {
                $q->where($field, 'like', "%{$val}%");
            });
            return;
        }

        // student_group can be either room id (numeric) or partial room name
        if ($field === 'student_group') {
            if (is_numeric($val)) {
                $qb->whereHas('fceerProfile', function($q) use ($val) { $q->where('student_group', $val); });
            } else {
                $qb->whereHas('fceerProfile.group', function($q) use ($val) { $q->where('group', 'like', "%{$val}%"); });
            }
            return;
        }

        // committee membership
        if (in_array($field, ['committee','committee_id'])) {
            if (is_numeric($val)) {
                $qb->whereHas('committeeMemberships', function($q) use ($val){ $q->where('committee_id', $val); });
            } else {
                $qb->whereHas('committeeMemberships.committee', function($q) use ($val){ $q->where('committee_name','like', "%{$val}%"); });
            }
            return;
        }

        // committee position
        if (in_array($field, ['position','position_id','committee_position'])) {
            if (is_numeric($val)) {
                $qb->whereHas('committeeMemberships', function($q) use ($val){ $q->where('position_id', $val); });
            } else {
                $qb->whereHas('committeeMemberships.position', function($q) use ($val){ $q->where('position_name','like', "%{$val}%"); });
            }
            return;
        }

        // volunteer / teaching subject
        if (in_array($field, ['subject','subject_id','volunteer_subject'])) {
            if (is_numeric($val)) {
                if (method_exists(\App\Models\User::class, 'subjectTeachers')) {
                    $qb->whereHas('subjectTeachers', function($q) use ($val){ $q->where('subject_id', $val); });
                }
            } else {
                if (method_exists(\App\Models\User::class, 'subjectTeachers')) {
                    $qb->whereHas('subjectTeachers.subject', function($q) use ($val){ $q->where('name','like', "%{$val}%"); });
                }
                // fallback to volunteerSubjects relation if available
                if (method_exists(\App\Models\User::class, 'volunteerSubjects')) {
                    $qb->orWhereHas('volunteerSubjects', function($q) use ($val){ $q->where('name','like', "%{$val}%"); });
                }
            }
            return;
        }

        // highschool / highschool subject
        if ($field === 'highschool') {
            if (is_numeric($val)) {
                $qb->whereHas('highschoolRecords', function($q) use ($val){ $q->where('highschool_id', $val); });
            } else {
                // search the highschool's name via relation
                $qb->whereHas('highschoolRecords.highschool', function($q) use ($val){ $q->where('highschool_name','like', "%{$val}%"); });
            }
            return;
        }

        if ($field === 'highschool_subject') {
            if (is_numeric($val)) {
                // HighschoolSubjectRecord stores highschoolsubject_id
                $qb->whereHas('HighschoolSubjectRecords', function($q) use ($val){ $q->where('highschoolsubject_id', $val); });
            } else {
                $qb->whereHas('HighschoolSubjectRecords.subject', function($q) use ($val){ $q->where('subject_name','like', "%{$val}%"); });
            }
            return;
        }

        // educationalRecords based filters
        if (in_array($field, ['degree_program','degree_program_id'])) {
            if (is_numeric($val)) {
                $qb->whereHas('educationalRecords', function($q) use ($val){ $q->where('degreeprogram_id', $val); });
            } else {
                // search degree program name via degreeProgram relation
                $qb->whereHas('educationalRecords.degreeProgram', function($q) use ($val){ $q->where('full_degree_program_name','like', "%{$val}%"); });
            }
            return;
        }

        // university
        if (in_array($field, ['university','university_id'])) {
            if (is_numeric($val)) {
                $qb->whereHas('educationalRecords', function($q) use ($val){ $q->where('university_id', $val); });
            } else {
                $qb->whereHas('educationalRecords.university', function($q) use ($val){ $q->where('university_name','like', "%{$val}%"); });
            }
            return;
        }

        // field of work: this project stores fields of work as a separate model (FieldOfWork)
        // linked via professional credentials. Search professionalCredentials->fieldOfWork.
        if (in_array($field, ['field_of_work','field_of_work_id'])) {
            if (is_numeric($val)) {
                if (method_exists(\App\Models\User::class, 'professionalCredentials')) {
                    $qb->whereHas('professionalCredentials', function($q) use ($val){ $q->where('fieldofwork_id',$val); });
                }
            } else {
                if (method_exists(\App\Models\User::class, 'professionalCredentials')) {
                    $qb->whereHas('professionalCredentials.fieldOfWork', function($q) use ($val){ $q->where('name','like', "%{$val}%"); });
                }
            }
            return;
        }

        // address: city / barangay
        // address search: house/block/lot/street OR city/barangay/province names
        if (in_array($field, ['address','address_id'])) {
            if (is_numeric($val)) {
                // match via address_id on profile
                $qb->whereHas('userProfile', function($q) use ($val){ $q->where('address_id',$val); });
            } else {
                // search address columns and related names (house_number, block_number, street, lot_number)
                $qb->whereHas('userProfile.address', function($aq) use ($val) {
                    $aq->where('house_number','like', "%{$val}%")
                       ->orWhere('block_number','like', "%{$val}%")
                       ->orWhere('street','like', "%{$val}%")
                       ->orWhere('lot_number','like', "%{$val}%");
                });

                // also search city/barangay/province names via relations
                $qb->orWhereHas('userProfile.address.city', function($c) use ($val){ $c->where('city_name','like', "%{$val}%"); });
                $qb->orWhereHas('userProfile.address.barangay', function($b) use ($val){ $b->where('barangay_name','like', "%{$val}%"); });
                $qb->orWhereHas('userProfile.address.province', function($p) use ($val){ $p->where('province_name','like', "%{$val}%"); });
            }
            return;
        }

        // barangay filter kept for backward compatibility
        if (in_array($field, ['barangay','barangay_id'])) {
            if (is_numeric($val)) {
                $qb->whereHas('userProfile.address', function($q) use ($val){ $q->where('barangay_id',$val); });
            } else {
                $qb->whereHas('userProfile.address.barangay', function($q) use ($val){ $q->where('barangay_name','like', "%{$val}%"); });
            }
            return;
        }

        // Last resort: try userProfile columns or user columns
        try {
            $qb->whereHas('userProfile', function($q) use ($field, $val){ $q->where($field, 'like', "%{$val}%"); });
            return;
        } catch (\Throwable $_) {
            try { $qb->where($field, 'like', "%{$val}%"); } catch (\Throwable $_) { /* ignore */ }
        }
    }

    /**
     * Apply a global search across common roster fields and relations.
     */
    protected function applyGlobalRosterSearch($qb, string $search)
    {
        if (! $search) return;
        $q = $search;
        $qb->where(function($qmain) use ($q) {
            $qmain->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%");

            // fceer profile
            if (method_exists(\App\Models\User::class, 'fceerProfile')) {
                $qmain->orWhereHas('fceerProfile', function($fq) use ($q) {
                    $fq->where('student_number', 'like', "%{$q}%")
                       ->orWhere('volunteer_number', 'like', "%{$q}%")
                       ->orWhere('fceer_batch', 'like', "%{$q}%");
                });
            }

            // committee name / position
            if (method_exists(\App\Models\User::class, 'committeeMemberships')) {
                $qmain->orWhereHas('committeeMemberships.committee', function($cq) use ($q) { $cq->where('committee_name','like', "%{$q}%"); });
                $qmain->orWhereHas('committeeMemberships.position', function($pq) use ($q) { $pq->where('position_name','like', "%{$q}%"); });
            }

            // teaching/volunteer subject
            if (method_exists(\App\Models\User::class, 'subjectTeachers')) {
                $qmain->orWhereHas('subjectTeachers.subject', function($sq) use ($q) { $sq->where('subject_name','like', "%{$q}%"); });
            }
            if (method_exists(\App\Models\User::class, 'volunteerSubjects')) {
                $qmain->orWhereHas('volunteerSubjects', function($vs) use ($q) { $vs->where('subject_name','like', "%{$q}%"); });
            }

            // highschool / highschool subjects (use related models' name columns)
            if (method_exists(\App\Models\User::class, 'highschoolRecords')) {
                // highschoolRecords -> highschool.highschool_name
                $qmain->orWhereHas('highschoolRecords.highschool', function($hh) use ($q) { $hh->where('highschool_name','like', "%{$q}%"); });
            }
            if (method_exists(\App\Models\User::class, 'HighschoolSubjectRecords')) {
                // HighschoolSubjectRecords -> subject.subject_name
                $qmain->orWhereHas('HighschoolSubjectRecords.subject', function($hss) use ($q) { $hss->where('subject_name','like', "%{$q}%"); });
            }

            // educational records -> degreeProgram.full_degree_program_name and university.university_name
            if (method_exists(\App\Models\User::class, 'educationalRecords')) {
                $qmain->orWhereHas('educationalRecords.degreeProgram', function($d) use ($q) { $d->where('full_degree_program_name','like', "%{$q}%"); });
                $qmain->orWhereHas('educationalRecords.university', function($u) use ($q) { $u->where('university_name','like', "%{$q}%"); });
            }

            // userProfile name parts
            if (method_exists(\App\Models\User::class, 'userProfile')) {
                $qmain->orWhereHas('userProfile', function($up) use ($q) {
                    $up->where('f_name','like', "%{$q}%")
                       ->orWhere('m_name','like', "%{$q}%")
                       ->orWhere('s_name','like', "%{$q}%")
                       ->orWhere('phone_number','like', "%{$q}%");
                });

                // address -> search address columns and related names (house/block/street/lot, city, barangay, province)
                if (method_exists(\App\Models\UserProfile::class, 'address')) {
                    $qmain->orWhereHas('userProfile.address', function($a) use ($q) {
                        $a->where('house_number','like', "%{$q}%")
                          ->orWhere('block_number','like', "%{$q}%")
                          ->orWhere('street','like', "%{$q}%")
                          ->orWhere('lot_number','like', "%{$q}%");
                    });

                    $qmain->orWhereHas('userProfile.address.city', function($c) use ($q) { $c->where('city_name','like', "%{$q}%"); });
                    $qmain->orWhereHas('userProfile.address.barangay', function($b) use ($q) { $b->where('barangay_name','like', "%{$q}%"); });
                    $qmain->orWhereHas('userProfile.address.province', function($p) use ($q) { $p->where('province_name','like', "%{$q}%"); });
                }
            }
        });
    }
}
