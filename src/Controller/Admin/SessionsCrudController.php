<?php

namespace App\Controller\Admin;

use App\Entity\Sessions;
use App\Repository\SessionsRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class SessionsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sessions::class;
    }

    public function __construct(private readonly SessionsRepository $sessionsRepository,)
    {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Сеансы')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportAction = Action::new('exportExcel', 'Выгрузить Excel')
            ->linkToCrudAction('exportExcel')
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $exportAction)
            ->disable(Action::NEW, Action::DELETE, Action::EDIT);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addCssFile('/css/admin.css');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();

        yield DateTimeField::new('startAt', 'Начало')->hideOnForm();
        yield DateTimeField::new('endAt', 'Окончание')->hideOnForm();

        yield IntegerField::new('deltaTime', 'Продолжительность')
            ->formatValue(fn($value) => $value . ' сек.');
        yield AssociationField::new('terminal', 'Терминал');
        yield IntegerField::new('events', 'Событий')
            ->formatValue(fn($value) => count($value));
        yield AssociationField::new('events', false)
            ->setFormType(EntityType::class)
            ->setTemplatePath('admin/field/sessions.html.twig');
    }

    public function exportExcel(): Response
    {
        $sessions = $this->sessionsRepository->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Сеансы');

        $row = 1;

        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'Отчёт по сеансам терминала');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row += 2;

        foreach ($sessions as $session) {
            $sheet->fromArray([
                'Session ID', 'Начало', 'Окончание', 'Длительность (сек.)', 'Терминал', 'Кол-во событий'
            ], null, "A{$row}");

            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFCCE5FF'],
                ],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $row++;

            $sheet->fromArray([
                $session->getId(),
                $session->getStartAt()?->format('Y-m-d H:i:s'),
                $session->getEndAt()?->format('Y-m-d H:i:s'),
                $session->getDeltaTime(),
                $session->getTerminal()?->__toString(),
                $session->getEvents()->count(),
            ], null, "A{$row}");
            $sheet->getStyle("A{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $row++;

            if ($session->getEvents()->count() > 0) {
                $sheet->fromArray(['ID', 'Объект', 'Дата/время', 'Координаты', 'Ответ'], null, "B{$row}");
                $sheet->getStyle("B{$row}:F{$row}")->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFEFEF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_DOTTED]],
                ]);
                $row++;

                foreach ($session->getEvents() as $event) {
                    $sheet->fromArray([
                        '',
                        $event->getId(),
                        $event->getObjectName(),
                        $event->getDateAt()?->format('Y-m-d H:i:s'),
                        $event->getCoordinates(),
                        $event->getResponse(),
                    ], null, "A{$row}");
                    $sheet->getStyle("B{$row}:F{$row}")->getAlignment()->setWrapText(true);
                    $row++;
                }
            } else {
                $sheet->setCellValue("B{$row}", 'Нет событий');
                $row++;
            }

            $row += 2;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $maxRow = $sheet->getHighestRow();
        $sheet->getStyle("A3:F{$maxRow}")->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(fn() => $writer->save('php://output'));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', ResponseHeaderBag::DISPOSITION_ATTACHMENT . ';filename="Сеансы_терминалов.xlsx"');

        return $response;
    }
}
