<?php

namespace App\Form\Type;

use Aropixel\PageBundle\Form\Type\AbstractJsonPageType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ContactPageType extends AbstractJsonPageType
{
    protected function buildCustomForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('phone', TextType::class, ['label' => 'contact.form.phone'])
            ->add('address', TextType::class, ['label' => 'contact.form.address'])
        ;
    }

    public function getType(): string
    {
        return 'contact';
    }
}
