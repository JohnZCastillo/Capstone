<?php

namespace App\lib;

use App\service\AreaService;
use Slim\Views\TwigExtension;

class LotFinder extends TwigExtension{

    protected  AreaService $areaService;

    public function __construct(AreaService $areaService)
    {
        $this->areaService = $areaService;
    }

    public function getFunctions():array
    {
        return [
            new \Twig\TwigFunction('getLot', [$this, 'getLot']),
        ];
    }

    public function getLot($block): array
    {

        if($block === 'ALL'){
            return  [];
        }

        return $this->areaService->getLot($block);
    }

}