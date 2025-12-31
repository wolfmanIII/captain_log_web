<?php

namespace App\Security\Voter;

use App\Entity\AnnualBudget;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AnnualBudgetVoter extends Voter
{
    public const VIEW = 'annual_budget_view';
    public const EDIT = 'annual_budget_edit';
    public const DELETE = 'annual_budget_delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)) {
            return false;
        }

        return $subject instanceof AnnualBudget;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var AnnualBudget $budget */
        $budget = $subject;

        if ($budget->getUser() === null || $budget->getUser()->getId() !== $user->getId()) {
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
