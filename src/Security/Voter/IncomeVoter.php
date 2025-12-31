<?php

namespace App\Security\Voter;

use App\Entity\Income;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class IncomeVoter extends Voter
{
    public const VIEW = 'income_view';
    public const EDIT = 'income_edit';
    public const DELETE = 'income_delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)) {
            return false;
        }

        return $subject instanceof Income;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Income $income */
        $income = $subject;

        if ($income->getUser() === null || $income->getUser()->getId() !== $user->getId()) {
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
