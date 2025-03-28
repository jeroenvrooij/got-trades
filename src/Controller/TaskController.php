<?php
// src/Controller/TaskController.php
namespace App\Controller;

// ...
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\TurboBundle;

class TaskController extends AbstractController
{
    #[Route('/task/new', 'new_task')]
    public function new(Request $request): Response
    {
            $form = $this->createFormBuilder([], ['attr' => ['class' => 'mb-3']])
                ->add('foiling', ChoiceType::class, [
                    'attr' => ['class' =>'form-select w-auto'],
                    'placeholder' => 'Filter on foiling',
                    'placeholder_attr' => [
                        'disabled' => 'true', 
                        'hidden' => 'true',
                    ],
                    'choices'  => [
                        'All' => 'All',
                        'Standard' => 'S',
                        'RF' => 'R',
                    ],
                    'required' => false,
                ])
                ->add('hide', CheckboxType::class, [
                    'attr' => ['class' =>'form-check-input', 'role' => 'switch'],
                    'label' => 'hide own cards?',
                    'required' => false,
                ])
                ->add('send', SubmitType::class)
                ->getForm();


            $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData()['foiling'];
            dump($form->getData());
            // ... perform some action, such as saving the task to the database

            // 🔥 The magic happens here! 🔥
            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->renderBlock('new.html.twig', 'success_stream', ['task' => $task]);
            }

            // If the client doesn't support JavaScript, or isn't using Turbo, the form still works as usual.
            // Symfony UX Turbo is all about progressively enhancing your applications!
            return $this->redirectToRoute('new_task', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('new.html.twig', [
            'form' => $form,
            'task' => '',
        ]);
    }
}