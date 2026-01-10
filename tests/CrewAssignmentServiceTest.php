<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Campaign;
use App\Entity\Crew;
use App\Entity\Ship;
use App\Entity\User;
use App\Service\CrewAssignmentService;
use PHPUnit\Framework\TestCase;

class CrewAssignmentServiceTest extends TestCase
{
    public function testAssignToShipUsesCampaignSessionDate(): void
    {
        $service = new CrewAssignmentService();
        $campaign = (new Campaign())->setTitle('Op Drift')->setStartingYear(1105)->setSessionDay(120)->setSessionYear(1105);
        $ship = $this->makeShip($this->makeUser(), 'ISS Relay', 'Courier', 'B-2', '1800000.00')
            ->setCampaign($campaign);
        $crew = $this->makeCrew($this->makeUser(), 'Ari', 'Voss');

        $service->assignToShip($ship, $crew);

        self::assertSame($ship, $crew->getShip());
        self::assertSame('Active', $crew->getStatus());
        self::assertSame(120, $crew->getActiveDay());
        self::assertSame(1105, $crew->getActiveYear());
    }

    public function testAssignToShipFallsBackToShipSessionDate(): void
    {
        $service = new CrewAssignmentService();
        $ship = $this->makeShip($this->makeUser(), 'ISS Drift', 'Scout', 'S-1', '950000.00')
            ->setSessionDay(45)
            ->setSessionYear(1106);
        $crew = $this->makeCrew($this->makeUser(), 'Nova', 'Rao');

        $service->assignToShip($ship, $crew);

        self::assertSame(45, $crew->getActiveDay());
        self::assertSame(1106, $crew->getActiveYear());
    }

    public function testRemoveFromShipClearsStatusAndDates(): void
    {
        $service = new CrewAssignmentService();
        $ship = $this->makeShip($this->makeUser(), 'ISS Orion', 'Trader', 'A-1', '2000000.00');
        $crew = $this->makeCrew($this->makeUser(), 'Lira', 'Vance')
            ->setStatus('On Leave')
            ->setActiveDay(12)
            ->setActiveYear(1105)
            ->setOnLeaveDay(80)
            ->setOnLeaveYear(1105)
            ->setRetiredDay(0)
            ->setRetiredYear(0);
        $ship->addCrew($crew);

        $service->removeFromShip($ship, $crew);

        self::assertNull($crew->getShip());
        self::assertFalse($ship->getCrews()->contains($crew));
        self::assertNull($crew->getStatus());
        self::assertNull($crew->getActiveDay());
        self::assertNull($crew->getActiveYear());
        self::assertNull($crew->getOnLeaveDay());
        self::assertNull($crew->getOnLeaveYear());
        self::assertNull($crew->getRetiredDay());
        self::assertNull($crew->getRetiredYear());
    }

    public function testRemoveFromShipKeepsMiaAndDeceasedStatus(): void
    {
        $service = new CrewAssignmentService();
        $ship = $this->makeShip($this->makeUser(), 'ISS Aegis', 'Lancer', 'C-2', '4100000.00');
        $crew = $this->makeCrew($this->makeUser(), 'Mara', 'Keen')
            ->setStatus('Missing (MIA)')
            ->setMiaDay(200)
            ->setMiaYear(1105)
            ->setActiveDay(10)
            ->setActiveYear(1105);
        $ship->addCrew($crew);

        $service->removeFromShip($ship, $crew);

        self::assertSame('Missing (MIA)', $crew->getStatus());
        self::assertSame(200, $crew->getMiaDay());
        self::assertSame(1105, $crew->getMiaYear());
        self::assertNull($crew->getActiveDay());
        self::assertNull($crew->getActiveYear());
    }

    private function makeUser(): User
    {
        return (new User())
            ->setEmail(uniqid('crew@log.test', true))
            ->setPassword('hash');
    }

    private function makeShip(User $user, string $name, string $type, string $class, string $price): Ship
    {
        return (new Ship())
            ->setName($name)
            ->setType($type)
            ->setClass($class)
            ->setPrice($price)
            ->setUser($user);
    }

    private function makeCrew(User $user, string $name, string $surname): Crew
    {
        return (new Crew())
            ->setName($name)
            ->setSurname($surname)
            ->setUser($user);
    }
}
