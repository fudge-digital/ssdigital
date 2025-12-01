<?php

namespace App\Policies;

use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudentDocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StudentDocument $studentDocument): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['orang_tua','admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StudentDocument $studentDocument): bool
    {
        // admin atau uploader asli (parent) boleh update
        return $user->role === 'admin' || $doc->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StudentDocument $studentDocument): bool
    {
        // admin atau uploader asli (parent)
        return $user->role === 'admin' || $doc->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StudentDocument $studentDocument): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StudentDocument $studentDocument): bool
    {
        return false;
    }
}
