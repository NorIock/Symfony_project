<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Entity\Users;
use App\Form\FavoriteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class FavoriteController extends AbstractController
{
    /**
     * @Route("/movie/{page}/{id}", name="favorite", defaults={"page":1})
     */
    // public function addToFavorite(Request $request, $id)
    // {
    //     if (isset($user_id))
    //     {
    //         $user_id = $this->getUser()->getId();
    //     }

    //     $form = $this->createForm(FavoriteType::class);


    //     return $this->render('movie/show.html.twig', [
    //         'favoriteform' => $form->createView(),
    //     ]);
    // }
}
