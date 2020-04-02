<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\SourceTopic;
use App\Entity\Correspondent;
use App\Entity\Topic;
use App\Form\DataTransformer\HiddenToTopicTransformer;
use App\Form\Type\NewSourceTopicTopicType;
use App\Form\Type\SourceTopicTopicType;
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
use App\Form\DataTransformer\TopicAutocompleteTransformer;
use App\Form\EventListener;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class SourceTopicType extends \Symfony\Component\Form\AbstractType
{

    public function __construct(TopicAutocompleteTransformer $transformer)
    {
        $this->transfomer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('topic', SourceTopicTopicType::class, array(
                        'label' => false
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SourceTopic::class,
        ));
    }
}