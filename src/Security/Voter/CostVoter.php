<?php

namespace App\Security\Voter;

use App\Entity\Cost;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CostVoter extends Voter
{
    public const VIEW = 'cost_view';
    public const EDIT = 'cost_edit';
    public const DELETE = 'cost_delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)) {
            return false;
        }

        return $subject instanceof Cost;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Cost $cost */
        $cost = $subject;

        if ($cost->getUser() === null || $cost->getUser()->getId() !== $user->getId()) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => true,
            self::EDIT => true,
            self::DELETE => true,
            default => false,
        };
    }
}
