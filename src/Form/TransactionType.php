<?php

namespace App\Form;

use App\Entity\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Income' => 'income',
                    'Expense' => 'expense',
                ],
                'label' => 'Type'
            ])
            ->add('currency', ChoiceType::class, [
                'choices' => [
                    'USD' => 'USD',
                    'CLP' => 'CLP',
                ],
                'label' => 'Currency'
            ])
            ->add('amount', MoneyType::class, [
                'currency' => 'USD',
                'label' => 'Amount',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'El monto no puede estar vacío',
                    ]),
                    new Assert\LessThanOrEqual([
                        'value' => 99999999.99, 
                        'message' => 'El monto debe ser menor o igual a 99,999,999.99 USD',
                    ]),
                ],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date'
            ]);

        //  evento para cambiar la moneda de dolar a peso chileno
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            if (isset($data['currency'])) {
                $form->add('amount', MoneyType::class, [
                    'currency' => $data['currency'],
                    'label' => 'Amount'
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
