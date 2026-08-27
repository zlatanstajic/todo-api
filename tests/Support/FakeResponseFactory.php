<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ReflectionException;
use ReflectionMethod;
use TypeError;

/**
 * Minimal ResponseFactory used by controller unit tests.
 */
class FakeResponseFactory implements ResponseFactory
{
    public function make($content = '', $status = 200, array $headers = [])
    {
        $content = $this->normalize($content);
        $status = $this->normalizeStatus($status);

        return new JsonResponse($content, $status);
    }

    public function noContent($status = 204, array $headers = [])
    {
        return new JsonResponse([], $status);
    }

    public function view($view, $data = [], $status = 200, array $headers = [])
    {
        return new JsonResponse($data, $status);
    }

    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        $data = $this->normalize($data);
        $status = $this->normalizeStatus($status);

        return new JsonResponse($data, $status);
    }

    public function jsonp($callback, $data = [], $status = 200, array $headers = [], $options = 0)
    {
        return new JsonResponse([$callback, $data], $status);
    }

    public function stream($callback, $status = 200, array $headers = [])
    {
        return new JsonResponse([], $status);
    }

    public function streamJson($data, $status = 200, $headers = [], $encodingOptions = 15)
    {
        return new JsonResponse($data, $status);
    }

    public function streamDownload($callback, $name = null, array $headers = [], $disposition = 'attachment')
    {
        return new JsonResponse([], 200);
    }

    public function download($file, $name = null, array $headers = [], $disposition = 'attachment')
    {
        return new JsonResponse([], 200);
    }

    public function file($file, array $headers = [])
    {
        return new JsonResponse([], 200);
    }

    public function redirectTo($path, $status = 302, $headers = [], $secure = null)
    {
        return new JsonResponse(['redirect' => $path], $status);
    }

    public function redirectToRoute($route, $parameters = [], $status = 302, $headers = [])
    {
        return new JsonResponse(['route' => $route], $status);
    }

    public function redirectToAction($action, $parameters = [], $status = 302, $headers = [])
    {
        return new JsonResponse(['action' => $action], $status);
    }

    public function redirectGuest($path, $status = 302, $headers = [], $secure = null)
    {
        return new JsonResponse(['guest' => $path], $status);
    }

    public function redirectToIntended($default = '/', $status = 302, $headers = [], $secure = null)
    {
        return new JsonResponse(['intended' => $default], $status);
    }

    private function normalize($value)
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->normalize($v);
            }

            return $out;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            try {
                $rm = new ReflectionMethod($value, 'toArray');
                $params = $rm->getParameters();

                if (count($params) === 0) {
                    return $value->toArray();
                }

                $p = $params[0];

                if (! $p->hasType() || $p->allowsNull()) {
                    return $value->toArray(null);
                }

                // Provide a minimal Request instance if param is typed
                return $value->toArray(new Request);
            } catch (ReflectionException|TypeError) {
                return $value->toArray(null);
            }
        }

        return $value;
    }

    private function normalizeStatus($status)
    {
        if (! is_int($status) || $status < 100 || $status > 599) {
            return 500;
        }

        return $status;
    }
}
