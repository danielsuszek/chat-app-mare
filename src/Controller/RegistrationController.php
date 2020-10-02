<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $form = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'label' => 'Nazwa użytkownika'
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => ['label' => 'Hasło'],
                'second_options' => ['label' => 'Powtórz hasło']
            ])
            ->add('register', SubmitType::class, [
                'label' => 'Rejestruj',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])->getForm();
        
        $form->handleRequest($request);
        
        if($form->isSubmitted()) {
            $data = $form->getData();
            $user = new User();
            $user->setUsername($data['username']);
            $user->setPassword(
                    $passwordEncoder->encodePassword($user, $data['password']) 
            );
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);

            $em->flush();
            
            return $this->redirect($this->generateUrl('app_login'));
        }
        
        
        return $this->render('registration/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
