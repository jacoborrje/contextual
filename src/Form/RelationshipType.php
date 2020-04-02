<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\Relationship;
use App\Entity\Actor;
use App\Form\Type\ActorAutocompleteType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Form\EventListener\TypeFieldSubscriber;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Form\DataTransformer\ActorAutocompleteTransformer;
use App\Form\Type\RelationshipActorType;


class RelationshipType extends \Symfony\Component\Form\AbstractType
{
    private $transformer;

    public function __construct(ActorAutocompleteTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new TypeFieldSubscriber())
                ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                    $relationship = $event->getData();
                    $form = $event->getForm();
                    if (!$relationship || null === $relationship->getId()) {
                        $form->add('actor2', ActorAutocompleteType::class, array(
                            'label' => 'Actor',
                            'attr' => array('class'=> "actorRelationshipInput")
                        ));
                        $form->add('actor2_text', TextType::class);
                    }
                })
                ->add('text_start_date', TextType::class, array('label' => 'Start date'))
                ->add('text_end_date', TextType::class, array('label' => 'End date'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Relationship::class,
        ));
    }
}