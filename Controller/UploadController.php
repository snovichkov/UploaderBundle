<?php

namespace CyberApp\UploaderBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UploadController extends Controller
{
    const RESPONSE_MAX_AGE = 63072000;
    const RESPONSE_SHARED_MAX_AGE = 63072000;

    /**
     * @Route(
     *     path="/uploads/{endpoint}/{file}",
     *     requirements={"file": ".+"},
     *     name="view_orphanage_upload"
     * )
     *
     * @return Response
     */
    public function orphanageAction(Request $request)
    {
        try {
            $orphanageStorage = $this->get('oneup_uploader.orphanage_manager')->get($request->get('endpoint'));
        } catch (\Exception $e) {
            throw $this->createNotFoundException();
        }

        $founded = false;
        foreach ($orphanageStorage->getFiles() as $file) {
            if ($file->getRelativePathname() === $request->get('file')) {
                $founded = true;
                continue;
            }
        }

        if (! $founded) {
            throw $this->createNotFoundException();
        }

        $content = null;

        try {
            if (method_exists($file, 'getContents')) {
                $content = $file->getContents();
            } elseif (method_exists($file, 'getContent')) {
                $content = $file->getContent();
            } else {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            throw $this->createNotFoundException();
        }

        $response = new Response();

        $response
            ->setPublic()
            ->setMaxAge($this::RESPONSE_MAX_AGE)
            ->setSharedMaxAge($this::RESPONSE_SHARED_MAX_AGE)
            ->setContent($content)
            ->headers
            ->add([
                'Content-Type' => $this
                    ->get('liip_imagine.binary.mime_type_guesser')
                    ->guess($content),
                'Content-Disposition' => $response
                    ->headers
                    ->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $file->getBasename()),
            ]);

        return $response;
    }
}
