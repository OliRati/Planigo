<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startAt', null, [
                'widget' => 'single_text',
                'label'=> 'Date de début',
            ])
            ->add('endAt', null, [
                'widget' => 'single_text',
                'label'=> 'Date de fin',
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'nom'
            ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
