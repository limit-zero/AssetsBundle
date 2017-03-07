<?php

namespace Limit0\AssetsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles file uploads
 *
 * @author  Josh Worden <josh@limit0.io>
 */
class AssetController extends Controller
{
    public function uploadAction(Request $request)
    {
        $processor = $this->get('limit0_assets.processor');
        $prefix = $request->request->get('model');
        $files = $request->files->all();
        if (array_key_exists('file', $files)) {
            $files = $files['file'];
        }
        $results = [];
        foreach ($files as $file) {
            try {
                $url = $processor->processUpload($prefix, $file);
                $results[] = [
                    'status'    => 'success',
                    'file'      => $file->getClientOriginalName(),
                    'url'       => $url
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'status'    => 'failed: '.$e->getMessage(),
                    'file'      => $file->getClientOriginalName(),
                ];
            }
        }
        return new JsonResponse($results);
    }

    public function uploadBase64Action(Request $request)
    {
        $processor = $this->get('limit0_assets.processor');
        $path  = $request->request->get('path');
        $files = $request->request->get('file');
        if (!is_array($files)) {
            throw new \RuntimeException('No files provided with the request.');
        }
        $results = ['data' => [], 'errors' => []];
        foreach ($files as $file) {
            $file = json_decode($file, true);
            try {
                $url = $processor->processBase64Upload($file['name'], $file['data'], $path);
                $results['data'][] = [
                    'file'      => $file['name'],
                    'url'       => $url
                ];
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'title'     => 'Upload Failed.',
                    'detail'    => $e->getMessage(),
                    'status'    => 500,
                    'meta'      => ['file' => $file['name']]
                ];
            }
        }
        return new JsonResponse($results);
    }

    public function deleteAction(Request $request)
    {
        $url = json_decode($request->getContent())->url;
        $this->get('limit0_assets.processor')->removeByUrl($url);
        return new Response(null, 204);
    }
}
