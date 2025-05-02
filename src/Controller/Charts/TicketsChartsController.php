<?php

namespace App\Controller\Charts;

use App\Entity\Model\DateTimeRangeModel;
use App\Form\DateTimeRangeType;
use App\Repository\TicketRepository;
use DateInterval;
use DatePeriod;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class TicketsChartsController extends AbstractController
{
    private function getDatesFromRange(\DateTimeInterface $start, \DateTimeInterface $end, string $format = 'd.m.Y'): array
    {
        $dates = [];
        $period = new DatePeriod(
            (clone $start)->setTime(0,0,0),
            new DateInterval('P1D'),
            (clone $end)->modify('+1 day')->setTime(0,0,0)
        );
        foreach ($period as $d) {
            $dates[] = $d->format($format);
        }
        return $dates;
    }

    #[Route('/stats/ticket', name: 'app_stats_ticket')]
    public function index(
        ChartBuilderInterface $chartBuilder,
        TicketRepository $ticketRepo,
        Request $request
    ): Response {
        //статистика за текущий месяц по дефолту
        $dateFrom = new \DateTime('first day of this month 00:00');
        $dateTo = new \DateTime('last day of this month 23:59');


        //Получим сабмит форму с датами
        $form = $this->createForm(DateTimeRangeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DateTimeRangeModel $dateSubmit */
            $dateSubmit = $form->getData();

            $dateFrom = $dateSubmit->getDateFrom();
            $dateTo = $dateSubmit->getDateTo();
        }

        // 5) Собираем статистику по билетам
        $tickets = $ticketRepo->findByDateInterval($dateFrom, $dateTo);

        $raw         = [];
        $types       = [];
        $statsByType = [];

        foreach ($tickets as $t) {
            $day   = $t->getCreatedAt()->format('d.m.Y');
            $type  = $t->getType();
            $price = $t->getPrice();

            $types[$type] = $type;
            $raw[$day][$type] = ($raw[$day][$type] ?? 0) + 1;

            if (!isset($statsByType[$type])) {
                $statsByType[$type] = ['count' => 0, 'totalPrice' => 0];
            }
            $statsByType[$type]['count']++;
            $statsByType[$type]['totalPrice'] += $price;
        }

        foreach ($statsByType as $type => $stats) {
            $statsByType[$type]['avgPrice'] = round($stats['totalPrice'] / max($stats['count'], 1), 2);
        }

        // 6) Подготовка данных для графика
        $labels  = $this->getDatesFromRange($dateFrom, $dateTo);
        $datasets = [];
        $palette  = [
            'rgb(255, 99, 132)',
            'rgb(54, 162, 235)',
            'rgb(255, 205, 86)',
            'rgb(75, 192, 192)',
            'rgb(153, 102, 255)',
        ];

        $i = 0;
        foreach ($types as $type) {
            $data = [];
            foreach ($labels as $day) {
                $data[] = $raw[$day][$type] ?? 0;
            }

            $color = $palette[$i % count($palette)];
            $datasets[] = [
                'label'           => $type,
                'data'            => $data,
                'backgroundColor' => $color,
                'borderColor'     => $color,
            ];
            $i++;
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels'   => $labels,
            'datasets' => $datasets,
        ]);
        $chart->setOptions([
            'responsive'          => true,
            'maintainAspectRatio' => false,
            'plugins'             => [
                'title'   => [
                    'display' => true,
                    'text'    => sprintf(
                        'Продажи билетов по типам: %s — %s',
                        $dateFrom->format('d.m.Y'),
                        $dateTo->format('d.m.Y')
                    ),
                ],
                'legend'  => ['position' => 'top'],
                'tooltip' => ['enabled' => true],
            ],
            'scales'              => [
                'x' => ['ticks' => ['autoSkip' => true, 'maxRotation' => 45]],
                'y' => ['beginAtZero' => true],
            ],
        ]);

        $date = (new DateTimeRangeModel())
            ->setDateFrom($dateFrom)
            ->setDateTo($dateTo);

        $form = $this->createForm(DateTimeRangeType::class, $date);
        return $this->render('admin/field/tickets_charts.html.twig', [
            'form'        => $form->createView(),
            'chart'       => $chart,
            'statsByType' => $statsByType,
        ]);
    }
}
