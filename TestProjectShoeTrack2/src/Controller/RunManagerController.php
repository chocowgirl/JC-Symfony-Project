<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Shoepair;
use App\Form\ActivityType;
use App\Form\SearchRunsType;
use App\Repository\ActivityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RunManagerController extends AbstractController
{

    #[Route('/run/manager', name: 'app_run_manager')]
    public function indexRuns(ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $rep = $em->getRepository(Activity::class);

        // notez que findBy renverra toujours un array même s'il trouve qu'un objet
        $activities = $rep->findBy(
            ['user' => $this->getUser()],
            ["activityDate" => "DESC"]
        );
        $vars = ['activities' => $activities];

        return $this->render('run_manager/index_runs.html.twig', $vars);
    }

    #[Route("/run/manager/update/{id}", name: 'update_activity')]
    public function updateActivity(
        ActivityRepository $rep,
        Request $req,
        ManagerRegistry $doctrine
    ): Response {
        // on obtient d'abord le run
        $activity = $rep->find($req->get('id'));
        $user = $this->getUser();
        $form = $this->createForm(ActivityType::class, $activity,
    );

        $form->handleRequest($req);
        $vars = ['form' => $form];
        // dd($form->getErrors());
        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();

            return $this->redirectToRoute("app_run_manager");
        }
        return $this->render("run_manager/edit_run.html.twig", $vars);
    }

    #[Route("/run/manager/delete/{id}", name: 'delete_activity')]
    public function deleteActivity(
        ActivityRepository $rep,
        Request $req,
        ManagerRegistry $doctrine
    ): Response {
        // Retrieve the activity by ID
        $activity = $rep->find($req->get('id'));
    
        // Check if the activity exists
        if (!$activity) {
            throw $this->createNotFoundException('No activity found for id ' . $req->get('id'));
        }
        // Use the EntityManager to remove the activity
        $entityManager = $doctrine->getManager();
        $entityManager->remove($activity);
        $entityManager->flush();  // Apply the delete operation to the database
        // Redirect to a specific route after deletion
        return $this->redirectToRoute("app_run_manager");
    }

    #[Route("/run/manager/create", name: 'create_activity')]
    public function createActivity(
        ActivityRepository $rep,
        Request $req,
        ManagerRegistry $doctrine
    ): Response {

        // we first create the run
        $activity = new Activity();

        $form = $this->createForm(ActivityType::class, $activity);
        //do I need to pass through the user?
        $form->handleRequest($req);

        $vars = ['form' => $form];

        // dd($form->getErrors());

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->persist($activity);
            $doctrine->getManager()->flush();

            return $this->redirectToRoute("app_run_manager");
        }
        return $this->render("run_manager/create_run.html.twig", $vars);
    }

    
    #[Route('/run/manager/stats', name: 'search_activities')]
    public function searchRuns(
        Request $request, 
        ActivityRepository $rep, 
        ManagerRegistry $doctrine
        ): Response {
        $em = $doctrine->getManager();
        $rep = $em->getRepository(Activity::class);

        // Fetch the user's shoes (assuming there's a relation between User and Shoe entities)
        $user = $this->getUser();
        $shoes = $em->getRepository(Shoepair::class)->findBy(['userOwner' => $user]);

        // Create the form and pass the user's shoes as an option
        $form = $this->createForm(SearchRunsType::class, null, ['shoes' => $shoes]); 
        $form->handleRequest($request);

        $activities = [];
        $totalRuns = 0;
        $totalDistance = 0;
        $totalDuration = 0;
        $averageDistance = 0;
        $averageSpeedPerKm = 0;

        // Handle form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Fetch form data
            $data = $form->getData();
            $startDate = $data['startDate'];
            $endDate = $data['endDate'];
            $shoe = $data['shoe'];

            // Convert end date to include full day
            $endDate = $endDate->setTime(23, 59, 59);

            // Build the query for activities within the date range
            $qb = $rep->createQueryBuilder('a')
                ->where('a.user = :user')
                ->andWhere('a.activityDate BETWEEN :startDate AND :endDate')
                ->setParameter('user', $user)
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);

            // Add shoe filter if a specific shoe is selected
            if ($shoe) {
                $qb->andWhere('a.shoepairUsed = :shoeId')
                ->setParameter('shoeId', $shoe->getId());
            }

            $activities = $qb->getQuery()->getResult();

            // Calculate total runs, distance, and averages
            $totalRuns = count($activities);


            // dd($activities);  WORKS HERE
            // dd($totalRuns);  WORKS HERE 

            foreach ($activities as $activity) {
                $totalDistance += $activity->getActivityDistanceKm();
                $totalDuration += $activity->getActivityChronoMin();
            }
            // dd($totalDistance);  WORKS HERE
            // dd($totalDuration);  WORKS HERE
            // dd($totalRuns);  WORKS HERE

            // // Calculate averages, ensuring no division by zero
            // if ($totalRuns > 0) {
            //     $averageDistance = ($totalDistance / $totalRuns);
            // // }else {$averageDistance = 0;
            // }
            // if ($totalDistance > 0) {
            //     $averageSpeedPerKm = ($totalDuration / $totalDistance);
            // }
            // else {$averageSpeedPerKm = 0;
            // }
            $averageDistance = $totalDistance / $totalRuns;
            $averageSpeedPerKm = $totalDuration / $totalDistance;

            // $vars = ['form' => $form->createView(),
            //         'activities' => $activities,
            //         'totalRuns' => $totalRuns,
            //         'totalDistance' => $totalDistance,
            //         'averageDistance' => $averageDistance,
            //         'averageSpeedPerKm' => $averageSpeedPerKm,
            //         ];

            // return $this->render('run_manager/stats_runs.html.twig', $vars);


        // dd($totalRuns);
        // dd($averageDistance);
        dd($averageSpeedPerKm);
        // Render the form and results
            return $this->render('run_manager/stats_runs.html.twig', [
                'form' => $form->createView(),
                // 'activities' => $activities,
                'totalRuns' => $totalRuns,
                'totalDistance' => $totalDistance,
                'averageDistance' => $averageDistance,
                'averageSpeedPerKm' => $averageSpeedPerKm,
                'test' => ['t', 'es'],
            ]);
        }


        return $this->render('run_manager/stats_runs.html.twig', [
            'form' => $form->createView(),
            // 'activities' => $activities,
            'totalRuns' => $totalRuns,
            'totalDistance' => $totalDistance,
            'averageDistance' => $averageDistance,
            'averageSpeedPerKm' => $averageSpeedPerKm,
            'test' => 'test',
        ]);
    

    }







}