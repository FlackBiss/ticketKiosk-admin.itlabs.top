<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\ExceptionLog;
use App\Entity\Information;
use App\Entity\Mail;
use App\Entity\News;
use App\Entity\Scheme;
use App\Entity\Place;
use App\Entity\Sessions;
use App\Entity\StandBy;
use App\Entity\Terminal;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\InformationRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly InformationRepository $informationRepository,
    )
    {
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route(path: '/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(EventCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<span>Админ-панель</span>')
            ->setFaviconPath('favicon.ico')
            ->renderContentMaximized()
            ->generateRelativeUrls();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Контент');
        yield MenuItem::linkToCrud('Новости', 'fa fa-newspaper', News::class);
        if ($this->informationRepository->count([]) === 0) {
            yield MenuItem::linkToCrud('Информация', 'fa fa-info-circle', Information::class)
                ->setAction(Action::NEW);
        } else {
            yield MenuItem::linkToCrud('Информация', 'fa fa-info-circle', Information::class)
                ->setAction(Action::EDIT)->setEntityId($this->informationRepository->findAll()[0]->getId());
        }


        yield MenuItem::section('Мероприятия и билеты');
        yield MenuItem::linkToCrud('Мероприятия', 'fa fa-calendar-check', Event::class);
        yield MenuItem::linkToCrud('Билеты', 'fa fa-ticket-alt', Ticket::class);
        yield MenuItem::linkToCrud('Схемы зала', 'fa fa-table', Scheme::class);
        yield MenuItem::linkToCrud('Места', 'fa fa-chair', Place::class);

        yield MenuItem::section('Терминалы и сессии');
        yield MenuItem::linkToCrud('Терминалы', 'fa fa-desktop', Terminal::class);
        yield MenuItem::linkToCrud('Сессии', 'fa fa-clock', Sessions::class);
        yield MenuItem::linkToCrud('Ожидание', 'fa fa-pause', StandBy::class);

        yield MenuItem::section('Логи');
        yield MenuItem::linkToCrud('Исключения', 'fa fa-bug', ExceptionLog::class);

        yield MenuItem::linkToRoute(
            'Билеты — по датам',
            'fa fa-ticket-alt',
            'app_stats_ticket'
        );

        yield MenuItem::section('Прочее');
        yield MenuItem::linkToCrud('Пользователи', 'fas fa-user-gear', User::class)
            ->setPermission('ROLE_ADMIN');
    }
}
