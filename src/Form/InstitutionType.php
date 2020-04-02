<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\Institution;
use App\Entity\Place;
use App\Entity\Archive;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\InstitutionPlaceType;



class InstitutionType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('text_date_of_establishment', TextType::class)
            ->add('text_date_of_dissolution', TextType::class)
            ->add('description', TextareaType::class, array(
                'attr' => array(
                    'class' => 'tinymce'
                )
            ))
            ->add('research_notes', TextareaType::class, array(
                'attr' => array(
                    'class' => 'tinymce'
                )
            ))
            ->add('place', EntityType::class, array(
                'class' => Place::class,
                'placeholder' => 'None',
                'empty_data' => null
            ))
            ->add('archive', EntityType::class, array(
                'class' => Archive::class,
                'placeholder' => 'None',
                'empty_data' => null
            ))
            ->add('new_place_toggle', ChoiceType::class, array(
                'choices' => array('Create new place' => 1),
                'mapped' => false,
                'label' => false,
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('new_place', InstitutionPlaceType::class, array(
                'mapped' => false,
                'required' => false
            ))
            ->add('Create', SubmitType::class, array('label' => 'Submit'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Institution::class,
        ));
    }

}