<?php

namespace AppBundle\Controller\Web;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends Controller
{

    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder, FileUploader $fileUploader)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            $file = $user->getProfileImage();
            $imageName = $fileUploader->uploadImage($file, $this->getParameter('profile_images_directory'));

            $password = $encoder->encodePassword($user, $user->getPlainPassword());

            $user->setProfileImage($imageName);
            $user->setPassword($password);

            $entytiManager = $this->getDoctrine()->getManager();
            $entytiManager->persist($user);
            $entytiManager->flush();

            return $this->redirectToRoute('security_login');
        }

        return $this->render('users/register.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/profile", name="user_profile")
     */
    public function profile()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        return $this->render('users/profile.html.twig', [
            'user' => $user
        ]);
    }


}
