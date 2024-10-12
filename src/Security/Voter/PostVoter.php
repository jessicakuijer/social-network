<?php

namespace App\Security\Voter;

use App\Entity\Post;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PostVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['edit', 'delete'])
            && $subject instanceof Post;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // si l'utilisateur n'est pas connecté, refuser l'accès
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Post $post */
        $post = $subject;

        return match($attribute) {
            'edit', 'delete' => $this->canEditOrDelete($post, $user),
            default => false,
        };
    }

    private function canEditOrDelete(Post $post, UserInterface $user): bool
    {
        return $user === $post->getUser();
    }
}