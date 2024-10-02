<?php

namespace App\Controller;
use App\Entity\Post;
use App\Entity\Rubrik;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use App\Repository\RubrikRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PostController extends AbstractController
{
    private $repo;
    private $emi;

    public function __construct(PostRepository $repo, EntityManagerInterface $emi)
    {
        $this->repo = $repo;
        $this->emi = $emi;
    }

    // Gestion de l'affichage de la page d'accueil (index)
    #[Route('/', name: 'app_post')]
    public function index(): Response
    {
        $posts = $this->repo->findBy([], ['createdAt' => 'DESC'], 4);

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }
 
      //GESTION DE LA RECUPERATION DU DETAIL D'UN POST(TOTALITE D'UN POST)+Commentaire
    
      #[Route('/post/{id}', name: 'show', requirements: ['id' => '\d+'])]
      public function showone(Post $post, Request $req, CommentRepository $crepo): Response
      {
          // Récupération des commentaires du post pour affichage
          $allComments = $crepo->findByPostOrderedByCreatedAtDesc($post->getId());
      
          // Instancier la classe Comment
          $comment = new Comment();
          
          // Créer le formulaire commentaire
          $commentForm = $this->createForm(CommentFormType::class, $comment);
          
          // Vérifier si l'utilisateur est connecté
          if ($this->getUser()) {
              // Soumettre le commentaire via le formulaire
              $commentForm->handleRequest($req);
              
              // Traitement du formulaire de commentaire soumis
              if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                  $comment->setUser($this->getUser());
                  $comment->setPost($post);
                  $comment->setCreatedAt(new \DateTimeImmutable());
                  
                  // Persister le commentaire (l'enregistrer dans la BDD)
                  $this->emi->persist($comment);
                  $this->emi->flush();
                  
                  // Rediriger pour éviter la resoumission du formulaire
                  return $this->redirectToRoute('show', ['id' => $post->getId()]);
              }
          }
          
          // Rendre la vue avec les données appropriées
          return $this->render('show/show.html.twig', [
              'post' => $post,
              'comments' => $allComments,
              'comment_form' => $this->getUser() ? $commentForm->createView() : null,
          ]);
      }

    #[Route('/rubrik/rubrik/{id}', name: 'posts_by_rubrik')]
    public function postsByRubrik(Rubrik $rubrik, $id, PostRepository $postRepository, RubrikRepository $rubrikRepository): Response
    {
        $rubrik = $rubrikRepository->find($id);
        if (!$rubrik) {
            throw $this->createNotFoundException('rubrik not found');
        }
    
        $posts = $postRepository->findByRubrik($rubrik);
        
       
            return $this->render('rubrik/rubrik.html.twig', [
                'rubrik' => $rubrik,
                'posts' => $posts
            ]);
        }
}