<?php

namespace App\Support;

/* security-10032026-Maurice: preserve paginator JSON without Laravel 5.7 resource wrapping. */
class PaginatedJson
{
    public static function response($paginator, $resourceClass, $request, $operation)
    {
        $started = microtime(true);
        $correlationId = SessionTracer::id($request->header('X-Correlation-ID'));
        SessionTracer::enter($operation, $correlationId, array('resource_type' => 'pagination'));
        try {
            $data = array();
            foreach ($paginator->getCollection() as $item) {
                $data[] = (new $resourceClass($item))->toArray($request);
            }
            $payload = array(
                'data' => $data,
                'links' => array('first' => $paginator->url(1), 'last' => $paginator->url($paginator->lastPage()), 'prev' => $paginator->previousPageUrl(), 'next' => $paginator->nextPageUrl()),
                'meta' => array('current_page' => $paginator->currentPage(), 'from' => $paginator->firstItem(), 'last_page' => $paginator->lastPage(), 'path' => $paginator->getOptions()['path'], 'per_page' => $paginator->perPage(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()),
            );
            $response = response()->json($payload, 200);
            SessionTracer::leave($operation, $correlationId, $started, 'success', array('resource_type' => 'pagination'));
            return $response;
        } catch (\Throwable $exception) {
            SessionTracer::exception($operation, $correlationId, $started, 'pagination_unexpected', $exception);
            throw $exception;
        }
    }
}
