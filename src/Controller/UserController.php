<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    /**
    * Lists all users.
    *
    * This function retrieves a list of all users and renders them in a template for display.
    * Before displaying the list, it checks whether the current user has permission to view users.
    * If the current user does not have permission, an error flash message is added and the user is redirected to the home page.
    * If the current user has permission, the list of users is retrieved from the UserRepository and passed to the template for rendering.
    *
    * @param UserRepository $userRepository The repository for user entities.
    * @param Security $security The security component for checking user permissions.
    * @return Response A response containing the rendered list of users.
    */
    #[Route('/users', name: 'user_list')]
    public function listUser( UserRepository $userRepository, Security $security, TranslatorInterface $translator): Response
    {
        
        if (!$this->isGranted('USER_VIEW', $security->getUser())){
            $this->addFlash('error', 'Vous devez être connecté pour voir les utilisateurs');
            return $this->redirectToRoute('app_home');
        }
        $userList = $userRepository->findAll();

        // First item of the Array is Anonym therefore we remove it.
        // If Anonym user is removed or changed modify this code.
        // unset(Admin1)
        foreach($userList as $key=>$user) {
            $userEmail = $user->getEmail();
            if($userEmail === 'admin1@gmail.com' || $userEmail === 'anonym@.com'){
                unset($userList[$key]);
            }
        }
        
        return $this->render('user/list.html.twig', [
            'controller_name' => 'UserController',
            'users' => $userList,
        ]);
    }

    /**
     * Register function allows to create a new user with unique identifier, a hashed password, a verified email.
     *
     * @param Request $request Stores data from form.
     * @param UserPasswordHasherInterface $userPasswordHasher Tool for hashing password.
     * @param EntityManagerInterface $entityManager Tool to push data to bdd.
     * @return Response
     */
    #[Route('/users/create', name: 'user_create')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, TranslatorInterface $translator, Security $security): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous devez être administrateur pour créer un nouvel utilisateur');
            return $this->redirectToRoute('app_home');
        }
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $user->setRoles($form->get('roles')->getData());
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'L\'utilisateur a bien été ajouté');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit a user.
     *
     * This function handles the editing of a user entity based on the provided user object.
     * Before allowing the user to edit, it checks whether the current user has permission to edit users.
     * If the current user does not have permission, an error flash message is added and the user is redirected to the home page.
     * If the current user has permission, a form is created using the UserType form type, removing the password field for security reasons.
     * The user entity is then updated based on the submitted form data.
     * After successfully updating the user entity, a success flash message is added and the user is redirected to the list of users.
     *
     * @param User $user The user entity to be edited.
     * @param Request $request The request object containing the form data.
     * @param EntityManagerInterface $entityManager The entity manager for persisting changes.
     * @param Security $security The security component for checking user permissions.
     * @param TranslatorInterface $translator The translator for translating flash messages.
     * @return Response A response containing the form for editing the user.
     */
    // #[Route('/user/{id}/edit', name: 'user_edit')]
    // public function editUser(User $user, Request $request, EntityManagerInterface $entityManager, Security $security, TranslatorInterface $translator): Response
    // {
    //     if (!$this->isGranted('USER_EDIT', $security->getUser())){
    //         $this->addFlash('error', 'Vous devez être connecté pour éditer un utilisateur');
    //         return $this->redirectToRoute('app_home');
    //     }

    //     $form = $this->createForm(UserType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $user->setRoles($form->get('roles')->getData());

    //         $entityManager->flush();

    //         $this->addFlash('success',  'L\'utilisateur a bien été modifié');

    //         return $this->redirectToRoute('user_list');
    //     }

    //     return $this->render('user/edit.html.twig', [
    //         'form' => $form->createView(),
    //         'user' => $user,
    //     ]);
    // }

    /**
     * Edit a user.
     *
     * This function handles the editing of a user entity based on the provided user object.
     * Before allowing the user to edit, it checks whether the current user has permission to edit users.
     * If the current user does not have permission, an error flash message is added and the user is redirected to the home page.
     * If the current user has permission, a form is created using the UserType form type, removing the password field for security reasons.
     * The user entity is then updated based on the submitted form data.
     * After successfully updating the user entity, a success flash message is added and the user is redirected to the list of users.
     *
     * @param User $user The user entity to be edited.
     * @param Request $request The request object containing the form data.
     * @param EntityManagerInterface $entityManager The entity manager for persisting changes.
     * @param Security $security The security component for checking user permissions.
     * @param TranslatorInterface $translator The translator for translating flash messages.
     * @return Response A response containing the form for editing the user.
     */
    #[Route('/user/{id}/role/admin', name: 'user_role_admin')]
    public function switchToAdmin(User $user,EntityManagerInterface $entityManager, Security $security, TranslatorInterface $translator): Response
    {
        if (!$this->isGranted('USER_EDIT', $security->getUser())){
            $this->addFlash('error', 'Vous devez être connecté pour éditer un utilisateur');
            return $this->redirectToRoute('app_home');
        }
        $user->setRoles(['ROLE_ADMIN']);
        $entityManager->flush();
        $this->addFlash('success',  'L\'utilisateur a bien été modifié');
        return $this->redirectToRoute('user_list');
    }

    /**
     */
    #[Route('/user/{id}/role/user', name: 'user_role_user')]
    public function switchToUser(User $user, EntityManagerInterface $entityManager, Security $security, TranslatorInterface $translator): Response
    {
        if (!$this->isGranted('USER_EDIT', $security->getUser())){
            $this->addFlash('error', 'Vous devez être connecté pour éditer un utilisateur');
            return $this->redirectToRoute('app_home');
        }

        $user->setRoles(['ROLE_USER']);

        $entityManager->flush();

        $this->addFlash('success',  'L\'utilisateur a bien été modifié');

        return $this->redirectToRoute('user_list');

    }

}
