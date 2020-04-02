<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\Action;
use App\Entity\Correspondent;
use App\Entity\Place;
use App\Form\Type\CorrespondentAutocompleteType;
use App\Form\Type\PlaceAutocompleteType;
use App\Repository\CorrespondentRepository;
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


class ActionType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('text_start_date', TextType::class, array('label' => 'Start date'))
            ->add('text_end_date', TextType::class, array('label' => 'End date'))
            ->add('description', TextType::class, array('label' => 'Description'))

            ->add('type', ChoiceType::class, array( "choices" => array(
                "none" => 0,
                "author" => 1,
                "recipient" => 2,
                "signer" => 3,
                "deliverer" => 4,
                "answerer" => 5
            )))
            ->add('correspondentText', TextType::class)
            ->add('correspondent', CorrespondentAutocompleteType::class)
            ->add('place', PlaceAutocompleteType::class, array(
            ), array('label' => 'Place where it happened'))
            ->add('placeText', TextType::class)
            ->add('description', TextType::class)
         ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Action::class,
        ));
    }
}