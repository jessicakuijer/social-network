<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        dump($request->request->all());
        dump($form->getData());

        if ($form->isSubmitted()) {
            dump($form->getErrors(true, false));
            $logger->info('Form submitted');
            if ($form->isValid()) {
                $logger->info('Form is valid');
                try {
                    // encode the plain password
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $form->get('plainPassword')->getData()
                        )
                    );

                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Inscription réussie !');
                    $logger->info('User registered successfully', ['username' => $user->getUsername()]);
                    return $this->redirectToRoute('app_home');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'inscription : ' . $e->getMessage());
                    $logger->error('Registration error', ['error' => $e->getMessage()]);
                }
            } else {
                $logger->info('Form is invalid', ['errors' => $form->getErrors(true, false)]);
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}