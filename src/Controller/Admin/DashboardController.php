<?php

namespace App\Controller\Admin;


use App\Entity\User;
use App\Entity\Post;
use App\Entity\Rubrik;
use App\Entity\Comment;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{

    protected $userRepository;

    //mettre en place le contructeur
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        //Definir le rôle minimum à avoir pour accéder au DASHBOARD
        if($this->isGranted('ROLE_EDITOR')){
            return $this->render('admin/dashboard.html.twig');
        }else
        return $this->redirectToRoute('app_post');
    }


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle("Les Ateliers D'Andrée");
    }
    public function configureMenuItems(): iterable
    {
       
        yield MenuItem::linkToRoute('Go To Site', 'fa-solid fa-arrow-rotate-left', 'app_post');

       
        if($this->isGranted('ROLE_ADMIN')){
            yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home')->setPermission('ROLE_SUPER_ADMIN');
        }

        //LES AUTRES ROLE
        
        if($this->isGranted('ROLE_EDITOR')){
            yield MenuItem::section('Posts');
            yield MenuItem::subMenu('Posts', 'fa_sharp fa-solid fa-blog')->setSubItems([
                  MenuItem::linkToCrud('Create Post', 'fas fa-newspaper', Post::class)->setAction(Crud::PAGE_NEW),
                  MenuItem::linkToCrud('Show Post', 'fas fa-eye', Post::class),
            ]);

        }

        if($this->isGranted('ROLE_MODO')){
            yield MenuItem::section('Comments');
            yield MenuItem::subMenu('Comments', 'fa fa-comment-dots')->setSubItems([
                  MenuItem::linkToCrud('Create Comment', 'fas fa-newspaper', Comment::class)->setAction(Crud::PAGE_NEW),
                  MenuItem::linkToCrud('Show Comment', 'fas fa-eye', Comment::class),

            ]);
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('Rubrik');
            yield MenuItem::subMenu('Rubrik', 'fa-solid fa-book-open-reader')->setSubItems([
                  MenuItem::linkToCrud('Create Rubrik', 'fas fa-newspaper', Rubrik::class)->setAction(Crud::PAGE_NEW),
                  MenuItem::linkToCrud('Show Rubrik', 'fas fa-eye', Rubrik::class),
            ]);
        }
        if($this->isGranted('ROLE_SUPER_ADMIN')){
            yield MenuItem::section('User');
            yield MenuItem::subMenu('User', 'fa fa-user-circle')->setSubItems([
                  MenuItem::linkToCrud('Create User', 'fa fa-plus-circle', User::class)->setAction(Crud::PAGE_NEW),
                  MenuItem::linkToCrud('Show User', 'fas fa-eye', User::class),
            ]);

         }

    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        if(!$user instanceof User){
            throw new \Exception('Wrong user');
        }
        $avatar = 'divers/avatars/' . $user->getAvatar();

        return parent::configureUserMenu($user)
        ->setAvatarUrl($avatar);
    }
}
