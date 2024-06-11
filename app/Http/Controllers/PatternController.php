<?php

namespace App\Http\Controllers;

use App\Patterns\AbstractFactory\AbstractFactory;
use App\Patterns\AbstractFactory\ConcreteFactory1;
use App\Patterns\Factory\ConcreteCreator2;
use App\Patterns\Factory\Creator;

class PatternController extends Controller
{

    public function factoryMethod()
    {
        $clientCode = function (Creator $creator) {
            return $creator->someOperation();
        };

        $concreteCreator1 = $clientCode(new ConcreteCreator2());

        die($concreteCreator1);
    }

    public function abstractFactory()
    {
        $clientCode = function (AbstractFactory $abstractFactory) {
            $productA = $abstractFactory->createProductA();
            $productB = $abstractFactory->createProductB();

            die($productB->usefulFunctionB() . '<br />' . $productB->anotherUsefulFunctionB($productA));
        };

        $clientCode(new ConcreteFactory1());
    }

    public function builder()
    {
        return response()->json('hola');
    }
}
