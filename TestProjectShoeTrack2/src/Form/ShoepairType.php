<?php

namespace App\Form;

use App\Entity\Shoepair;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShoepairType extends AbstractType
{
    private Security $sec;

    public function __construct(Security $sec)
    {
        $this->sec = $sec;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // obtenir l'user
        $user = $this->sec->getUser();

        $builder
            ->add('nameBrandModel')
            ->add('startDateOfUse', null, [
                'widget' => 'single_text',
            ])
            ->add('wearLimitKm')
            ->add('currentWearKm')
            ->add('shoeNote')
            ->add('inActiveService')
            ->add('userOwner', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'query_builder'=> function(UserRepository $er) use ($user){
                    return $er->createQueryBuilder('s')
                    ->where('s.id = :connectedUser')
                    ->setParameter('connectedUser', $user);
                }

            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Shoepair::class,
        ]);
    }
}
