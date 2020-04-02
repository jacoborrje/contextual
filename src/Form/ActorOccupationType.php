<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Entity\ActorOccupation;
use App\Entity\Occupation;
use App\Entity\Institution;
use App\Form\Type\InstitutionAutocompleteType;
use App\Form\Type\OccupationAutocompleteType;
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


class ActorOccupationType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('occupation', OccupationAutocompleteType::class)
            ->add('occupation_text', TextType::class, array('label' => 'Occupation'))
            ->add('institution', InstitutionAutocompleteType::class)
            ->add('institution_text', TextType::class, array('label' => 'Institution'))
            ->add('text_start_date', TextType::class, array('label' => 'Start date'))
            ->add('text_end_date', TextType::class, array('label' => 'End date'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ActorOccupation::class,
        ));
    }
}