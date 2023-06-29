<?php

/**
 * Admin Controller.
 */

namespace App\Controller;

use App\Form\Type\ChangeEmailType;
use App\Form\Type\ChangePasswordType;
use App\Service\AdminServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AdminController.
 */
class AdminController extends AbstractController
{
    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Admin Service.
     */
    private AdminServiceInterface $adminService;

    /**
     * Constructor.
     *
     * @param TranslatorInterface   $translator   the translator service
     * @param AdminServiceInterface $adminService the admin service
     */
    public function __construct(TranslatorInterface $translator, AdminServiceInterface $adminService)
    {
        $this->translator = $translator;
        $this->adminService = $adminService;
    }

    /**
     * Change password action.
     *
     * @param Request                     $request         the request object
     * @param UserPasswordHasherInterface $passwordEncoder the password encoder service
     *
     * @return RedirectResponse|Response the response object
     */
    #[Route('/admin_password', name: 'admin_change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordEncoder): RedirectResponse|Response
    {
        $user = $this->getUser();

        $changePasswordForm = $this->createForm(ChangePasswordType::class, null, [
            'current_password_required' => true,
        ]);

        $changePasswordForm->handleRequest($request);
        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $data = $changePasswordForm->getData();

            if (!$passwordEncoder->isPasswordValid($user, $data['currentPassword'])) {
                $this->addFlash('danger', $this->translator->trans('message.incorrect.password'));

                return $this->redirectToRoute('admin_change_password');
            }

            $this->adminService->updatePassword($user, $data['newPassword']);

            $this->addFlash('success', $this->translator->trans('password.changed'));

            return $this->redirectToRoute('admin_change_password');
        }

        return $this->render('admin/password.html.twig', [
            'changePasswordForm' => $changePasswordForm->createView(),
        ]);
    }

    /**
     * Change email action.
     *
     * @param Request                     $request         the request object
     * @param UserPasswordHasherInterface $passwordEncoder the password encoder service
     *
     * @return RedirectResponse|Response the response object
     */
    #[Route('/admin_email', name: 'admin_change_email')]
    public function changeEmail(Request $request, UserPasswordHasherInterface $passwordEncoder): RedirectResponse|Response
    {
        $user = $this->getUser();

        $changeEmailForm = $this->createForm(ChangeEmailType::class);

        $changeEmailForm->handleRequest($request);
        if ($changeEmailForm->isSubmitted() && $changeEmailForm->isValid()) {
            $data = $changeEmailForm->getData();

            if (!$passwordEncoder->isPasswordValid($user, $data['currentPassword'])) {
                $this->addFlash('danger', $this->translator->trans('message.incorrect.password'));

                return $this->redirectToRoute('admin_change_email');
            }

            $this->adminService->updateEmail($user, $data['newEmail']);

            $this->addFlash('success', $this->translator->trans('email.changed'));

            return $this->redirectToRoute('admin_change_email');
        }

        return $this->render('admin/email.html.twig', [
            'changeEmailForm' => $changeEmailForm->createView(),
        ]);
    }
}
