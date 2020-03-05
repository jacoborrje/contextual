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
use App\Entity\PdfFile;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Entity\File;

class PdfFileType extends AbstractType
{
    private $transformer;

    public function __construct(ArrayToUploadableFileTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileContents', VichFileType::class, [
                    'multiple' => true,
                    'label' => false,
                    'allow_delete' => true,
                    'download_uri' => true,
                    'attr' => array(
                        'class' => "box__file")
                    ]
            );
        $builder->addModelTransformer($this->transformer);
    }


    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PdfFile::class,
        ));
    }
    public function getName()
    {
        return 'PdfFile';
    }

}

?>
