<?php

namespace HeimrichHannot\MediaLibraryBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use HeimrichHannot\MediaLibraryBundle\Collection\ArchiveTypeCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/_huh_media_library/test', name: 'huh_media_library.test.')]
class TestController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(ArchiveTypeCollection $collection): Response
    {
        return new Response('<style>:root { color-scheme: dark }</style>HALLO WELT');
    }
}