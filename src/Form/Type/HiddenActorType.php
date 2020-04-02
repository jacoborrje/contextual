<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-02-22
 * Time: 11:49
 */

namespace App\Form\Type;


use App\Form\DataTransformer\HiddenToActorTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Actor;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HiddenActorType extends AbstractType
{
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $hiddenToActorTransformer = new HiddenToActorTransformer($this->em);
        $builder->addModelTransformer($hiddenToActorTransformer);
    }

    public function getParent() {
        return HiddenType::class;
    }

    public function getName() {
        return 'HiddenActor';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Actor::class,
        ));
    }

}