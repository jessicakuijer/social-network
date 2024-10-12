<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['edit'])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var User $subjectUser */
        $subjectUser = $subject;

        return match($attribute) {
            'edit' => $this->canEdit($subjectUser, $user),
            default => false,
        };
    }

    private function canEdit(User $subjectUser, UserInterface $user): bool
    {
        // L'utilisateur ne peut éditer que son propre profil
        return $user === $subjectUser;
    }
}