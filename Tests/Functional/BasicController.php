<?php

namespace MeteoConcept\HCaptchaBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use MeteoConcept\HCaptchaBundle\Form\HCaptchaType;

class BasicController extends AbstractController
{
    public function form(Request $request): Response
    {
        $form = $this->createFormBuilder()
                     ->add('witness', TextType::class)
                     ->add('captcha', HCaptchaType::class)
                     ->add('submit', SubmitType::class)
                     ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->getData();
            return $this->redirectToRoute('ok', ['value' => $content['witness']]);
        }

        return $this->render('form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function ok(Request $request, string $value): Response
    {
        return $this->render('ok.html.twig', [
            'value' => $value,
        ]);
    }
}
