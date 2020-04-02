<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\Archive;
use App\Entity\Place;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class ArchiveType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('abbreviation', TextType::class)
            ->add('parent', EntityType::class, array(
                'label' => 'Parent archive',
                'class' => Archive::class,
                'placeholder' => 'None',
                'empty_data' => null))

            ->add('research_notes', TextareaType::class, array('label' => 'Research notes'))
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $archive = $event->getData();
                $form = $event->getForm();
                if (!$archive || is_null($archive->getId()))
                    $attributes = array('style' => 'display:none');
                else
                    $attributes = array('style' => 'display:block');

                $form->add('parent', EntityType::class, array(
                        'class' => Archive::class,
                        'placeholder' => 'None',
                        'empty_data' => null,
                        'label' => 'Parent archive')
                );
                $form->add('place', EntityType::class, array(
                        'class' => Place::class,
                        'placeholder' => 'None',
                        'empty_data' => null,
                        'label' => 'Place'
                ));
            })
            ->add('submit', SubmitType::class, array('label' => 'Submit'))
            ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Archive::class,
            'parent' => 0
        ));
    }

}