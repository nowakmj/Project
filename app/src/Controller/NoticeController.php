<?php
/**
 * Notice controller.
 */

namespace App\Controller;

use App\Entity\Notice;
use App\Form\Type\NoticeType;
use App\Service\NoticeService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class NoticeController.
 */
#[Route('/notice')]
class NoticeController extends AbstractController
{
    /**
     * NoticeService.
     */
    private NoticeService $noticeService;
    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param NoticeService       $noticeService The notice service
     * @param TranslatorInterface $translator    The translator
     */
    public function __construct(NoticeService $noticeService, TranslatorInterface $translator)
    {
        $this->noticeService = $noticeService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     *
     * @throws NonUniqueResultException
     */
    #[Route(
        name: 'notice_index',
        methods: 'GET'
    )]
    public function index(Request $request): Response
    {
        $filters = $this->getFilters($request);
        $pagination = $this->noticeService->getPaginatedList(
            $request->query->getInt('page', 1),
            $filters
        );

        return $this->render('notice/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Notice $notice Notice
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'notice_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function show(Notice $notice): Response
    {
        return $this->render('notice/show.html.twig', ['notice' => $notice]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'notice_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        $notice = new Notice();
        $form = $this->createForm(
            NoticeType::class,
            $notice,
            ['action' => $this->generateUrl('notice_create')]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->noticeService->save($notice);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('notice_index');
        }

        return $this->render('notice/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Notice  $notice  Notice entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'notice_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Notice $notice): Response
    {
        $form = $this->createForm(
            NoticeType::class,
            $notice,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('notice_edit', ['id' => $notice->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->noticeService->save($notice);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('notice_index');
        }

        return $this->render(
            'notice/edit.html.twig',
            [
                'form' => $form->createView(),
                'notice' => $notice,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Notice  $notice  Notice entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'notice_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, Notice $notice): Response
    {
        $form = $this->createForm(
            FormType::class,
            $notice,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('notice_delete', ['id' => $notice->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->noticeService->delete($notice);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('notice_index');
        }

        return $this->render(
            'notice/delete.html.twig',
            [
                'form' => $form->createView(),
                'notice' => $notice,
            ]
        );
    }

    /**
     * Activate action.
     *
     * @param Notice $notice Notice entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/activate', name: 'notice_activate', methods: 'PUT')]
    public function activate(Notice $notice): Response
    {
        $this->addFlash(
            'success',
            $this->translator->trans('message.notice.accepted')
        );

        $this->noticeService->activate($notice);

        return $this->redirectToRoute('notice_index');
    }

    /**
     * Action deactivate.
     *
     * @param Notice $notice Notice entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/deactivate', name: 'notice_deactivate', methods: 'PUT')]
    public function deactivate(Notice $notice): Response
    {
        $this->addFlash(
            'warning',
            $this->translator->trans('message.notice.deactivated')
        );

        $this->noticeService->deactivate($notice);

        return $this->redirectToRoute('notice_index');
    }

    /**
     * Get filters from request.
     *
     * @param Request $request HTTP request
     *
     * @return array<string, int> Array of filters
     *
     * @psalm-return array{category_id: int, tag_id: int, status_id: int}
     */
    private function getFilters(Request $request): array
    {
        $filters = [];
        $filters['category_id'] = $request->query->getInt('filters_category_id');

        return $filters;
    }
}
