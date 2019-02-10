<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Source;
use Symfony\Component\HttpFoundation\Response;

class PageController extends AbstractController
{
    /**
     * @Route("/", name="page")
     */
    public function index()
    {
        $list = $this->getDoctrine()
        ->getRepository(Source::class)
        ->getList();

        header('Refresh:10');

        foreach ($list as $source) {
            $exchangeRate = $this->getExchangeRate($source);
            if ($exchangeRate) {
                return $this->render('page/index.html.twig', [
                    'exchangeRate' => $exchangeRate,
                    'source' => $source->getName(),
                    'error' => false,
                ]);
            }
        }

        return $this->render('page/index.html.twig', [
            'error' => true,
        ]);
    }

    private function getExchangeRate($source)
    {
        $stream = fopen($source->getAddress(),"r");
        $data ="";
        while (!feof($stream)) {
            $data .= fread($stream, 8192);
        }
        fclose($stream);

        switch($source->getName()) {
            case 'www.cbr-xml-daily.ru':
                $data = json_decode($data,TRUE);
                $result = $this->findJsNode($data);
                break;
            case 'www.ecb.europa.eu':
                $data = simplexml_load_string($data);
                $data = json_encode($data);
                $data = json_decode($data,TRUE);
                $result = $this->findXmlNode($data);
                break;
        }

        return $result;

    }

    private function findXmlNode($dataset,$id = 'RUB')
    {
        static $result;
        foreach ($dataset as $key=>$value){
            if ($value != $id){
                if (is_array($dataset[$key])) {
                     self::findXmlNode($dataset[$key]);
                }
            } else {
                $result = $dataset["rate"];
            }
        }
        if ($result) return $result;
    }

    private function findJsNode($dataset,$id = 'EUR')
    {
        static $result;
        foreach ($dataset as $key=>$value){
            if ($value != $id){
                if (is_array($dataset[$key])) {
                     self::findJsNode($dataset[$key]);
                }
            } else {
                $result = $dataset["Value"];
            }
        }
        if ($result) return $result;
    }
}
