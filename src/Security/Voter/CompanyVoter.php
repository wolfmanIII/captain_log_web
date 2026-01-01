<?php

namespace App\Security\Voter;

use App\Entity\Company;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CompanyVoter extends Voter
{
    public const CREATE = 'COMPANY_CREATE';
    public const EDIT = 'COMPANY_EDIT';
    public const VIEW = 'COMPANY_VIEW';
    public const DELETE = 'COMPANY_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof Company) {
            return false;
        }

        return in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::CREATE => $this->canCreate($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    private function canCreate(Company $company, ?UserInterface $user = null): bool
    {
        return $company->getId() === null;
    }

    private function canView(Company $company, ?UserInterface $user = null): bool
    {
        return $this->isOwner($company, $user);
    }

    private function canEdit(Company $company, ?UserInterface $user = null): bool
    {
        return $this->isOwner($company, $user);
    }

    private function canDelete(Company $company, ?UserInterface $user = null): bool
    {
        return $this->canEdit($company, $user);
    }

    private function isOwner(Company $company, UserInterface $user): bool
    {
        return $company->getUser() instanceof User
            && $user instanceof User
            && $company->getUser()->getId() === $user->getId();
    }
}
