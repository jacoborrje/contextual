<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\ActorPlace;
use App\Entity\Place;
use App\Entity\Institution;
use App\Form\Type\PlaceAutocompleteType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class ActorPlaceType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('place', PlaceAutocompleteType::class, array(
                'empty_data' => null
            ))
            ->add('place_text', TextType::class, array('label' => 'Place'))
            ->add('type', ChoiceType::class, array(
                'choices' => array(
                        'birthplace' => 0,
                        'place of death' => 1,
                        'place of residence' => 2,
                        'place of work' => 3,
                        'country residence' => 4,
                        'visited' => 5,
                )
            ))
            ->add('text_date_of_arrival', TextType::class, array('label' => 'Start date'))
            ->add('text_date_of_leaving', TextType::class, array('label' => 'End date'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ActorPlace::class,
        ));
    }
}