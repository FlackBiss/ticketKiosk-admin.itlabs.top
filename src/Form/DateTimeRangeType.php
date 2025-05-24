<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Model\DateTimeRangeModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeRangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
                'html5'  => true,
                'attr'   => [
                    'class'       => 'form-control js-datepicker',
                    'placeholder' => 'Дата начала',
                ],
                'label'  => 'С',
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
                'html5'  => true,
                'attr'   => [
                    'class'       => 'form-control js-datepicker',
                    'placeholder' => 'Дата окончания',
                ],
                'label'  => 'По',
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'title',
                'required' => false,
                'placeholder' => 'Все мероприятия',
                'attr' => [
                    'class' => 'form-select',
                ],
                'label' => 'Мероприятие',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DateTimeRangeModel::class,
        ]);
    }
}
