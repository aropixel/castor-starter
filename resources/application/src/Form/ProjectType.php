<?php

namespace App\Form;

use Aropixel\AdminBundle\Form\Type\EditorType;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\AdminBundle\Form\Type\Page\PublishableType;
use App\Entity\Project;
use App\Entity\ProjectImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'project.form.title'])
            ->add('description', EditorType::class, ['label' => 'project.form.description', 'required' => false])
            ->add('publishable', PublishableType::class, ['mapped' => false, 'inherit_data' => true])
            ->add('image', ImageType::class, [
                'label' => 'project.form.image',
                'data_class' => ProjectImage::class,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Project::class]);
    }
}
