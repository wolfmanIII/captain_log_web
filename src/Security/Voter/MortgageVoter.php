<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class MortgageVoter extends Voter
{
    public const EDIT = 'MORTGAGE_EDIT';
    public const VIEW = 'MORTGAGE_VIEW';
    public const SIGN = 'MORTGAGE_SIGN';
    public const DELETE = 'MORTGAGE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::SIGN, self::DELETE])
            && $subject instanceof \App\Entity\Mortgage;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        #if (!$user instanceof UserInterface) {
        #    return false;
        #}

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
            case self::DELETE:
                if (!$subject->isSigned()) {
                    return true;
                }
                break;

            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                break;

            case self::SIGN:
                if (
                    $subject->getCode() 
                    && !$subject->isSigned()
                ) {
                    return true;
                }
                break;
        }

        return false;
    }
}
