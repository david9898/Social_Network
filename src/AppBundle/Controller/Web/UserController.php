<?php

namespace AppBundle\Controller\Web;

use AppBundle\Entity\Article;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Service\FileUploader;
use AppBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends Controller
{

    /**
     * @Route("/register", name="user_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder, FileUploader $fileUploader, UserService $userService)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            $profileImage     = $user->getProfileImage();
            $coverImage       = $user->getCoverImage();
            $profileImageName = $fileUploader->uploadImage($profileImage, $this->getParameter('profile_images_directory'));
            $coverImageName   = $fileUploader->uploadImage($coverImage, $this->getParameter('cover_images_directory'));

            $password = $encoder->encodePassword($user, $user->getPlainPassword());

            $user->setProfileImage($profileImageName);
            $user->setCoverImage($coverImageName);
            $user->setPassword($password);

            $fullName = $user->getFirstName() . ' ' . $user->getLastName();
            $user->setFullName($fullName);

            $entytiManager = $this->getDoctrine()->getManager();
            $entytiManager->persist($user);
            $entytiManager->flush();

            $userService->registerUser($user->getId(), $user->getEmail(), $user->getFirstName(), $user->getLastName(),
                                        $user->getPhone(), $user->getBirthDate()->format('string'), $profileImageName, $user->getFullName(),
                                        $coverImageName);

            return $this->redirectToRoute('security_login');
        }

        return $this->render('users/register.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/myProfile", name="user_profile")
     */
    public function myProfile()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $currentId = $this->getUser()->getId();

        $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->getUser($currentId);

        $articles = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->getArticlesOnUser($currentId);

        return $this->render('users/profile.html.twig', [
            'user'     => $user,
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/profile/{id}", name="some_profile")
     */
    public function seeSomeProfile($id, UserService $userService)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $myId      = $this->getUser()->getId();
        $session   = new Session();
        $csrfToken = bin2hex(random_bytes(32));
        $session->set('csrf_token', $csrfToken);

        $userData = $userService->getUserData($id);

        $relation = $userService->checkForUserRelation($myId, $id);

        $articles = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->getArticlesOnUser($id);

        return $this->render('users/someProfile.html.twig', [
            'user'       => $userData,
            'articles'   => $articles,
            'relation'   => $relation,
            'csrf_token' => $csrfToken,
            'id'         => $id
        ]);
    }
}
