<?php

namespace App\Exceptions;

use RuntimeException;
use App\Support\SessionTracer;

class AcademicProviderException extends RuntimeException
{
    protected $category;

    public function __construct($category, $message = 'Academic provider unavailable', $code = 0, $previous = null)
    {
        $started = microtime(true);
        SessionTracer::enter('academic.exception.construct', 'exception', array('mode' => 'provider'));
        $this->category = $category;
        parent::__construct($message, $code, $previous);
        SessionTracer::leave('academic.exception.construct', 'exception', $started, 'success');
    }

    public function category()
    {
        $started = microtime(true);
        SessionTracer::enter('academic.exception.category', 'exception', array('mode' => 'provider'));
        $category = $this->category;
        SessionTracer::leave('academic.exception.category', 'exception', $started, 'success');
        return $category;
    }

}
