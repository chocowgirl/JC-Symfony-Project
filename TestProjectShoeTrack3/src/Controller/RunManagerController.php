<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Activity;
use App\Entity\Shoepair;
use App\Form\ActivityType;
use App\Form\SearchRunsType;
use App\Repository\ActivityRepository;
use App\Repository\ShoepairRepository;
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
        ShoepairRepository $shoepairRep,
        Request $req,
        ManagerRegistry $doctrine
    ): Response {
        // on obtient d'abord le run
        $activity = $rep->find($req->get('id'));
        //store the original run distance
        $originalDistance = $activity->getActivityDistanceKm();
        //get the shoe used for the activity
        $shoepair = $activity->getShoepairUsed();
        
        $user = $this->getUser();
        $form = $this->createForm(
            ActivityType::class,
            $activity,
        );

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the updated distance from the form
            $newDistance = $activity->getActivityDistanceKm();
    
            if ($shoepair) {
                $currentWear = $shoepair->getCurrentWearKm();
    
                // First, subtract the original distance from the shoe's current wear
                $newWearKm = $currentWear - $originalDistance;
    
                // Then, add the updated distance to the shoe's wear
                $newWearKm += $newDistance;
    
                // OPTIONAL VALIDATION: Ensure the new wear does not exceed the wear limit
                if ($newWearKm > $shoepair->getWearLimitKm()) {
                    // Handle case where the shoe exceeds the wear limit
                    $this->addFlash('warning', 'This shoe has exceeded its wear limit!');
    
                    // Optionally redirect back to the form without saving changes
                    return $this->redirectToRoute('update_activity', ['id' => $activity->getId()]);
                }
    
                // Update the shoe's current wear with the adjusted value
                $shoepair->setCurrentWearKm($newWearKm);
    
                // Persist the changes to both the activity and the shoepair
                $em = $doctrine->getManager();
                $em->persist($activity); // Persist the updated activity
                $em->persist($shoepair); // Persist the updated shoepair
                $em->flush();
    
                // Redirect after successful update
                return $this->redirectToRoute("app_run_manager");
            }
        }
    
        // If the form is not submitted or valid, show the form again
        return $this->render("run_manager/edit_run.html.twig", [
            'form' => $form->createView(),
        ]);
    }


    //     $vars = ['form' => $form];
    //     // dd($form->getErrors());
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $doctrine->getManager()->flush();

    //         return $this->redirectToRoute("app_run_manager");
    //     }
    //     return $this->render("run_manager/edit_run.html.twig", $vars);
    // }

    #[Route("/run/manager/delete/{id}", name: 'delete_activity')]
    public function deleteActivity(
        ActivityRepository $rep,
        ShoepairRepository $shoepairRep,
        Request $req,
        ManagerRegistry $doctrine
    ): Response {
        // Retrieve the activity by ID
        $activity = $rep->find($req->get('id'));

        // Check if the activity exists
        if (!$activity) {
            throw $this->createNotFoundException('No activity found for id ' . $req->get('id'));
        }
        // Retrieve the shoe associated with the activity
        $shoepair = $activity->getShoepairUsed();
        // Get the activity distance
        $activityDistance = $activity->getActivityDistanceKm();  

        // If the shoe is associated with the activity
        if ($shoepair) {
            // Subtract the activity distance from the shoe's current wear
            $newWearKm = $shoepair->getCurrentWearKm() - $activityDistance;

            // Ensure the new wear value is not negative (just in case)
            $newWearKm = max(0, $newWearKm);

            // Update the shoe's current wear
            $shoepair->setCurrentWearKm($newWearKm);
        }

        // Use the EntityManager to remove the activity
        $entityManager = $doctrine->getManager();
        $entityManager->remove($activity);
        // Persist the updated shoepair (if it exists)
        if ($shoepair) {
            $entityManager->persist($shoepair);
        }

        // Apply the delete operation to the database
        $entityManager->flush();  
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

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            
            // Get the shoepair used in the activity
            /** @var Shoepair $shoepair */
            $shoepair = $activity->getShoepairUsed();
    
            if ($shoepair) {
                // Add the distance of the current activity to the current wear of the shoe
                $currentWear = $shoepair->getCurrentWearKm();
                $activityDistance = $activity->getActivityDistanceKm();
    
                // Update the current wear km for the shoe
                $shoepair->setCurrentWearKm($currentWear + $activityDistance);
    
                // Persist changes to both activity and shoepair
                $em->persist($activity);
                $em->persist($shoepair);
                $em->flush();
    
                // Redirect after successfully saving
                return $this->redirectToRoute("app_run_manager");
            }
        }
        // If form isn't submitted or isn't valid, show the form again
        return $this->render("run_manager/create_run.html.twig", [
            'form' => $form->createView(),
        ]);
    }
    //     $vars = ['form' => $form];

    //     // dd($form->getErrors());

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $doctrine->getManager()->persist($activity);
    //         $doctrine->getManager()->flush();

    //         return $this->redirectToRoute("app_run_manager");
    //     }
    //     return $this->render("run_manager/create_run.html.twig", $vars);
    // }


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

        // if submit: get form data and recall the SAME action (redirect). we send it the form data
        if ($form->isSubmitted() && $form->isValid()) {
            $dataForm = $form->getData();
            $dataArray['startDate'] = $dataForm['startDate'];
            $dataArray['endDate'] = $dataForm['endDate'];
            $dataArray['shoe'] = $dataForm['shoe']?->getId();

            $jsonData = json_encode($dataArray);
            $vars = ['jsonData' => $jsonData];
            // dd($vars);
            return $this->redirectToRoute('search_activities', $vars);
        }

        // init 
        $activities = [];
        $totalRuns = 0;
        $totalDistance = 0;
        $totalDuration = 0;
        $averageDistance = 0;
        $averageSpeedPerKm = 0;

        // verify if we have sent form data
        // $val = $request->get('startDate');
        // if ($val instanceof DateTime){
        //     dd($val);
        // }
        $jsonData = $request->get('jsonData');
        if ($jsonData) {
            // decode form filters; received en GET            
            $data = json_decode($jsonData);

            // dd ($data->startDate);
            $startDate = new DateTime($data->startDate->date, new DateTimeZone($data->startDate->timezone));
            $endDate = new DateTime($data->endDate->date, new DateTimeZone($data->endDate->timezone));
            $shoe = $data->shoe;
            
            // Convert end date to include full day
            // $endDate = $endDate->setTime(23, 59, 59);

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
                    ->setParameter('shoeId', $shoe); // $shoe contains the id, we didn't pass the object in the redirect
            }

            

            // dd($qb->getQuery()->getSql());
            $activities = $qb->getQuery()->getResult();
            // dd($activities);


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
            if ($totalRuns > 0) {
                $averageDistance = ($totalDistance / $totalRuns);
            } else {
                $averageDistance = 0;
            }
            if ($totalDistance > 0) {
                $averageSpeedPerKm = ($totalDuration / $totalDistance);
            } else {
                $averageSpeedPerKm = 0;
            }

            // $averageDistance = $totalDistance / $totalRuns;
            // $averageSpeedPerKm = $totalDuration / $totalDistance;

            // $vars = ['form' => $form->createView(),
            //         'activities' => $activities,
            //         'totalRuns' => $totalRuns,
            //         'totalDistance' => $totalDistance,
            //         'averageDistance' => $averageDistance,
            //         'averageSpeedPerKm' => $averageSpeedPerKm,
            //         ];

            // return $this->render('run_manager/stats_runs.html.twig', $vars);


            // dump($totalRuns);
            // dump($averageDistance);
            // dump($averageSpeedPerKm);
            // dd($shoe[nameBrandModel]);

            // Render the form and results


        }

        // in every case, load the same twig.
        // it will show the form and the results if they exist


        return $this->render('run_manager/stats_runs.html.twig', [
            'form' => $form->createView(),
            // 'activities' => $activities,
            // 'startDate' => $startDate,
            // 'endDate' => $endDate,
            'totalRuns' => $totalRuns,
            'totalDistance' => $totalDistance,
            'averageDistance' => $averageDistance,
            'averageSpeedPerKm' => $averageSpeedPerKm,
            // 'test' => 'test',
        ]);
    }


    // #[Route('/run/manager/stats/show', name: 'show_run_stats')]
    // public function showStats(Request $req)
    // {
    //     // dd($req->get('totalRuns'));
    //     return $this->render('run_manager/stats_runs_show.html.twig', [
    //         'totalRuns' => $req->get("totalRuns"),
    //         'totalDistance' => $req->get("totalDistance"),
    //         'averageDistance' => $req->get("averageDistance"),
    //         'averageSpeedPerKm' => $req->get("averageSpeedPerKm"),
    //     ]);
    // }
}
