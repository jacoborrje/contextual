<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\Series;
use App\Entity\Archive;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class SeriesType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('abbreviation', TextType::class)
            ->add('text_start_date', TextType::class, array('label' => 'Start date'))
            ->add('text_end_date', TextType::class, array('label' => 'End date'))
            ->add('description', TextareaType::class)
            ->add('research_notes', TextareaType::class, array('label' => 'Research notes'))

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $series = $event->getData();
                $form = $event->getForm();
                if(!$series || is_null($series->getId()))
                    $attributes = array('style' =>'display:none');
                else
                    $attributes = array('style' => 'display:block');

                $form->add('parent', EntityType::class, array(
                        'class' => Series::class,
                        'placeholder' => 'None',
                        'empty_data' => null,
                        'label' => 'Parent series',
                        'attr' => $attributes)
                );
                $form->add('archive', EntityType::class, array(
                        'class' => Archive::class,
                        'placeholder' => 'None',
                        'empty_data' => null,
                        'label' => 'Parent series',
                        'attr' => $attributes)
                );
                })
            ->add('submit', SubmitType::class, array('label' => 'Submit'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Series::class,
        ));
    }

}