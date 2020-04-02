<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-02-22
 * Time: 11:49
 */

namespace App\Form\Type;


use App\Form\DataTransformer\ActorAutocompleteTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;

class RelationshipActorType extends AbstractType
{
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $autocompleteTransformer = new ActorAutocompleteTransformer($this->em);

        $builder
            ->addModelTransformer($autocompleteTransformer)
        ;
    }

    public function getParent() {
        return TextType::class;
    }

    public function getName() {
        return 'RelationshipActor';
    }

}