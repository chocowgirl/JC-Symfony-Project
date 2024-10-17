<?php

namespace App\Controller;

use App\Entity\Shoepair;
use App\Form\ShoepairType;
use App\Repository\ShoepairRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ShoeManagerController extends AbstractController
{
    #[Route('/shoe/manager', name: 'app_shoe_manager')]
    public function indexShoes(ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $rep = $em->getRepository(Shoepair::class);

        //notez que findBy renverra toujours in array même s'il trouve
        //qu'un objet
        $shoes = $rep->findBy(
            ['userOwner' => $this->getUser()],
            ["currentWearKm" => "DESC"],
        );
        $vars = ['shoes' => $shoes];

        return $this->render('shoe_manager/index_shoes.html.twig', $vars);
    }

    #[Route("/shoe/manager/update/{id}", name: 'update_shoe')]
    public function updateShoe(
        ShoepairRepository $rep,
        Request $req,
        ManagerRegistry $doctrine
    ): Response {
        // on obtient d'abord le shoepair
        $shoepair = $rep->find($req->get('id'));
        // $userOwner = $this->getUser();
        $form = $this->createForm(
            ShoepairType::class,
            $shoepair,
        );

        $form->handleRequest($req);
        $vars = ['form' => $form];
        // dd($form->getErrors());
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($shoepair);
            $doctrine->getManager()->flush();

            return $this->redirectToRoute("app_shoe_manager");
        }
        return $this->render("shoe_manager/edit_shoe.html.twig", $vars);
    }


    #[Route("/shoe/manager/create", name: 'create_shoe')]
    public function createShoe(
        ShoepairRepository $rep,
        Request $req,
        ManagerRegistry $doctrine
    ): Response {

        
        // we first create the shoe
        $shoepair = new Shoepair();
        
        $form = $this->createForm(ShoepairType::class, $shoepair);
        //do I need to pass through the user?
        $form->handleRequest($req);

        $vars = ['form' => $form];

        // dd($form->getErrors());

        if ($form->isSubmitted() && $form->isValid()) {
            // dd();
            $doctrine->getManager()->persist($shoepair);
            $doctrine->getManager()->flush();

            return $this->redirectToRoute("app_shoe_manager");
        }
        return $this->render("shoe_manager/create_shoe.html.twig", $vars);
    }




}
