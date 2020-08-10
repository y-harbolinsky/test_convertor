<?php declare(strict_types = 1);

namespace App\Controller;

use App\Exception\ApiBadRequestException;
use App\Manager\RatesManagerInterface;
use App\Request\ExchangeModel;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\Route("/api/v1/convertor")
 */
class ConvertorController extends AbstractFOSRestController
{
    /** RatesManagerInterface */
    private $manager;

    public function __construct(RatesManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Rest\Get("/rates", name="rates")
     * View()
     */
    public function ratesAction(): View
    {
        return $this->view($this->manager->getAvailableRates());
    }

    /**
     * @Rest\Get("/{base}/{target}/{amount}", name="convertor")
     * View()
     */
    public function convertAction(ExchangeModel $model, ValidatorInterface $validator): View
    {
        $errors = $validator->validate($model);

        if (count($errors) > 0) {
            //TODO Handle validation errors
            return $this->view($errors);
        }

        $result = [];

        try {
            $result = $this->manager->convertMoney($model);
        } catch (ApiBadRequestException $ex) {
            return $this->view([
                'message' -> $ex->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $ex) {
            // TODO handle exception, log error
        }

        return $this->view($result);
    }
}
