<?php

namespace App\Security\Voter;

use App\Entity\Ship;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class ShipVoter extends Voter
{
    public const EDIT = 'SHIP_EDIT';
    public const VIEW = 'SHIP_VIEW';
    public const DELETE = 'SHIP_DELETE';
    public const CREW_REMOVE = 'SHIP_CREW_REMOVE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Ship;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        #if (!$user instanceof UserInterface) {
        #    return true;
        #}


        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                return true;

            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                return true;

            case self::DELETE:
                if (
                    $subject->getCrews()->count() <= 0
                    || !$subject->hasMortgage()
                ) {
                    return true;
                }
                break;

            case self::CREW_REMOVE:
                if (!$subject->hasMortgageSigned()) {
                    return true;
                }
                break;
        }

        return false;
    }
}
