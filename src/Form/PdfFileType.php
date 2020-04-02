<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 15:22
 */

namespace App\Form;

use App\Form\DataTransformer\ArrayToUploadableFileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\DatabaseFile;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class PdfFileType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileContents', FileType::class, [
                    'multiple' => true,
                    'label' => false,
                    'attr' => array(
                        'class' => "box__file")
                    ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => DatabaseFile::class,
        ));
    }

    public function getName()
    {
        return 'PdfFileType';
    }

}

?>
