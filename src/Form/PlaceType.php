<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\Place;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class PlaceType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typeChoices = array('Country' => 1, 'City' => 2, 'Town' => 3, 'Village' => 4, 'Hamlet' => 5, 'Neighbourhood' => 6, 'Region' => 7, 'Street' => 8, 'House' => 9, 'Church'=>10, 'Castle'=>11, 'Estate' => 12, 'Suburb' => 13);

        $builder
            ->add('name', TextType::class)
            ->add('parent', EntityType::class, array(
                'label' => 'Parent place',
                'class' => Place::class,
                'placeholder' => 'None',
                'empty_data' => null))
            ->add('type', ChoiceType::class, array(
                "choices" => $typeChoices))
            ->add('lng', TextType::class)
            ->add('lat', TextType::class)
            ->add('image', ImageFileType::class, array(
                'label' => false))
            ->add('description', TextareaType::class, array(
                'attr' => array(
                    'class' => 'tinymce'
                )
            ))
            ->add('create', SubmitType::class, array('label' => 'Submit'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Place::class,
        ));
    }

}