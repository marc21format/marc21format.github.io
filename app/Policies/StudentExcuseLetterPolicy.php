<?php

namespace App\Policies;

use App\Models\StudentExcuseLetter;
use App\Models\User;

class StudentExcuseLetterPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StudentExcuseLetter $letter): bool
    {
        return $user->role_id <= 2 || $letter->user_id == $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role_id <= 2 || $user->role_id == 4; // admins/execs and students
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StudentExcuseLetter $letter): bool
    {
        return $user->role_id <= 2 || $letter->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StudentExcuseLetter $letter): bool
    {
        return $user->role_id <= 2 || $letter->user_id == $user->id;
    }
}