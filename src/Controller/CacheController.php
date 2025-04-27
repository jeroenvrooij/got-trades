<?php
// src/Controller/HomeController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

class CacheController extends AbstractController
{
    public function __construct(
    ) {
    }

    #[Route('/admin/clear-prod-cache')]
    public function clearCache(Security $security, KernelInterface $kernel): Response
    {
        $user = $security->getUser();

        // Make sure only *I* can access this
        /** @var App\Entity\User $user */
        if (!$user || $user->getId()->toString() !== '01958552-5c7e-70e0-99dd-b0dc54179b71') {
            throw $this->createAccessDeniedException();
        }

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'cache:clear',
            '--env' => 'prod',
            '--no-interaction' => true,
        ]);

        $output = new BufferedOutput();

        try {
            $application->run($input, $output);
            $message = nl2br($output->fetch());

            return new Response(
                '<html><head><meta name="robots" content="noindex, nofollow"></head><body><h2>✅ Cache cleared!</h2><pre>' . $message . '</pre></body></html>'
            );
        } catch (\Exception $e) {
            return new Response(
                '<html><head><meta name="robots" content="noindex, nofollow"></head><body><h2>❌ Error: ' . $e->getMessage() . '</h2></body></html>',
                500
            );
        }
    }
    // #[Route('/admin/clear-prod-cache')]
    // public function clearCache(): Response
    // {
    //     /** @var App\Entity\User $user */
    //     $user = $this->getUser();

    //     // Make sure only *you* can access this
    //     if (!$user || $user->getId()->toString() !== '01958552-5c7e-70e0-99dd-b0dc54179b71') {
    //         throw $this->createAccessDeniedException();
    //     }

    //     $cacheDir = $this->getParameter('kernel.project_dir') . '/var/cache/prod';

    //     $fs = new Filesystem();

    //     try {
    //         if ($fs->exists($cacheDir)) {
    //             $fs->remove($cacheDir); // ✅ This removes the directory and everything inside it

    //             return new Response(
    //                 '<html><head><meta name="robots" content="noindex, nofollow"></head><body><h2>✅ Cache cleared successfully.</h2></body></html>'
    //             );
    //         }

    //         return new Response(
    //             '<html><head><meta name="robots" content="noindex, nofollow"></head><body><h2>⚠️ Cache directory does not exist or is already cleared.</h2></body></html>'
    //         );
    //     } catch (IOExceptionInterface $exception) {
    //         return new Response(
    //             '<html><head><meta name="robots" content="noindex, nofollow"></head><body><h2>❌ Error clearing cache: ' . $exception->getMessage() . '</h2></body></html>',
    //             500
    //         );
    //     }
    // }
}