<?php

namespace App\Form;

use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description')
            ->add('statut')
            ->add('date_creation', null, [
                'widget' => 'single_text',
            ])
            ->add('date_maximum_reso', null, [
                'widget' => 'single_text',
            ])
            ->add('date_resolu', null, [
                'widget' => 'single_text',
            ])
            ->add('priorite')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
