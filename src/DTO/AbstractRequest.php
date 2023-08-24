<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractRequest
{
    protected $validator;
    
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        
        if($this->getRequest()->getContent())
        {
           
            $this->populate();
            if ($this->autoValidateRequest()) {
                $this->validate();
            }
        }
    }
    
    protected function autoValidateRequest(): bool
    {
        return true;
    }

    protected function populate(): void
    {
        foreach ($this->getRequest()->toArray() as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }


        }
    }
    
    protected function populateNested($dto,$data)
    {
        foreach ($data as $property => $value) {
            if (property_exists($dto, $property)) {
                  $dto->{$property} = $value;
                }
        }
        return $dto;
    }

    public function getRequest(): Request
    {
        return Request::createFromGlobals();
    }

    protected function buildMessages($errors)
    {
        $messages = ['message' => 'validation_failed', 'errors' => []];

        /** @var \Symfony\Component\Validator\ConstraintViolation  */
        foreach ($errors as $message) {
            $messages['errors'][] = [
                'property' => $message->getPropertyPath(),
                'value' => $message->getInvalidValue(),
                'message' => $message->getMessage(),
            ];
        }

        if (count($messages['errors']) > 0) {
            $response = new JsonResponse($messages,400);
            $response->send();

            exit;
        }

    }

    public function validate()
    {

        $errors = $this->validator->validate($this);

        $this->buildMessages($errors);
    }
}

