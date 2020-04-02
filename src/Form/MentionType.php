<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\Action;
use App\Entity\Mention;
use App\Entity\Place;
use App\Entity\Source;
use App\Entity\Actor;
use App\Entity\Institution;
use App\Form\DataTransformer\InstitutionAutocompleteTransformer;
use App\Form\DataTransformer\TimeTransformer;
use App\Form\Type\InstitutionAutocompleteType;
use App\Form\Type\PlaceAutocompleteType;
use App\Form\Type\TimeType;
use App\Form\Type\ActorAutocompleteType;
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


class MentionType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('info_source', EntityType::class, array(
                'class' => Source::class,
                'attr' => array('style' => 'display:none;')
            ))
            ->add('verb', TextType::class, array('label' => 'The action that was performed.'))

        ->add('text_date', TextType::class, array('label' => 'Date that the mentioned event happened'));

        $builder->add($builder->create('time', TextType::class, array('label' => 'Time that the mentioned event happened'))
            ->addModelTransformer(new TimeTransformer())
        );

        $builder->add('description', TextareaType::class, array('label' => 'Description of the event'))

            ->add('actor', ActorAutocompleteType::class, array(
                'label' => 'The actor who performed what was mentioned',
                'empty_data' => null
            ))
            ->add('actorText', TextType::class, array(
                'label' => 'The actor who performed what was mentioned',
                'empty_data' => null
            ))
            ->add('institution', InstitutionAutocompleteType::class, array(
                'label' => 'The mentioned institution',
                'empty_data' => null
            ))
            ->add('institutionText', TextType::class, array(
                'label' => 'The mentioned institution',
                'empty_data' => null
            ))
            ->add('place', PlaceAutocompleteType::class, array(
                'label' => 'A place mentioned in the source',
                'empty_data' => null
            ))
            ->add('placeText', TextType::class, array(
                'label' => 'A place mentioned in the source',
                'empty_data' => null
            ))
            ->add('eventPlace', PlaceAutocompleteType::class, array(
                'label' => 'The place where it happened',
                'empty_data' => null
            ))
            ->add('eventPlaceText', TextType::class, array(
                'label' => 'A place mentioned in the source',
                'empty_data' => null
            ))

            ->add('mentioned_source', EntityType::class, array(
                'label' => 'The source that was mentioned (i.e., not the source in which the event was mentioned)',
                'class' => Source::class,
                'placeholder' => 'None',
                'empty_data' => null
            ))
            ->add('startPage', TextType::class, array('label' => 'Start page'))
            ->add('endPage', TextType::class, array('label' => 'End page'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Mention::class,
        ));
    }
}