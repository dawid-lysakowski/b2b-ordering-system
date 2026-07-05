<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminCategoryController extends AbstractController
{
    #[Route('/admin/categories', name: 'app_admin_categories')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager
            ->getRepository(Category::class)
            ->findBy([], ['id' => 'DESC']);

        return $this->render('admin_category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/admin/categories/new', name: 'app_admin_category_new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $category = new Category();
        $category->setIsActive(true);

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Kategoria została dodana.');

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin_category/form.html.twig', [
            'form' => $form,
            'title' => 'Nowa kategoria',
            'buttonLabel' => 'Dodaj kategorię',
        ]);
    }

    #[Route('/admin/categories/{id}/edit', name: 'app_admin_category_edit')]
    public function edit(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Kategoria została zaktualizowana.');

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin_category/form.html.twig', [
            'form' => $form,
            'title' => 'Edycja kategorii',
            'buttonLabel' => 'Zapisz zmiany',
        ]);
    }

    #[Route('/admin/categories/{id}/toggle-active', name: 'app_admin_category_toggle_active', methods: ['POST'])]
    public function toggleActive(
        Category $category,
        EntityManagerInterface $entityManager
    ): Response {
        $category->setIsActive(!$category->isActive());

        $entityManager->flush();

        if ($category->isActive()) {
            $this->addFlash('success', 'Kategoria została aktywowana.');
        } else {
            $this->addFlash('success', 'Kategoria została dezaktywowana.');
        }

        return $this->redirectToRoute('app_admin_categories');
    }
}