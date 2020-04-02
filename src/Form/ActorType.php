<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\Actor;
use App\Entity\ActorOccupation;
use App\Entity\Place;

use App\Form\DataTransformer\PlaceAutocompleteTransformer;
use App\Form\Type\PlaceAutocompleteType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
Use App\Form\RelationshipType;
Use App\Form\OccupationType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ActorType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('surname', TextType::class, array('label' => 'Surname'))
            ->add('first_name', TextType::class, array('label' => 'First name'))
            ->add('alt_surnames', TextType::class, array('label' => 'Alternative surnames'))
            ->add('alt_first_names', TextType::class, array('label' => 'Alternative first names'))
            ->add('text_birthdate', TextType::class, array('label' => 'Birthdate', 'empty_data'    => '',
                'required'      => false))
            ->add('text_date_of_death', TextType::class, array('label' => 'Date of death', 'empty_data'    => '',
        'required'      => false,))
            ->add ('description', TextareaType::class, array(
                'attr' => array('class' => 'tinymce', 'style' => 'height:602px;')))
            ->add ('research_notes', TextareaType::class, array(
                'attr' => array('class' => 'tinymce', 'style' => 'height:602px;')))
            ->add('birth_place', PlaceAutocompleteType::class)
            ->add('birth_place_text', TextType::class)
            ->add('place_of_death', PlaceAutocompleteType::class)
            ->add('place_of_death_text', TextType::class)
            ->add('gender', ChoiceType::class, array( "choices" => array(
                "unknown" => null,
                "male" => 0,
                "female" => 1
            )))
            ->add('primaryRelationships', CollectionType::class, array(
                'entry_type' => RelationshipType::class,
                'entry_options' => array('label' => false),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('secondaryRelationships', CollectionType::class, array(
                'entry_type' => RelationshipType::class,
                'entry_options' => array('label' => false),
                'allow_add' => false,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('occupations', CollectionType::class, array(
                'entry_type' => ActorOccupationType::class,
                'entry_options' => array('label' => false),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ))
            ->add('places', CollectionType::class, array(
                'entry_type' => ActorPlaceType::class,
                'entry_options' => array('label' => false),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('submit', SubmitType::class, array('label' => 'Submit'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Actor::class,
        ));
    }

}