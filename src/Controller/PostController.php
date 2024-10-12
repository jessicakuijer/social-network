<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser());
            $post->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur actuel est l'auteur du post
        if ($post->getUser() !== $this->getUser()) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à éditer ce message.');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre message a été mis à jour.');
            return $this->redirectToRoute('app_topic_show', ['id' => $post->getTopic()->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($post->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce message.');
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $topic = $post->getTopic();
            $entityManager->remove($post);
            
            // Vérifier si c'était le dernier post du topic
            if ($topic->getPosts()->count() === 1) {
                $entityManager->remove($topic);
                $this->addFlash('info', 'Le sujet a été supprimé car c\'était le dernier message.');
                $entityManager->flush();
                return $this->redirectToRoute('app_category_show', ['id' => $topic->getCategory()->getId()]);
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Le message a été supprimé.');
            return $this->redirectToRoute('app_topic_show', ['id' => $topic->getId()]);
        }

        return $this->redirectToRoute('app_topic_show', ['id' => $post->getTopic()->getId()]);
    }
}