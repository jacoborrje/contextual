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
use App\Form\Type\HiddenActorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class FoundPlaceType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typeChoices = array('Author place' => 1, 'Recipient place' => 2, 'mention' => 3);

        $builder
            ->add('placeId', HiddenType::class)
            ->add('startPos', HiddenType::class)
            ->add('endPos', HiddenType::class)
            ->add('type', ChoiceType::class, array(
                "choices" => $typeChoices,
                "label" => false,
                'required' => false));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
        ));
    }

}