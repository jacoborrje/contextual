<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-02-22
 * Time: 11:49
 */

namespace App\Form\Type;


use App\Form\DataTransformer\HiddenToTopicTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;

class SourceTopicTopicType extends AbstractType
{
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $hiddenToTopicTransformer = new HiddenToTopicTransformer($this->em);
        $builder->addModelTransformer($hiddenToTopicTransformer);
    }

    public function getParent() {
        return HiddenType::class;
    }

    public function getName() {
        return 'SourceTopicTopic';
    }

}