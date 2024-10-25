<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'Title',
                'attr' => ['placeholder' => 'Search by title'],
            ])
            ->add('priority', ChoiceType::class, [
                'required' => false,
                'label' => 'Priority',
                'choices' => [
                    'High Priority' => 'High',
                    'Medium Priority' => 'Medium',
                    'Low Priority' => 'Low',
                ],
                'placeholder' => 'Select Priority',
            ])
            ->add('status', ChoiceType::class, [
                'required' => false,
                'label' => 'Status',
                'choices' => [
                    'Open' => 'open',
                    'In Progress' => 'in_progress',
                    'Resolved' => 'resolved',
                    'Closed' => 'closed',
                ],
                'placeholder' => 'Select Status',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
