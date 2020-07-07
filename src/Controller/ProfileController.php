<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UpdateUserType;
use app\Controller\SecurityController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;




class ProfileController extends AbstractController
{

    /**
     * @Route("/profile/display_profile", name="show_profile")
     */
    public function displayUserInfo()
    {
        $user_id = $this->getUser()->getId();   
        // $user_name = $this->getUser()->getName();
        // $user_mail = $this->getUser()->getEmail();

        // dd($user_id);

        $repo = $this->getDoctrine()->getRepository(Users::class);

        $user = $repo->find($user_id);



        return $this->render('profile/display_profile.html.twig', [
            // 'user_id'=>$user_id,
            // 'user_name'=>$user_name,
            'data'=>$user,
        ]);
    }

    /**
     * @Route("/profile/update_user", name="update_user")
     */
    public function updateUser(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user_id = $this->getUser()->getId();  
        // dd($user_id);

        $entityManager = $this->getDoctrine()->getManager();
        $update = $entityManager->getRepository(Users::class)->find($user_id);


        $form = $this->createForm(UpdateUserType::class, $update);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $hash = $encoder->encodePassword($update, $update->getPassword());
            $update->setPassword($hash);
            $entityManager->flush();

            return $this->redirectToRoute('show_profile');
        }

        return $this->render('profile/update_user.html.twig', [
            'form' => $form->createView(),
            'userdata'=>$update,
        ]);
    }

    /**
     * @Route("/profile/delete_user", name="delete_user")
     */
    public function deleteUser(Request $request)
    {
        $session = new Session();

        $form=$this->createFormBuilder()
            ->add('submit', SubmitType::class, ['label' => 'I want to delete my profile'])
            ->getForm()
            ;
            
        $user_id = $this->getUser()->getId();
            
        $entityManager = $this->getDoctrine()->getManager();
        $delete = $entityManager->getRepository(Users::class)->find($user_id);
            
        $form->handleRequest($request);

        if($form->isSubmitted())
        {
            $session->invalidate();
            $entityManager->remove($delete);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('profile/delete_user.html.twig', [
            'delete_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile", name = "profile")
     */

    public function home()
    {
        return $this->render('profile/index.html.twig');
    }
}
