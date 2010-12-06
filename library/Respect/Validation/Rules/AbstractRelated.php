<?php

namespace Respect\Validation\Rules;

use Respect\Validation\Rules\AllOf;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validatable;
use \ReflectionProperty;
use \ReflectionException;

abstract class AbstractRelated extends AbstractRule implements Validatable
{

    protected $mandatory = true;
    protected $reference = '';
    protected $referenceValidator;

    public function __construct($reference,
        Validatable $referenceValidator=null, $mandatory=true)
    {
        if (!is_string($reference) || empty($reference))
            throw new ComponentException(
                'Invalid reference name'
            );
        $this->reference = $reference;
        $this->referenceValidator = $referenceValidator;
        $this->mandatory = $mandatory;
    }

    abstract protected function hasReference($input);

    abstract protected function getReferenceValue($input);

    abstract protected function createException();

    protected function reportError($input, ValidationException $related=null)
    {
        $e = $this->getException();
        if ($e)
            return $e;
        $e = $this->createException();
        if (!is_null($related))
            $e->addRelated($related);
        $e->configure($input, $this->reference, !is_null($related));
        return $e;
    }

    public function validate($input)
    {
        if ($this->mandatory && !$this->hasReference($input))
            return false;
        if (!is_null($this->referenceValidator))
            return $this->referenceValidator
                ->validate($this->getReferenceValue($input));
        return true;
    }

    public function assert($input)
    {
        if ($this->mandatory && !$this->hasReference($input))
            throw $this->reportError($input);
        try {
            if (!is_null($this->referenceValidator))
                $this->referenceValidator->assert(
                    $this->getReferenceValue($input)
                );
        } catch (ValidationException $e) {
            throw $this->reportError($input, $e);
        } catch (ReflectionException $e) {
            throw $this->reportError($input);
        }
        return true;
    }

    public function check($input)
    {
        if ($this->mandatory && !$this->hasReference($input))
            throw $this->reportError($input);
        if (!is_null($this->referenceValidator))
            $this->referenceValidator->check(
                $this->getReferenceValue($input)
            );
        return true;
    }

}