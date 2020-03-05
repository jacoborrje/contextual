<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\Source;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class SourceType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('title', TextType::class, array('label' => 'Title'))
            ->add('place_in_volume', TextType::class, array('label' => 'Place in volume'))
            ->add('pages', TextType::class)
            ->add('status', ChoiceType::class, array( "choices" => array(
                "not ordered" => 0,
                "ordered" => 1,
                "unfinished" => 2,
                "surveyed" => 3,
                "scanned" => 4,
                "completed" => 5
            ))
            )
            ->add('date', TextType::class, array('label' => 'Creation date'))
            ->add ('transcription', TextareaType::class, array(
                'attr' => array('class' => 'tinymce', 'style' => 'height:602px;')))
            ->add ('research_notes', TextareaType::class, array(
                'attr' => array('class' => 'tinymce', 'style' => 'height:602px;')))
            ->add ('excerpt', TextareaType::class, array(
                'attr' => array('class' => 'tinymce', 'style' => 'height:602px;')))
            ->add('submit', SubmitType::class, array('label' => 'Create source'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Source::class,
        ));
    }

}