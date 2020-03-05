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
            ->add('text_date', TextType::class, array('label' => 'Date that the mentioned event happened'))
            ->add ('description', TextareaType::class, array('label' => 'Description of the event')
            )
            ->add('actor', EntityType::class, array(
                'label' => 'The actor who performed what was mentioned',
                'class' => Actor::class,
                'placeholder' => 'None',
                'empty_data' => null
            ))
            ->add('institution', EntityType::class, array(
                'label' => 'The mentioned institution',
                'class' => Institution::class,
                'placeholder' => 'None',
                'empty_data' => null
            ))
            ->add('place', EntityType::class, array(
                'label' => 'The place where it happened',
                'class' => Place::class,
                'placeholder' => 'None',
                'empty_data' => null
            ))
            /*->add('action', EntityType::class, array(
                'class' => Action::class,
            ))*/
            ->add('mentioned_source', EntityType::class, array(
                'label' => 'The source that was mentioned (i.e., not the source in which the event was mentioned)',
                'class' => Source::class,
                'placeholder' => 'None',
                'empty_data' => null
            ))
            ->add('start_page', TextType::class, array('label' => 'Start page'))
            ->add('end_page', TextType::class, array('label' => 'Start page'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Mention::class,
        ));
    }
}