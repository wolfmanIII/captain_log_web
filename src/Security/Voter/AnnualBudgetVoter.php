<?php

namespace App\Security\Voter;

use App\Entity\AnnualBudget;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AnnualBudgetVoter extends Voter
{
    public const CREATE = 'annual_budget_create';
    public const VIEW = 'annual_budget_view';
    public const EDIT = 'annual_budget_edit';
    public const DELETE = 'annual_budget_delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::CREATE, self::VIEW, self::EDIT, self::DELETE], true)) {
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

        return match ($attribute) {
            self::CREATE => $this->canCreate($budget),
            self::VIEW => $this->canView($budget, $user),
            self::EDIT => $this->canEdit($budget, $user),
            self::DELETE => $this->canDelete($budget, $user),
            default => false,
        };
    }

    private function canCreate(AnnualBudget $budget): bool
    {
        return $budget->getId() === null;
    }

    private function canView(AnnualBudget $budget, User $user): bool
    {
        return $this->isOwner($budget, $user);
    }

    private function canEdit(AnnualBudget $budget, User $user): bool
    {
        return $this->isOwner($budget, $user);
    }

    private function canDelete(AnnualBudget $budget, User $user): bool
    {
        return $this->canEdit($budget, $user);
    }

    private function isOwner(AnnualBudget $budget, User $user): bool
    {
        return $budget->getUser() instanceof User
            && $budget->getUser()->getId() === $user->getId();
    }
}
