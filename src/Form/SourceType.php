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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
Use App\Form\ActionType;
Use App\Form\MentionType;
Use App\Form\SourceTopicType;
Use App\Form\UploadType;


class SourceType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typeChoices = array('letter' => 1,
                             'book' => 2,
                             'document' => 3,
                             'article' => 4,
                             'travelogue' => 5,
                             'receipt' => 6,
                             'birth record' => 7,
                             'will' => 8,
                             'obituary' => 9,
                             'protocol' => 10);
        $languageChoices = array(   'undefined' => null,
                                    'Swedish' => 1,
                                    'English' => 2,
                                    'Latin' => 3,
                                    'French' => 4,
                                    'German' => 5,
                                    'Dutch' => 6);
        $builder
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
            )))
            ->add('type', ChoiceType::class, array(
                "choices" => $typeChoices))
            ->add('language', ChoiceType::class, array(
                "choices" => $languageChoices))
            ->add('text_date', TextType::class, array('label' => 'Creation date'))
            ->add ('transcription', TextareaType::class, array(
                'attr' => array('class' => 'tinymce', 'style' => 'height:602px;')))
            ->add ('research_notes', TextareaType::class, array(
                'attr' => array('class' => 'tinymce', 'style' => 'height:602px;')))
            ->add ('excerpt', TextareaType::class, array(
                'attr' => array('class' => 'tinymce', 'style' => 'height:602px;')))

            ->add('actions', CollectionType::class, array(
                'entry_type' => ActionType::class,
                'entry_options' => array('label' => false),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('sourceTopics', CollectionType::class, array(
                'entry_type' => SourceTopicType::class,
                'entry_options' => array('label' => false),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('mentions', CollectionType::class, array(
                'entry_type' => MentionType::class,
                'entry_options' => array('label' => false),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ))
            ->add('file', PdfFileType::class, array(
                'label' => false
            ))
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