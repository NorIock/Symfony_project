<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MovieController extends AbstractController
{

    /**
     * @Route("/movie/search/{page}", name = "search", defaults={"page":1})
     */

    public function search(Request $request, $page): Response
    {
        $form= $this->createFormBuilder()
            ->add('Search', TextType::class, ['attr' => ['autofocus' => true]])
            ->add('Select', ChoiceType::class, [
                'choices' => [
                    'Title' => 'title',
                    'Actor' => 'actor',
                    'Year' => 'year',
                    'Minimun rate' => 'minrate',
                    'Max rate' => 'maxrate'
                ]
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            // $data = $form->getData()["Search"];
            return $this->searchBar($data, $form, $page);
            // dd($form->getData());
        } 
        else
        {
            
            return $this->render('movie/search.html.twig', [
                'search_form'=>$form->createView(),
            ]);

        }
    }

        
    /**
     * @Route("/movie/search/{page}", name = "search_movie", defaults={"page":1})
     */

    public function searchBar($data, $form, $page)
    {


        $client = HttpClient::create();

        if($data['Select'] == 'title')
        {
            $response = $client->request('GET', "https://api.themoviedb.org/3/search/movie?api_key=e7a0f8037bd3c9cef786ec68557daf5f&language=en-US&query=" . $data['Search'] . "&page=" . $page . "&include_adult=false");
        }

        else if($data['Select'] == 'actor')
        {
            $response = $client->request('GET', "https://api.themoviedb.org/3/search/person?api_key=e7a0f8037bd3c9cef786ec68557daf5f&language=en-US&query=" . $data['Search'] . "&page=" . $page . "&include_adult=false");
        }

        else if($data['Select'] == 'year')
        {
            $response = $client->request('GET', "https://api.themoviedb.org/3/discover/movie?api_key=e7a0f8037bd3c9cef786ec68557daf5f&language=en-US&sort_by=popularity.desc&include_adult=false&include_video=false&page=" . $page . "&primary_release_year=" . $data['Search']);
        }

        else if($data['Select'] == 'minrate')
        {
            $response = $client->request('GET', "https://api.themoviedb.org/3/discover/movie?api_key=e7a0f8037bd3c9cef786ec68557daf5f&language=en-US&sort_by=popularity.desc&include_adult=false&include_video=false&page=" .$page . "&vote_average.gte=" . $data['Search']);
        }

        else if($data['Select'] == 'maxrate')
        {
            $response = $client->request('GET', "https://api.themoviedb.org/3/discover/movie?api_key=e7a0f8037bd3c9cef786ec68557daf5f&language=en-US&sort_by=popularity.desc&include_adult=false&include_video=false&page=" .$page . "&vote_average.lte=" . $data['Search']);
        }

        $client = HttpClient::create();


        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $content = $response->toArray();
        // dd($content);

        $number_page = $this->pagination($page);

        return $this->render('movie/search.html.twig', [
            'results'=>$content['results'],
            'pages'=>$number_page,
            'data'=>$data,
            'search_form'=>$form->createView()
        ]);
    }
    
    /**
     * @Route("/movie/{page}", name="movie", defaults={"page":1})
     */

    public function discover(int $page)
    {

        $client = HttpClient::create();
        $response = $client->request('GET', "https://api.themoviedb.org/3/discover/movie?api_key=e7a0f8037bd3c9cef786ec68557daf5f&language=en-US&sort_by=popularity.desc&include_adult=false&include_video=false&page=" . $page);
        $client = HttpClient::create();


        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $content = $response->toArray();

        $number_page = $this->pagination($page);

        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
            'movies'=>$content['results'],
            'pages'=>$number_page,
        ]);
    }

    /**
     * @Route("/movie/{page}", name="pagination", defaults={"page":1})
     */

    public function pagination($page)
    {
        if ($page == 1)
        {
            $page_array = array($page, $page + 1, $page +2, $page + 3, $page +4);
        }

        else if ($page == 2)
        {
            $page_array = array($page - 1, $page, $page +1, $page + 2, $page + 3);
        }

        else if ($page > 2 && $page <= 1000)
        {
            $page_array = array($page - 2, $page -1, $page, $page + 1, $page + 2);
        }

        else
        {
            return $this->pagination(1);
        }

        // dd($page_array);

        return $page_array;
    }

    /**
     * @Route("/actor/{name}", name="actor_show")
     */
    
    public function actor_show($name)
    {

        //Détails sur les acteurs principaux, boucle for (dans twig) pour avoir les 3 premiers et non pas la totalité

        $client_bis = HttpClient::create();
        $response_bis = $client_bis->request('GET', "https://api.themoviedb.org/3/search/person?api_key=e7a0f8037bd3c9cef786ec68557daf5f&language=en-US&query=" . $name . "&page=1&include_adult=false");
        $client_bis = HttpClient::create();
        
        $statusCode_bis = $response_bis->getStatusCode();
        $content_bis = $response_bis->getContent();
        $content_bis = $response_bis->toArray();

        // dd($content_bis);
        $id = $content_bis['results'][0]['id'];

        //Détails sur l'acteur ou actrice
        $client = HttpClient::create();
        $response = $client->request('GET', "https://api.themoviedb.org/3/person/" . $id . "?api_key=e7a0f8037bd3c9cef786ec68557daf5f&language=en-US&page=1&include_adult=false");
        
                $statusCode = $response->getStatusCode();
                $content = $response->getContent();
                $content = $response->toArray();

        // dd($content_bis);

         return $this->render('movie/showActor.html.twig', [
             'controller_name' => 'MovieController',
             'profile'=>$content,
             'movies'=>$content_bis['results'],
             'actor_id'=>$id,
         ]);
    }

     /**
     * @Route("/movie/{page}/{id}", name="movie_show", defaults={"page":1})
     */
    
     public function movie_show($page, $id)
    {
        //Détails sur le film
        $client = HttpClient::create();
        $response = $client->request('GET', "https://api.themoviedb.org/3/movie/" . $id . "?api_key=e7a0f8037bd3c9cef786ec68557daf5f&language=en-US");
        $client = HttpClient::create();

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $content = $response->toArray();

        //Détails sur les acteurs principaux, boucle for (dans twig) pour avoir les 3 premiers et non pas la totalité

        $client_bis = HttpClient::create();
        $response_bis = $client_bis->request('GET', "https://api.themoviedb.org/3/movie/" . $id . "/credits?api_key=e7a0f8037bd3c9cef786ec68557daf5f");
        $client_bis = HttpClient::create();

        $statusCode_bis = $response_bis->getStatusCode();
        $content_bis = $response_bis->getContent();
        $content_bis = $response_bis->toArray();

        // dd($content);

         return $this->render('movie/show.html.twig', [
             'controller_name' => 'MovieController',
             'show'=>$content,
             'casts'=>$content_bis['cast']
         ]);
    }



    /**
     * @Route("/", name = "home")
     */

    public function home()
    {
        return $this->render('movie/home.html.twig');
    }
}