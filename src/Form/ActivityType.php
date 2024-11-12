<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Activity;
use App\Entity\Shoepair;
use App\Repository\ShoepairRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ActivityType extends AbstractType
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
            ->add('activityDate', DateType::class)
            // , [
            //     'widget' => 'choice',
            //     'format' => 'yyyy/MM/dd', // Sets the desired format
            // ])
            ->add('activityDistanceKm')
            ->add('activityNote')
            ->add('activityChronoMin')
            // ->add('user', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'name',
            //     'query_builder'=> function(UserRepository $er) use ($user){
            //         return $er->createQueryBuilder('s')
            //         ->where('s.id = :connectedUser')
            //         ->setParameter('connectedUser', $user);
            //     }

            // ])
            ->add('shoepairUsed', EntityType::class, [
                'class' => Shoepair::class,
                'choice_label' => 'nameBrandModel',
                'query_builder' => function (ShoepairRepository $er) use ($user) {
                    return $er->createQueryBuilder('s')
                        ->where('s.userOwner = :connectedUser')
                        ->setParameter('connectedUser', $user); 
                },

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
