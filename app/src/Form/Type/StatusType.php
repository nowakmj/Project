<?php
/**
 * Status type.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class StatusType.
 */
class StatusType extends AbstractType
{
    /**
     * Build the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('changeStatus', SubmitType::class, [
            'label' => 'label.status',
        ]);
    }
}
