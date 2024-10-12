<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Form\PostType;
use App\Form\TopicType;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/topic')]
class TopicController extends AbstractController
{
    #[Route('/new/{categoryId}', name: 'app_topic_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $categoryId): Response
    {
        $category = $entityManager->getRepository(Category::class)->find($categoryId);
        if (!$category) {
            throw $this->createNotFoundException('La catégorie n\'existe pas');
        }

        $topic = new Topic();
        $topic->setCategory($category);
        $topic->setAuthor($this->getUser());
        $topic->setCreatedAt(new \DateTimeImmutable()); // Ajoutez cette ligne si nécessaire

        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($topic);

            $post = new Post();
            $post->setContent($form->get('firstPostContent')->getData());
            $post->setUser($this->getUser());
            $post->setTopic($topic);
            $post->setCreatedAt(new \DateTimeImmutable()); // Assurez-vous que Post a aussi un createdAt
            $entityManager->persist($post);

            $entityManager->flush();

            return $this->redirectToRoute('app_topic_show', ['id' => $topic->getId()]);
        }

        return $this->render('topic/new_topic.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    #[Route('/{id}', name: 'app_topic_show', methods: ['GET', 'POST'])]
    public function show(Topic $topic, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser());
            $post->setTopic($topic);
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_topic_show', ['id' => $topic->getId()]);
        }

        return $this->render('topic/topic.html.twig', [
            'topic' => $topic,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_topic_delete', methods: ['POST'])]
    public function delete(Request $request, Topic $topic, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est l'auteur du topic ou un administrateur
        if ($topic->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce sujet.');
        }

        if ($this->isCsrfTokenValid('delete'.$topic->getId(), $request->request->get('_token'))) {
            $categoryId = $topic->getCategory()->getId();
            $entityManager->remove($topic);
            $entityManager->flush();

            $this->addFlash('success', 'Le sujet a été supprimé.');
            return $this->redirectToRoute('app_category_show', ['id' => $categoryId]);
        }

        return $this->redirectToRoute('app_topic_show', ['id' => $topic->getId()]);
    }
}