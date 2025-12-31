<?php

namespace App\Controller\Admin;

use App\Entity\Insurance;
use App\Entity\InterestRate;
use App\Entity\ShipRole;
use App\Entity\CostCategory;
use App\Entity\DocumentFile;
use App\Entity\DocumentChunk;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public function index(): Response
    {
        $links = [
            'Interest Rate' => $this->adminUrlGenerator->setController(InterestRateCrudController::class)->setAction('index')->generateUrl(),
            'Insurance' => $this->adminUrlGenerator->setController(InsuranceCrudController::class)->setAction('index')->generateUrl(),
            'Ship Role' => $this->adminUrlGenerator->setController(ShipRoleCrudController::class)->setAction('index')->generateUrl(),
            'Cost Category' => $this->adminUrlGenerator->setController(CostCategoryCrudController::class)->setAction('index')->generateUrl(),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'links' => $links,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Captain Log Web');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Captain Log', 'fa fa-home', 'app_home');

        yield MenuItem::section('Settings');
        yield MenuItem::linkToCrud('Interest Rate', 'fas fa-list', InterestRate::class);
        yield MenuItem::linkToCrud('Insurance', 'fas fa-list', Insurance::class);
        yield MenuItem::linkToCrud('ShipRole', 'fas fa-list', ShipRole::class);
        yield MenuItem::linkToCrud('Cost Category', 'fas fa-list', CostCategory::class);
    }
}
